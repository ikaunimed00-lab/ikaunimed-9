<?php

namespace App\Services\Payments;

use App\Events\Payments\TripayPaymentSettled;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripayWebhookService
{
    /**
     * @return array{http_status: int, message: string}
     */
    public function handleShopCallback(Request $request): array
    {
        $config = config('services.tripay');
        $privateKey = $config['private_key'] ?? null;
        $merchantCode = $config['merchant_code'] ?? null;

        if (! $privateKey || ! $merchantCode) {
            return ['http_status' => 500, 'message' => 'Tripay not configured'];
        }

        /** @var array<string, mixed> $data */
        $data = $request->all();
        $reference = $data['reference'] ?? null;
        $status = $data['status'] ?? null;
        $amount = $data['amount'] ?? null;
        $signature = $data['signature'] ?? null;

        if (! $reference || ! $status || $amount === null || ! $signature) {
            return ['http_status' => 400, 'message' => 'Invalid payload'];
        }

        $computedSignature = hash_hmac(
            'sha256',
            $merchantCode . $reference . $status . $amount,
            $privateKey
        );

        if (! hash_equals($computedSignature, (string) $signature)) {
            return ['http_status' => 400, 'message' => 'Invalid signature'];
        }

        $payment = $this->resolvePayment((string) $reference, $data['merchant_ref'] ?? null);
        if (! $payment) {
            return ['http_status' => 200, 'message' => 'Payment not found'];
        }

        $callbackAmount = (int) $amount;
        $paymentAmount = (int) $payment->amount;

        if ($paymentAmount !== $callbackAmount) {
            $payment->logs()->create([
                'event' => 'tripay_callback_amount_mismatch',
                'payload' => json_encode($data),
                'headers' => $request->headers->all(),
                'status_code' => 400,
            ]);

            return ['http_status' => 400, 'message' => 'Invalid amount'];
        }

        if ($payment->status === Payment::STATUS_PAID) {
            $payment->logs()->create([
                'event' => 'tripay_callback_ignored',
                'payload' => json_encode($data),
                'headers' => $request->headers->all(),
                'status_code' => 200,
            ]);

            return ['http_status' => 200, 'message' => 'OK'];
        }

        $tripayStatus = strtoupper((string) $status);
        $newPaymentStatus = $payment->status;
        $newOrderStatus = $payment->order?->status;
        $paidAt = $payment->paid_at;
        $shouldDispatchSettledEvent = false;

        if ($tripayStatus === 'PAID') {
            $newPaymentStatus = Payment::STATUS_PAID;
            $newOrderStatus = 'paid';
            $paidAt ??= now();
            $shouldDispatchSettledEvent = true;
        } elseif ($tripayStatus === 'UNPAID') {
            $newPaymentStatus = Payment::STATUS_PENDING;
            $newOrderStatus = 'awaiting_payment';
            $paidAt = null;
        } elseif (in_array($tripayStatus, ['EXPIRED', 'FAILED', 'REFUND'], true)) {
            $newPaymentStatus = $tripayStatus === 'EXPIRED'
                ? Payment::STATUS_EXPIRED
                : Payment::STATUS_FAILED;
            $newOrderStatus = 'canceled';
            $paidAt = null;
        }

        DB::transaction(function () use ($payment, $newPaymentStatus, $newOrderStatus, $paidAt, $data, $request): void {
            $payment->update([
                'status' => $newPaymentStatus,
                'paid_at' => $paidAt,
            ]);

            if ($newOrderStatus && $payment->order) {
                $payment->order->update([
                    'status' => $newOrderStatus,
                ]);
            }

            $payment->logs()->create([
                'event' => 'tripay_callback',
                'payload' => json_encode($data),
                'headers' => $request->headers->all(),
                'status_code' => 200,
            ]);
        });

        if ($shouldDispatchSettledEvent) {
            $payment->refresh();
            TripayPaymentSettled::dispatch($payment, $data);
        }

        return ['http_status' => 200, 'message' => 'OK'];
    }

    /**
     * @param  string|int|null  $merchantRef
     */
    private function resolvePayment(string $reference, string|int|null $merchantRef): ?Payment
    {
        $payment = Payment::query()
            ->where('provider', 'tripay')
            ->where('provider_reference', $reference)
            ->first();

        if ($payment || ! is_string($merchantRef)) {
            return $payment;
        }

        if (! str_starts_with($merchantRef, 'ORD-')) {
            return null;
        }

        $orderId = (int) substr($merchantRef, 4);
        if ($orderId <= 0) {
            return null;
        }

        return Payment::query()
            ->where('provider', 'tripay')
            ->where('order_id', $orderId)
            ->latest('id')
            ->first();
    }
}

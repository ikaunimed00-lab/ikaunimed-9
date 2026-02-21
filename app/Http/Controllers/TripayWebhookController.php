<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TripayWebhookController extends Controller
{
    public function shop(Request $request): Response
    {
        $config = config('services.tripay');

        $privateKey = $config['private_key'] ?? null;
        $merchantCode = $config['merchant_code'] ?? null;

        if (! $privateKey || ! $merchantCode) {
            return response('Tripay not configured', 500);
        }

        $data = $request->all();

        $reference = $data['reference'] ?? null;
        $status = $data['status'] ?? null;
        $amount = $data['amount'] ?? null;
        $signature = $data['signature'] ?? null;

        if (! $reference || ! $status || $amount === null || ! $signature) {
            return response('Invalid payload', 400);
        }

        $computedSignature = hash_hmac(
            'sha256',
            $merchantCode . $reference . $status . $amount,
            $privateKey
        );

        if (! hash_equals($computedSignature, $signature)) {
            return response('Invalid signature', 400);
        }

        $payment = Payment::query()
            ->where('provider', 'tripay')
            ->where('provider_reference', $reference)
            ->first();

        if (! $payment && isset($data['merchant_ref'])) {
            $merchantRef = $data['merchant_ref'];

            if (str_starts_with($merchantRef, 'ORD-')) {
                $orderId = (int) substr($merchantRef, 4);

                if ($orderId > 0) {
                    $payment = Payment::query()
                        ->where('provider', 'tripay')
                        ->where('order_id', $orderId)
                        ->latest('id')
                        ->first();
                }
            }
        }

        if (! $payment) {
            return response('Payment not found', 200);
        }

        $tripayStatus = strtoupper($status);

        $newPaymentStatus = $payment->status;
        $newOrderStatus = $payment->order?->status ?? null;
        $paidAt = $payment->paid_at;

        if ($tripayStatus === 'PAID') {
            $newPaymentStatus = Payment::STATUS_PAID;
            $newOrderStatus = 'paid';

            if (! $paidAt) {
                $paidAt = now();
            }
        } elseif (in_array($tripayStatus, ['UNPAID'], true)) {
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

        DB::transaction(function () use ($payment, $newPaymentStatus, $newOrderStatus, $paidAt, $data, $request) {
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

        return response('OK', 200);
    }
}


<?php

namespace App\Listeners\Payments;

use App\Events\Payments\TripayPaymentSettled;
use App\Models\Course;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\Courses\CourseLearningService;
use Illuminate\Support\Facades\Mail;

class HandleTripayPaymentSettled
{
    public function __construct(
        private readonly CourseLearningService $courseLearningService
    ) {
    }

    public function handle(TripayPaymentSettled $event): void
    {
        $payment = $event->payment->loadMissing([
            'user',
            'order.items.product.course',
        ]);

        if (! $payment->order) {
            return;
        }

        foreach ($payment->order->items as $item) {
            $product = $item->product;

            if (($item->product_type ?? null) === 'digital') {
                $this->handleDigitalProduct($payment, $item);
            }

            if (
                ($item->product_type ?? null) === 'service'
                && $product
                && $product->membership_role
                && $payment->user
            ) {
                $payment->user->assignRole($product->membership_role);
            }
        }

        $this->sendConfirmationEmail($payment);
    }

    private function handleDigitalProduct(Payment $payment, OrderItem $item): void
    {
        $product = $item->product;
        if (! $product) {
            $payment->logs()->create([
                'event' => 'tripay_digital_product_missing',
                'payload' => json_encode([
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order?->id,
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                ]),
                'headers' => [],
                'status_code' => 200,
            ]);

            return;
        }

        if (! $product->course_id) {
            $payment->logs()->create([
                'event' => 'tripay_digital_product_without_course_id',
                'payload' => json_encode([
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order?->id,
                    'order_item_id' => $item->id,
                    'product_id' => $product->id,
                ]),
                'headers' => [],
                'status_code' => 200,
            ]);

            return;
        }

        $course = Course::query()
            ->where('id', $product->course_id)
            ->where('status', 'published')
            ->first();

        if (! $course || ! $payment->user) {
            $payment->logs()->create([
                'event' => 'tripay_digital_course_not_found_by_id',
                'payload' => json_encode([
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order?->id,
                    'order_item_id' => $item->id,
                    'product_id' => $product->id,
                    'course_id' => $product->course_id,
                ]),
                'headers' => [],
                'status_code' => 200,
            ]);

            return;
        }

        $this->courseLearningService->activateEnrollment($payment->user, $course);
    }

    private function sendConfirmationEmail($payment): void
    {
        if (! $payment->user || ! $payment->user->email) {
            return;
        }

        $digitalCourses = $payment->order?->items
            ->filter(function ($item) {
                return ($item->product_type ?? null) === 'digital'
                    && $item->product
                    && $item->product->course;
            })
            ->map(function ($item) {
                return $item->product->course->title;
            })
            ->unique()
            ->values()
            ->all() ?? [];

        $courseList = '';
        if (! empty($digitalCourses)) {
            $courseList = '<ul>';

            foreach ($digitalCourses as $title) {
                $courseList .= '<li>' . e($title) . '</li>';
            }

            $courseList .= '</ul>';
        }

        $subject = 'Pembayaran Anda Berhasil - Akses Kursus LMS IKA UNIMED';
        $body = '<p>Halo ' . e($payment->user->name) . ',</p>'
            . '<p>Pembayaran Anda dengan nomor referensi <strong>' . e($payment->provider_reference ?? $payment->id) . '</strong> telah berhasil dikonfirmasi.</p>'
            . (! empty($digitalCourses)
                ? '<p>Anda sekarang sudah otomatis terdaftar pada kursus berikut:</p>' . $courseList
                : '<p>Pembayaran Anda berhasil diproses.</p>')
            . '<p>Silakan masuk ke dashboard LMS di portal alumni untuk mulai belajar.</p>'
            . '<p>Salam hangat,<br>' . e(config('app.name')) . '</p>';

        Mail::html($body, function ($message) use ($payment, $subject): void {
            $message->to($payment->user->email, $payment->user->name)->subject($subject);
        });
    }
}

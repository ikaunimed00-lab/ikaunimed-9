<?php

use App\Models\Course;
use App\Events\Payments\TripayPaymentSettled;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;

it('processes valid tripay callback and updates payment order and logs', function () {
    Event::fake();

    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 100000,
        'shipping_cost' => 0,
        'grand_total' => 100000,
        'shipping_address' => [
            'name' => 'Test User',
            'phone' => '08123456789',
            'address' => 'Alamat Lengkap',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 100000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-123',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 100000;
    $reference = 'TRX-REF-123';
    $status = 'PAID';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($payment->paid_at)->not()->toBeNull();
    expect($order->status)->toBe('paid');

    $log = PaymentLog::where('payment_id', $payment->id)->first();

    expect($log)->not()->toBeNull();
    expect($log->event)->toBe('tripay_callback');
    expect($log->status_code)->toBe(200);
    expect($log->payload)->toContain($reference);
    Event::assertDispatched(TripayPaymentSettled::class);
});

it('rejects invalid signature and does not change payment or order', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 50000,
        'shipping_cost' => 0,
        'grand_total' => 50000,
        'shipping_address' => [
            'name' => 'User Invalid',
            'phone' => '08123456780',
            'address' => 'Alamat Lain',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 50000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-456',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 50000;
    $reference = 'TRX-REF-456';
    $status = 'PAID';

    $invalidSignature = 'invalid-signature';

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $invalidSignature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertStatus(400);

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PENDING);
    expect($payment->paid_at)->toBeNull();
    expect($order->status)->toBe('awaiting_payment');
    expect(PaymentLog::where('payment_id', $payment->id)->count())->toBe(0);
});

it('handles expired status by canceling order and marking payment expired', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 75000,
        'shipping_cost' => 0,
        'grand_total' => 75000,
        'shipping_address' => [
            'name' => 'User Expired',
            'phone' => '08123456781',
            'address' => 'Alamat Expired',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 75000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-789',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 75000;
    $reference = 'TRX-REF-789';
    $status = 'EXPIRED';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_EXPIRED);
    expect($payment->paid_at)->toBeNull();
    expect($order->status)->toBe('canceled');

    $log = PaymentLog::where('payment_id', $payment->id)->first();

    expect($log)->not()->toBeNull();
    expect($log->event)->toBe('tripay_callback');
    expect($log->status_code)->toBe(200);
});

it('handles failed status by canceling order and marking payment failed', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 90000,
        'shipping_cost' => 0,
        'grand_total' => 90000,
        'shipping_address' => [
            'name' => 'User Failed',
            'phone' => '08123456782',
            'address' => 'Alamat Failed',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 90000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-900',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 90000;
    $reference = 'TRX-REF-900';
    $status = 'FAILED';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_FAILED);
    expect($payment->paid_at)->toBeNull();
    expect($order->status)->toBe('canceled');

    $log = PaymentLog::where('payment_id', $payment->id)->first();

    expect($log)->not()->toBeNull();
    expect($log->event)->toBe('tripay_callback');
    expect($log->status_code)->toBe(200);
});

it('is idempotent when receiving duplicate paid callbacks', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 120000,
        'shipping_cost' => 0,
        'grand_total' => 120000,
        'shipping_address' => [
            'name' => 'User Double',
            'phone' => '08123456783',
            'address' => 'Alamat Double',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 120000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-DOUBLE',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 120000;
    $reference = 'TRX-REF-DOUBLE';
    $status = 'PAID';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $firstResponse = $this->postJson('/webhook/tripay/shop', $payload);
    $firstResponse->assertOk();

    $payment->refresh();
    $order->refresh();

    $firstPaidAt = $payment->paid_at;

    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($firstPaidAt)->not()->toBeNull();
    expect($order->status)->toBe('paid');

    $secondResponse = $this->postJson('/webhook/tripay/shop', $payload);
    $secondResponse->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($payment->paid_at)->toEqual($firstPaidAt);
    expect($order->status)->toBe('paid');

    expect(PaymentLog::where('payment_id', $payment->id)->count())->toBe(2);
});

it('handles unpaid status by keeping payment pending and order awaiting payment', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 45000,
        'shipping_cost' => 0,
        'grand_total' => 45000,
        'shipping_address' => [
            'name' => 'User Unpaid',
            'phone' => '08123456784',
            'address' => 'Alamat Unpaid',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 45000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-UNPAID',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 45000;
    $reference = 'TRX-REF-UNPAID';
    $status = 'UNPAID';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PENDING);
    expect($payment->paid_at)->toBeNull();
    expect($order->status)->toBe('awaiting_payment');

    $log = PaymentLog::where('payment_id', $payment->id)->first();

    expect($log)->not()->toBeNull();
    expect($log->event)->toBe('tripay_callback');
    expect($log->status_code)->toBe(200);
});

it('handles refund status by canceling order and marking payment failed', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 110000,
        'shipping_cost' => 0,
        'grand_total' => 110000,
        'shipping_address' => [
            'name' => 'User Refund',
            'phone' => '08123456785',
            'address' => 'Alamat Refund',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 110000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-REFUND',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 110000;
    $reference = 'TRX-REF-REFUND';
    $status = 'REFUND';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_FAILED);
    expect($payment->paid_at)->toBeNull();
    expect($order->status)->toBe('canceled');

    $log = PaymentLog::where('payment_id', $payment->id)->first();

    expect($log)->not()->toBeNull();
    expect($log->event)->toBe('tripay_callback');
    expect($log->status_code)->toBe(200);
});

it('grants lms enrollment for digital products when payment is paid', function () {
    $user = User::factory()->create();

    $course = Course::create([
        'title' => 'Premium Course',
        'slug' => 'premium-course',
        'status' => 'published',
        'is_paid' => true,
    ]);

    $product = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => $course->id,
        'name' => 'Premium Course Product',
        'slug' => 'premium-course',
        'sku' => 'COURSE-001',
        'description' => 'Access to Premium Course',
        'price' => 150000,
        'stock' => 10,
        'type' => 'digital',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 150000,
        'shipping_cost' => 0,
        'grand_total' => 150000,
        'shipping_address' => [
            'name' => 'User Digital',
            'phone' => '08123456789',
            'address' => 'Alamat Digital',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_type' => 'digital',
        'quantity' => 1,
        'price' => 150000,
        'total' => 150000,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 150000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-DIGITAL',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 150000;
    $reference = 'TRX-REF-DIGITAL';
    $status = 'PAID';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($order->status)->toBe('paid');

    $enrollments = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->get();

    expect($enrollments->count())->toBe(1);
    expect($enrollments->first()->status)->toBe('active');
    expect($enrollments->first()->started_at)->not()->toBeNull();

    $secondResponse = $this->postJson('/webhook/tripay/shop', $payload);
    $secondResponse->assertOk();

    $enrollmentsAfter = Enrollment::where('user_id', $user->id)
        ->where('course_id', $course->id)
        ->get();

    expect($enrollmentsAfter->count())->toBe(1);
});

it('grants membership role for service products when payment is paid', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();

    $product = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Premium Membership',
        'slug' => 'premium-membership',
        'sku' => 'MEMBERSHIP-001',
        'description' => 'Keanggotaan premium alumni',
        'price' => 100000,
        'stock' => 100,
        'type' => 'service',
        'membership_role' => 'premium_member',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'organization_id' => null,
        'status' => 'awaiting_payment',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 100000,
        'shipping_cost' => 0,
        'grand_total' => 100000,
        'shipping_address' => [
            'name' => 'User Membership',
            'phone' => '08123456786',
            'address' => 'Alamat Membership',
        ],
        'notes' => null,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_type' => 'service',
        'quantity' => 1,
        'price' => 100000,
        'total' => 100000,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 100000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-MEMBERSHIP',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PENDING,
        'paid_at' => null,
        'raw_payload' => null,
    ]);

    config([
        'services.tripay.merchant_code' => 'TESTCODE',
        'services.tripay.private_key' => 'TESTPRIVATE',
    ]);

    $amount = 100000;
    $reference = 'TRX-REF-MEMBERSHIP';
    $status = 'PAID';

    $signature = hash_hmac(
        'sha256',
        'TESTCODE' . $reference . $status . $amount,
        'TESTPRIVATE'
    );

    $payload = [
        'reference' => $reference,
        'merchant_ref' => 'ORD-' . $order->id,
        'amount' => $amount,
        'status' => $status,
        'signature' => $signature,
    ];

    $response = $this->postJson('/webhook/tripay/shop', $payload);

    $response->assertOk();

    $payment->refresh();
    $order->refresh();
    $user->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PAID);
    expect($order->status)->toBe('paid');
    expect($user->hasRole('premium_member'))->toBeTrue();
});

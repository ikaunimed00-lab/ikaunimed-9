<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use Inertia\Testing\AssertableInertia as Assert;

it('prevents user from viewing another users order detail', function () {
    $user = \App\Models\User::factory()->create();
    $otherUser = \App\Models\User::factory()->create();

    $order = Order::create([
        'user_id' => $otherUser->id,
        'status' => 'paid',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 70000,
        'shipping_cost' => 0,
        'grand_total' => 70000,
        'shipping_address' => [
            'name' => 'User C',
            'phone' => '08123456782',
            'address' => 'Alamat C',
        ],
        'created_by' => $otherUser->id,
        'updated_by' => $otherUser->id,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('shop.orders.show', $order));

    $response->assertStatus(404);
});

it('shows order detail with payment information for owner', function () {
    $user = \App\Models\User::factory()->create();

    $order = Order::create([
        'user_id' => $user->id,
        'status' => 'paid',
        'fulfillment_status' => 'unfulfilled',
        'total_amount' => 100000,
        'shipping_cost' => 0,
        'grand_total' => 100000,
        'shipping_address' => [
            'name' => 'Test User',
            'phone' => '08123456789',
            'address' => 'Alamat Lengkap',
        ],
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'amount' => 100000,
        'provider' => 'tripay',
        'provider_reference' => 'TRX-REF-ORDER',
        'method' => 'BRIVA',
        'status' => Payment::STATUS_PAID,
        'paid_at' => now(),
        'raw_payload' => null,
    ]);

    $shipment = Shipment::create([
        'order_id' => $order->id,
        'courier_name' => 'JNE',
        'tracking_number' => 'RESI-123456',
        'status' => 'shipped',
        'shipped_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('shop.orders.show', $order));

    $response->assertOk();

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Shop/Orders/Show')
            ->where('order.id', $order->id)
            ->where('payment.id', $payment->id)
            ->where('payment.status', Payment::STATUS_PAID)
            ->where('order.shipment.tracking_number', $shipment->tracking_number)
    );
});

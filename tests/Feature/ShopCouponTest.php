<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('applies fixed coupon discount correctly', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456792',
        'nik' => '1234567890123462',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1995-06-06',
        'alamat_lengkap' => 'Alamat',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2013',
        's1_tahun_tamat' => '2017',
    ]);

    $product = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Produk Test',
        'slug' => 'produk-test',
        'sku' => 'SKU-001',
        'description' => 'Desc',
        'price' => 100000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $coupon = Coupon::create([
        'code' => 'DISKON10K',
        'type' => 'fixed',
        'value' => 10000,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    $cart = Cart::create(['user_id' => $user->id, 'status' => 'active']);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price_snapshot' => 100000,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'Test',
        'shipping_phone' => '08123',
        'shipping_address' => 'Addr',
        'coupon_code' => 'diskon10k', // Case insensitive test
    ]);

    $response->assertRedirect(); // Should redirect to payment or success
    
    $order = Order::latest('id')->first();
    // 100,000 - 10,000 = 90,000
    expect($order->grand_total)->toBe(90000);
    expect($order->couponUsage)->not->toBeNull();
});

it('applies percentage coupon discount correctly', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456792',
        'nik' => '1234567890123462',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1995-06-06',
        'alamat_lengkap' => 'Alamat',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2013',
        's1_tahun_tamat' => '2017',
    ]);

    $product = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Produk Test',
        'slug' => 'produk-test',
        'sku' => 'SKU-001',
        'description' => 'Desc',
        'price' => 100000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $coupon = Coupon::create([
        'code' => 'DISKON50',
        'type' => 'percentage',
        'value' => 0.5, // 50%
        'max_discount' => 20000, // Max 20k
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    $cart = Cart::create(['user_id' => $user->id, 'status' => 'active']);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price_snapshot' => 100000,
    ]);

    $this->actingAs($user);

    $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'Test',
        'shipping_phone' => '08123',
        'shipping_address' => 'Addr',
        'coupon_code' => 'DISKON50',
    ]);

    $order = Order::latest('id')->first();
    // 50% of 100,000 is 50,000. But max discount is 20,000.
    // Total = 100,000 - 20,000 = 80,000.
    expect($order->grand_total)->toBe(80000);
});

it('applies coupon only to eligible products', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456792',
        'nik' => '1234567890123462',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1995-06-06',
        'alamat_lengkap' => 'Alamat',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2013',
        's1_tahun_tamat' => '2017',
    ]);

    $productA = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Produk A (Eligible)',
        'slug' => 'produk-a',
        'sku' => 'SKU-A',
        'description' => 'Desc',
        'price' => 100000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $productB = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Produk B (Not Eligible)',
        'slug' => 'produk-b',
        'sku' => 'SKU-B',
        'description' => 'Desc',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $coupon = Coupon::create([
        'code' => 'PROMO-A',
        'type' => 'percentage',
        'value' => 0.5, // 50%
        'eligible_product_ids' => [(string)$productA->id],
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    $cart = Cart::create(['user_id' => $user->id, 'status' => 'active']);
    CartItem::create(['cart_id' => $cart->id, 'product_id' => $productA->id, 'quantity' => 1, 'price_snapshot' => 100000]);
    CartItem::create(['cart_id' => $cart->id, 'product_id' => $productB->id, 'quantity' => 1, 'price_snapshot' => 50000]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'Test',
        'shipping_phone' => '08123',
        'shipping_address' => 'Addr',
        'coupon_code' => 'promo-a',
    ]);

    $order = Order::latest('id')->first();
    // Total = 150,000.
    // Eligible Base = 100,000 (Product A only).
    // Discount = 50% of 100,000 = 50,000.
    // Grand Total = 150,000 - 50,000 = 100,000.

    expect($order->grand_total)->toBe(100000);
});

it('applies coupon only to eligible categories', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456792',
        'nik' => '1234567890123462',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1995-06-06',
        'alamat_lengkap' => 'Alamat',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2013',
        's1_tahun_tamat' => '2017',
    ]);

    $catA = ProductCategory::create(['name' => 'Category A', 'slug' => 'cat-a']);
    $catB = ProductCategory::create(['name' => 'Category B', 'slug' => 'cat-b']);

    $productA = Product::create([
        'organization_id' => null,
        'product_category_id' => $catA->id,
        'course_id' => null,
        'name' => 'Produk A (Eligible)',
        'slug' => 'produk-a',
        'sku' => 'SKU-A',
        'description' => 'Desc',
        'price' => 100000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $productB = Product::create([
        'organization_id' => null,
        'product_category_id' => $catB->id,
        'course_id' => null,
        'name' => 'Produk B (Not Eligible)',
        'slug' => 'produk-b',
        'sku' => 'SKU-B',
        'description' => 'Desc',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $coupon = Coupon::create([
        'code' => 'PROMO-CAT',
        'type' => 'percentage',
        'value' => 0.5, // 50%
        'eligible_categories' => [(string)$catA->id],
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    $cart = Cart::create(['user_id' => $user->id, 'status' => 'active']);
    CartItem::create(['cart_id' => $cart->id, 'product_id' => $productA->id, 'quantity' => 1, 'price_snapshot' => 100000]);
    CartItem::create(['cart_id' => $cart->id, 'product_id' => $productB->id, 'quantity' => 1, 'price_snapshot' => 50000]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'Test',
        'shipping_phone' => '08123',
        'shipping_address' => 'Addr',
        'coupon_code' => 'promo-cat',
    ]);

    $order = Order::latest('id')->first();
    // Total = 150,000.
    // Eligible Base = 100,000 (Product A only).
    // Discount = 50% of 100,000 = 50,000.
    // Grand Total = 150,000 - 50,000 = 100,000.

    expect($order->grand_total)->toBe(100000);
});

it('returns error when coupon is not applicable to any product in cart', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456792',
        'nik' => '1234567890123462',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1995-06-06',
        'alamat_lengkap' => 'Alamat',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2013',
        's1_tahun_tamat' => '2017',
    ]);

    $productA = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Produk A (Eligible)',
        'slug' => 'produk-a',
        'sku' => 'SKU-A',
        'description' => 'Desc',
        'price' => 100000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $productB = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Produk B (Not Eligible)',
        'slug' => 'produk-b',
        'sku' => 'SKU-B',
        'description' => 'Desc',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $coupon = Coupon::create([
        'code' => 'PROMO-A',
        'type' => 'percentage',
        'value' => 0.5,
        'eligible_product_ids' => [(string)$productA->id],
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    // Cart only has Product B (ineligible)
    $cart = Cart::create(['user_id' => $user->id, 'status' => 'active']);
    CartItem::create(['cart_id' => $cart->id, 'product_id' => $productB->id, 'quantity' => 1, 'price_snapshot' => 50000]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'Test',
        'shipping_phone' => '08123',
        'shipping_address' => 'Addr',
        'coupon_code' => 'promo-a',
    ]);

    $response->assertSessionHasErrors(['coupon_code' => 'Kupon tidak berlaku untuk produk dalam keranjang Anda.']);
});

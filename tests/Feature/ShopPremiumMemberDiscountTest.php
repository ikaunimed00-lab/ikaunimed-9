<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

it('applies discount for premium member on checkout', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('premium_member');
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456789',
        'nik' => '1234567890123456',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1990-01-01',
        'alamat_lengkap' => 'Alamat Lengkap',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2008',
        's1_tahun_tamat' => '2012',
    ]);

    $product = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Tes Produk',
        'slug' => 'tes-produk',
        'sku' => 'TEST-001',
        'description' => 'Produk untuk pengujian diskon',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
        'status' => 'active',
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 2,
        'price_snapshot' => 50000,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'User Premium',
        'shipping_phone' => '08123456789',
        'shipping_address' => 'Alamat Premium',
        'notes' => 'Catatan',
    ]);

    $response->assertRedirect();

    $order = Order::latest('id')->first();
    $payment = Payment::where('order_id', $order->id)->latest('id')->first();

    expect($order)->not()->toBeNull();
    expect($order->total_amount)->toBe(100000);
    expect($order->grand_total)->toBe(90000);
    expect($order->shipping_cost)->toBe(0);

    expect($payment)->not()->toBeNull();
    expect((int) $payment->amount)->toBe(90000);
});

it('does not apply discount for non premium member', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456780',
        'nik' => '1234567890123457',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1991-02-02',
        'alamat_lengkap' => 'Alamat Lengkap Biasa',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2009',
        's1_tahun_tamat' => '2013',
    ]);

    $product = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Tes Produk Non Premium',
        'slug' => 'tes-produk-non-premium',
        'sku' => 'TEST-002',
        'description' => 'Produk untuk pengujian tanpa diskon',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
        'status' => 'active',
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 2,
        'price_snapshot' => 50000,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'User Biasa',
        'shipping_phone' => '08123456780',
        'shipping_address' => 'Alamat Biasa',
        'notes' => 'Catatan',
    ]);

    $response->assertRedirect();

    $order = Order::latest('id')->first();
    $payment = Payment::where('order_id', $order->id)->latest('id')->first();

    expect($order)->not()->toBeNull();
    expect($order->total_amount)->toBe(100000);
    expect($order->grand_total)->toBe(100000);
    expect($order->shipping_cost)->toBe(0);

    expect($payment)->not()->toBeNull();
    expect((int) $payment->amount)->toBe(100000);
});

it('applies discount only to physical items for premium member with mixed cart', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('premium_member');
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456781',
        'nik' => '1234567890123458',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1992-03-03',
        'alamat_lengkap' => 'Alamat Premium Campuran',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2010',
        's1_tahun_tamat' => '2014',
    ]);

    $physicalProduct = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => null,
        'name' => 'Merchandise Fisik',
        'slug' => 'merchandise-fisik',
        'sku' => 'PHY-001',
        'description' => 'Produk fisik untuk pengujian diskon',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $digitalCourse = \App\Models\Course::create([
        'title' => 'Course Digital',
        'slug' => 'course-digital',
        'status' => 'published',
        'is_paid' => false,
    ]);

    $digitalProduct = Product::create([
        'organization_id' => null,
        'product_category_id' => null,
        'course_id' => $digitalCourse->id,
        'name' => 'Produk Digital',
        'slug' => 'produk-digital',
        'sku' => 'DIG-001',
        'description' => 'Produk digital tanpa diskon',
        'price' => 150000,
        'stock' => 10,
        'type' => 'digital',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
        'status' => 'active',
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $physicalProduct->id,
        'product_variant_id' => null,
        'quantity' => 2,
        'price_snapshot' => 50000,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $digitalProduct->id,
        'product_variant_id' => null,
        'quantity' => 1,
        'price_snapshot' => 150000,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'User Premium Mixed',
        'shipping_phone' => '08123456781',
        'shipping_address' => 'Alamat Premium Mixed',
        'notes' => 'Catatan Mixed',
    ]);

    $response->assertRedirect();

    $order = Order::latest('id')->first();
    $payment = Payment::where('order_id', $order->id)->latest('id')->first();

    expect($order)->not()->toBeNull();
    expect($order->total_amount)->toBe(250000);
    expect($order->grand_total)->toBe(240000);
    expect($order->shipping_cost)->toBe(0);

    expect($payment)->not()->toBeNull();
    expect((int) $payment->amount)->toBe(240000);
});

it('uses configurable rate and category whitelist for premium discount', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    SiteSetting::create([
        'key' => 'shop_premium_member_discount_rate',
        'value' => 0.2,
        'type' => 'text',
        'group' => 'shop',
        'label' => 'Premium Member Discount Rate',
        'description' => 'Persentase diskon member premium (0-1).',
    ]);

    SiteSetting::create([
        'key' => 'shop_premium_member_discount_category_slugs',
        'value' => ['merchandise'],
        'type' => 'text',
        'group' => 'shop',
        'label' => 'Premium Member Discount Categories',
        'description' => 'Daftar slug kategori produk fisik yang mendapatkan diskon.',
    ]);

    $user = User::factory()->create();
    $user->assignRole('premium_member');
    $user->assignRole('subscriber');
    $user->update([
        'wa' => '08123456782',
        'nik' => '1234567890123459',
        'tempat_lahir' => 'Medan',
        'tanggal_lahir' => '1993-04-04',
        'alamat_lengkap' => 'Alamat Premium Kategori',
        's1_fakultas' => 'Fakultas',
        's1_prodi' => 'Prodi',
        's1_tahun_masuk' => '2011',
        's1_tahun_tamat' => '2015',
    ]);

    $merchCategory = ProductCategory::create([
        'organization_id' => null,
        'name' => 'Merchandise',
        'slug' => 'merchandise',
        'description' => 'Kategori merchandise resmi.',
        'is_active' => true,
    ]);

    $otherCategory = ProductCategory::create([
        'organization_id' => null,
        'name' => 'Lainnya',
        'slug' => 'lainnya',
        'description' => 'Kategori lain.',
        'is_active' => true,
    ]);

    $discountedProduct = Product::create([
        'organization_id' => null,
        'product_category_id' => $merchCategory->id,
        'course_id' => null,
        'name' => 'Kaos Alumni',
        'slug' => 'kaos-alumni',
        'sku' => 'MERCH-001',
        'description' => 'Kaos resmi alumni.',
        'price' => 100000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $nonDiscountedProduct = Product::create([
        'organization_id' => null,
        'product_category_id' => $otherCategory->id,
        'course_id' => null,
        'name' => 'Merchandise Lain',
        'slug' => 'merchandise-lain',
        'sku' => 'OTHER-001',
        'description' => 'Merchandise di luar kategori diskon.',
        'price' => 50000,
        'stock' => 10,
        'type' => 'physical',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $cart = Cart::create([
        'user_id' => $user->id,
        'session_id' => null,
        'status' => 'active',
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $discountedProduct->id,
        'product_variant_id' => null,
        'quantity' => 1,
        'price_snapshot' => 100000,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $nonDiscountedProduct->id,
        'product_variant_id' => null,
        'quantity' => 1,
        'price_snapshot' => 50000,
    ]);

    $this->actingAs($user);

    $response = $this->post(route('shop.checkout.process'), [
        'shipping_name' => 'User Premium Kategori',
        'shipping_phone' => '08123456782',
        'shipping_address' => 'Alamat Premium Kategori',
        'notes' => 'Catatan Kategori',
    ]);

    $response->assertRedirect();

    $order = Order::latest('id')->first();
    $payment = Payment::where('order_id', $order->id)->latest('id')->first();

    expect($order)->not()->toBeNull();
    expect($order->total_amount)->toBe(150000);
    expect($order->grand_total)->toBe(130000);
    expect($order->shipping_cost)->toBe(0);

    expect($payment)->not()->toBeNull();
    expect((int) $payment->amount)->toBe(130000);
});

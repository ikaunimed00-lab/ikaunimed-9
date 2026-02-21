<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->where('is_published', true)
            ->with(['category', 'images' => fn ($q) => $q->orderBy('sort_order')])
            ->when($request->category, function ($q, $category) {
                $q->whereHas('category', function ($sub) use ($category) {
                    $sub->where('slug', $category);
                });
            })
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', '%' . $search . '%');
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $categories = \App\Models\ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'category']),
            'categories' => $categories,
        ]);
    }

    public function show(Product $product): Response
    {
        if (! $product->is_published) {
            abort(404);
        }

        $product->load([
            'category',
            'images' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $related = Product::query()
            ->where('is_published', true)
            ->where('id', '!=', $product->id)
            ->when($product->product_category_id, function ($q) use ($product) {
                $q->where('product_category_id', $product->product_category_id);
            })
            ->limit(4)
            ->get();

        return Inertia::render('Shop/Show', [
            'product' => $product,
            'related' => $related,
        ]);
    }

    public function cart(Request $request): Response
    {
        $user = $request->user();

        $cart = Cart::query()
            ->where('user_id', $user?->id)
            ->where('status', 'active')
            ->with([
                'items.product.images' => fn ($q) => $q->orderBy('sort_order'),
            ])
            ->first();

        return Inertia::render('Shop/Cart', [
            'cart' => $cart,
        ]);
    }

    public function checkout(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        $cart = Cart::query()
            ->where('user_id', $user?->id)
            ->where('status', 'active')
            ->with([
                'items.product.images' => fn ($q) => $q->orderBy('sort_order'),
            ])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('shop.cart.index')
                ->with('error', 'Keranjang Anda masih kosong.');
        }

        $shippingDefaults = [
            'name' => $user->name,
            'phone' => $user->wa,
            'address' => $user->alamat_lengkap,
        ];

        return Inertia::render('Shop/Checkout', [
            'cart' => $cart,
            'shippingDefaults' => $shippingDefaults,
        ]);
    }

    public function processCheckout(Request $request): RedirectResponse
    {
        $user = $request->user();

        $cart = Cart::query()
            ->where('user_id', $user?->id)
            ->where('status', 'active')
            ->with(['items.product'])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('shop.cart.index')
                ->with('error', 'Keranjang Anda masih kosong.');
        }

        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:50'],
            'shipping_address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $totalAmount = $cart->items->reduce(function ($carry, $item) {
            return $carry + ($item->price_snapshot * $item->quantity);
        }, 0);

        $shippingCost = 0;
        $grandTotal = $totalAmount + $shippingCost;

        $tripayItems = $cart->items->map(function ($item) {
            $product = $item->product;

            return [
                'sku' => $product->sku ?? (string) $product->id,
                'name' => $product->name,
                'price' => (int) $item->price_snapshot,
                'quantity' => $item->quantity,
                'subtotal' => (int) ($item->price_snapshot * $item->quantity),
            ];
        })->values()->all();

        $defaultMethod = config('services.tripay.default_method', 'BRIVA');

        $order = DB::transaction(function () use ($cart, $user, $data, $totalAmount, $shippingCost, $grandTotal, $defaultMethod) {
            $order = Order::create([
                'user_id' => $user->id,
                'organization_id' => $cart->items->first()?->product?->organization_id,
                'status' => 'awaiting_payment',
                'fulfillment_status' => 'unfulfilled',
                'total_amount' => $totalAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'shipping_address' => [
                    'name' => $data['shipping_name'],
                    'phone' => $data['shipping_phone'],
                    'address' => $data['shipping_address'],
                ],
                'notes' => $data['notes'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            foreach ($cart->items as $item) {
                $lineTotal = $item->price_snapshot * $item->quantity;

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_type' => $item->product->type,
                    'quantity' => $item->quantity,
                    'price' => $item->price_snapshot,
                    'total' => $lineTotal,
                ]);
            }

            $order->payments()->create([
                'user_id' => $user->id,
                'amount' => $grandTotal,
                'provider' => 'tripay',
                'provider_reference' => null,
                'method' => $defaultMethod,
                'status' => Payment::STATUS_PENDING,
                'paid_at' => null,
                'raw_payload' => null,
            ]);

            $cart->update(['status' => 'converted']);

            return $order;
        });

        $payment = $order->payments()->latest('id')->first();

        $config = config('services.tripay');

        $apiKey = $config['api_key'] ?? null;
        $privateKey = $config['private_key'] ?? null;
        $merchantCode = $config['merchant_code'] ?? null;
        $mode = $config['mode'] ?? 'sandbox';
        $method = $payment->method;

        if (! $apiKey || ! $privateKey || ! $merchantCode) {
            return redirect()
                ->route('shop.cart.index')
                ->with('error', 'Konfigurasi pembayaran belum lengkap. Silakan hubungi administrator.');
        }

        $amountInt = (int) $order->grand_total;
        $merchantRef = 'ORD-' . $order->id;
        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amountInt, $privateKey);

        $baseUrl = $mode === 'sandbox'
            ? 'https://tripay.co.id/api-sandbox'
            : 'https://tripay.co.id/api';

        $customerName = $order->shipping_address['name'] ?? $user->name;
        $customerEmail = $user->email;
        $customerPhone = $order->shipping_address['phone'] ?? null;

        $payload = [
            'method' => $method,
            'merchant_ref' => $merchantRef,
            'amount' => $amountInt,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'order_items' => $tripayItems,
            'callback_url' => config('app.url') . '/webhook/tripay/shop',
            'return_url' => config('app.url') . '/shop/orders',
            'expired_time' => now()->addDay()->timestamp,
            'signature' => $signature,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($baseUrl . '/transaction/create', $payload);

        if (! $response->successful()) {
            return redirect()
                ->route('shop.cart.index')
                ->with('error', 'Gagal membuat transaksi pembayaran. Silakan coba lagi atau hubungi administrator.');
        }

        $body = $response->json();
        $data = $body['data'] ?? null;

        if (! is_array($data) || empty($data['reference']) || empty($data['checkout_url'])) {
            return redirect()
                ->route('shop.cart.index')
                ->with('error', 'Respon pembayaran tidak valid. Silakan coba lagi atau hubungi administrator.');
        }

        $payment->update([
            'provider_reference' => $data['reference'],
            'raw_payload' => $body,
        ]);

        return redirect()->away($data['checkout_url']);
    }

    public function orders(Request $request): Response
    {
        $user = $request->user();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->withCount('items')
            ->with(['payments' => function ($q) {
                $q->latest('id');
            }])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Shop/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function showOrder(Request $request, Order $order): Response
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load([
            'items.product',
            'payments' => function ($q) {
                $q->latest('id');
            },
        ]);

        $latestPayment = $order->payments->first();

        return Inertia::render('Shop/Orders/Show', [
            'order' => $order,
            'payment' => $latestPayment,
        ]);
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        if (! $product->is_published) {
            abort(404);
        }

        $user = $request->user();

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user?->id,
                'status' => 'active',
            ],
            [
                'session_id' => null,
            ]
        );

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->increment('quantity');
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price_snapshot' => $product->price,
            ]);
        }

        return redirect()
            ->route('shop.cart.index')
            ->with('success', 'Produk ditambahkan ke keranjang.');
    }
}

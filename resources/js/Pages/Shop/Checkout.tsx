import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';
import MainLayout from '@/components/MainLayout';

type ProductImage = {
  id: number;
  path: string;
  is_primary: boolean;
  sort_order: number;
};

type Product = {
  id: number;
  name: string;
  slug: string;
  price: string;
  type: string;
  images: ProductImage[];
};

type CartItem = {
  id: number;
  quantity: number;
  price_snapshot: string;
  product: Product;
};

type Cart = {
  id: number;
  items: CartItem[];
};

type ShippingDefaults = {
  name: string | null;
  phone: string | null;
  address: string | null;
};

type PageProps = {
  cart: Cart;
  shippingDefaults: ShippingDefaults;
};

const renderTypeBadge = (type: string) => {
  if (type === 'digital') {
    return (
      <span className="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
        Digital
      </span>
    );
  }

  if (type === 'service') {
    return (
      <span className="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
        Jasa
      </span>
    );
  }

  return null;
};

export default function CheckoutPage() {
  const { cart, shippingDefaults } = usePage<PageProps>().props;

  const items = cart.items ?? [];

  const total = items.reduce((sum, item) => {
    return sum + Number(item.price_snapshot) * item.quantity;
  }, 0);

  const hasProfileAddress =
    !!shippingDefaults?.name &&
    !!shippingDefaults?.phone &&
    !!shippingDefaults?.address;

  const [addressMode, setAddressMode] = React.useState<
    'profile' | 'custom'
  >(hasProfileAddress ? 'profile' : 'custom');

  const { data, setData, post, processing, errors } = useForm({
    shipping_name: shippingDefaults?.name ?? '',
    shipping_phone: shippingDefaults?.phone ?? '',
    shipping_address: shippingDefaults?.address ?? '',
    notes: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('shop.checkout.process'));
  };

  return (
    <MainLayout variant="full">
      <div className="max-w-5xl mx-auto px-4 py-8">
      <div className="mb-4">
        <Link href={route('shop.cart.index')} className="text-sm text-blue-600">
          &larr; Kembali ke keranjang
        </Link>
      </div>

      <h1 className="text-2xl font-semibold mb-6">Checkout</h1>

      <div className="grid md:grid-cols-3 gap-8">
        <div className="md:col-span-2 space-y-4">
          <form onSubmit={handleSubmit} className="bg-white border rounded-lg p-6 space-y-4">
            <div className="space-y-2">
              <p className="text-sm font-medium text-gray-700">Alamat Pengiriman</p>
              {hasProfileAddress && (
                <div className="space-y-1 text-sm">
                  <div className="flex items-center gap-2">
                    <input
                      type="radio"
                      id="address_profile"
                      name="address_mode"
                      className="h-4 w-4 text-emerald-600 border-gray-300"
                      checked={addressMode === 'profile'}
                      onChange={() => {
                        setAddressMode('profile');
                        setData('shipping_name', shippingDefaults.name ?? '');
                        setData('shipping_phone', shippingDefaults.phone ?? '');
                        setData('shipping_address', shippingDefaults.address ?? '');
                      }}
                    />
                    <label htmlFor="address_profile" className="text-sm text-gray-700">
                      Gunakan data profil
                    </label>
                  </div>
                  <div className="ml-6 rounded-md border bg-gray-50 p-3 text-xs text-gray-700 space-y-1">
                    <div className="font-semibold">
                      {shippingDefaults.name ?? '-'}
                    </div>
                    <div>{shippingDefaults.phone ?? '-'}</div>
                    <div className="whitespace-pre-line">
                      {shippingDefaults.address ?? '-'}
                    </div>
                  </div>
                  <div className="flex items-center gap-2 pt-2">
                    <input
                      type="radio"
                      id="address_custom"
                      name="address_mode"
                      className="h-4 w-4 text-emerald-600 border-gray-300"
                      checked={addressMode === 'custom'}
                      onChange={() => setAddressMode('custom')}
                    />
                    <label htmlFor="address_custom" className="text-sm text-gray-700">
                      Gunakan alamat lain
                    </label>
                  </div>
                </div>
              )}
              {!hasProfileAddress && (
                <p className="text-xs text-gray-500">
                  Lengkapi data penerima di bawah ini untuk pengiriman.
                </p>
              )}
            </div>

            {(addressMode === 'custom' || !hasProfileAddress) && (
              <>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Nama Penerima
                  </label>
                  <input
                    type="text"
                    value={data.shipping_name}
                    onChange={(e) => setData('shipping_name', e.target.value)}
                    className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                  />
                  {errors.shipping_name && (
                    <p className="mt-1 text-xs text-red-600">{errors.shipping_name}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Nomor HP/WA
                  </label>
                  <input
                    type="text"
                    value={data.shipping_phone}
                    onChange={(e) => setData('shipping_phone', e.target.value)}
                    className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                  />
                  {errors.shipping_phone && (
                    <p className="mt-1 text-xs text-red-600">{errors.shipping_phone}</p>
                  )}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Alamat Lengkap
                  </label>
                  <textarea
                    value={data.shipping_address}
                    onChange={(e) => setData('shipping_address', e.target.value)}
                    className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                    rows={4}
                  />
                  {errors.shipping_address && (
                    <p className="mt-1 text-xs text-red-600">{errors.shipping_address}</p>
                  )}
                </div>
              </>
            )}

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Catatan (opsional)
              </label>
              <textarea
                value={data.notes}
                onChange={(e) => setData('notes', e.target.value)}
                className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                rows={3}
              />
              {errors.notes && (
                <p className="mt-1 text-xs text-red-600">{errors.notes}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={processing}
              className="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50"
            >
              Buat Order
            </button>
          </form>
        </div>

        <div className="bg-white border rounded-lg p-6 h-fit space-y-4">
          <h2 className="text-lg font-semibold mb-2">Ringkasan Pesanan</h2>

          <div className="space-y-3 max-h-64 overflow-y-auto pr-1">
            {items.map((item) => {
              const product = item.product;
              const primaryImage =
                product.images.find((img) => img.is_primary) ?? product.images[0];

              const lineTotal =
                Number(item.price_snapshot) * item.quantity;

              return (
                <div key={item.id} className="flex gap-3">
                  {primaryImage && (
                    <img
                      src={`/storage/${primaryImage.path}`}
                      alt={product.name}
                      className="w-14 h-14 object-cover rounded"
                    />
                  )}
                  <div className="flex-1">
                    <div className="text-xs text-gray-500 flex items-center">
                      <span className="truncate">{product.name}</span>
                      {renderTypeBadge(product.type)}
                    </div>
                    <div className="flex items-center justify-between mt-1 text-xs text-gray-500">
                      <span>Qty: {item.quantity}</span>
                      <span>
                        Rp {lineTotal.toLocaleString('id-ID')}
                      </span>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          <div className="border-t pt-4 mt-2 space-y-2 text-sm">
            <div className="flex items-center justify-between">
              <span>Subtotal</span>
              <span>Rp {total.toLocaleString('id-ID')}</span>
            </div>
            <div className="flex items-center justify-between text-gray-500">
              <span>Ongkir</span>
              <span>Belum termasuk</span>
            </div>
            <div className="flex items-center justify-between font-semibold">
              <span>Total</span>
              <span>Rp {total.toLocaleString('id-ID')}</span>
            </div>
          </div>
        </div>
      </div>
      </div>
    </MainLayout>
  );
}

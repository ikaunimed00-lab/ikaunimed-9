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

type ProductCategory = {
  id: number;
  name: string;
  slug: string;
};

type Product = {
  id: number;
  name: string;
  slug: string;
  price: string;
  type: string;
  images: ProductImage[];
  category?: ProductCategory | null;
};

type AuthUser = {
  id: number;
  name: string;
  email: string;
  role: string;
  roles: string[];
  permissions: string[];
};

type SharedAuth = {
  user: AuthUser | null;
  dashboard_route: string;
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

type PremiumDiscountConfig = {
  rate: number;
  categorySlugs: string[] | null;
};

type ShippingDefaults = {
  name: string | null;
  phone: string | null;
  address: string | null;
};

type PageProps = {
  auth: SharedAuth;
  cart: Cart;
  shippingDefaults: ShippingDefaults;
  premiumDiscount: PremiumDiscountConfig;
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
  const { cart, shippingDefaults, auth, premiumDiscount } =
    usePage<PageProps>().props;

  const [couponStatus, setCouponStatus] = React.useState<{
    valid: boolean;
    message: string;
    discount: number;
    code: string;
  } | null>(null);

  const [checkingCoupon, setCheckingCoupon] = React.useState(false);

  const items = cart.items ?? [];

  const subtotal = items.reduce((sum, item) => {
    return sum + Number(item.price_snapshot) * item.quantity;
  }, 0);

  const eligibleCategorySlugs =
    premiumDiscount?.categorySlugs && premiumDiscount.categorySlugs.length > 0
      ? premiumDiscount.categorySlugs
      : null;

  const physicalSubtotal = items
    .filter((item) => {
      if (item.product.type !== 'physical') {
        return false;
      }

      if (!eligibleCategorySlugs) {
        return true;
      }

      const categorySlug = item.product.category?.slug;

      if (!categorySlug) {
        return false;
      }

      return eligibleCategorySlugs.includes(categorySlug);
    })
    .reduce((sum, item) => {
      return sum + Number(item.price_snapshot) * item.quantity;
    }, 0);

  const isPremiumMember =
    !!auth?.user &&
    Array.isArray(auth.user.roles) &&
    auth.user.roles.includes('premium_member');

  const discountRate =
    typeof premiumDiscount?.rate === 'number' && premiumDiscount.rate > 0
      ? premiumDiscount.rate
      : 0.1;
  const discount =
    isPremiumMember && physicalSubtotal > 0
      ? Math.floor(physicalSubtotal * discountRate)
      : 0;

  const couponDiscount = couponStatus?.valid ? couponStatus.discount : 0;
  const total = Math.max(0, subtotal - discount - couponDiscount);

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
    coupon_code: '',
  });

  const checkCoupon = async () => {
    if (!data.coupon_code) return;
    setCheckingCoupon(true);
    setCouponStatus(null);

    try {
      const response = await fetch(route('shop.checkout.check-coupon'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
        body: JSON.stringify({ coupon_code: data.coupon_code }),
      });
      
      const result = await response.json();
      
      // Handle non-200 responses that return JSON
      if (!response.ok) {
        setCouponStatus({ 
            valid: false, 
            message: result.message || 'Terjadi kesalahan saat mengecek kupon.', 
            discount: 0, 
            code: '' 
        });
        return;
      }

      setCouponStatus(result);
    } catch (error) {
      console.error(error);
      setCouponStatus({ 
        valid: false, 
        message: 'Terjadi kesalahan saat mengecek kupon.', 
        discount: 0, 
        code: '' 
      });
    } finally {
      setCheckingCoupon(false);
    }
  };

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

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Kupon Diskon (opsional)
              </label>
              <div className="flex gap-2">
                <input
                  type="text"
                  value={data.coupon_code}
                  onChange={(e) => {
                    setData('coupon_code', e.target.value.toUpperCase());
                    if (couponStatus) setCouponStatus(null);
                  }}
                  className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 uppercase"
                  placeholder="KODE KUPON"
                />
                <button
                  type="button"
                  onClick={checkCoupon}
                  disabled={checkingCoupon || !data.coupon_code}
                  className="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                >
                  {checkingCoupon ? 'Cek...' : 'Cek'}
                </button>
              </div>

              {couponStatus && (
                <p className={`mt-1 text-xs ${couponStatus.valid ? 'text-emerald-600' : 'text-red-600'}`}>
                  {couponStatus.message}
                </p>
              )}

              {errors.coupon_code && (
                <p className="mt-1 text-xs text-red-600">{errors.coupon_code}</p>
              )}
              <p className="mt-1 text-xs text-gray-500">
                Jika kupon valid, diskon akan dihitung pada total pembayaran.
              </p>
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
              <span>Rp {subtotal.toLocaleString('id-ID')}</span>
            </div>
            {discount > 0 && (
              <div className="flex items-center justify-between text-emerald-700">
                <span>Diskon Member Premium</span>
                <span>- Rp {discount.toLocaleString('id-ID')}</span>
              </div>
            )}
            {couponDiscount > 0 && (
              <div className="flex items-center justify-between text-emerald-700">
                <span>Diskon Kupon ({couponStatus?.code})</span>
                <span>- Rp {couponDiscount.toLocaleString('id-ID')}</span>
              </div>
            )}
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

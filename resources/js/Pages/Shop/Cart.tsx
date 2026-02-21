import { Link, usePage, router } from '@inertiajs/react';
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

type PageProps = {
  cart: Cart | null;
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

export default function CartPage() {
  const { cart } = usePage<PageProps>().props;

  const items = cart?.items ?? [];

  const total = items.reduce((sum, item) => {
    return sum + Number(item.price_snapshot) * item.quantity;
  }, 0);

  return (
    <MainLayout variant="full">
      <div className="max-w-5xl mx-auto px-4 py-8">
      <div className="mb-4">
        <Link href={route('shop.index')} className="text-sm text-blue-600">
          &larr; Kembali ke katalog
        </Link>
      </div>

      <h1 className="text-2xl font-semibold mb-6">Keranjang Belanja</h1>

      {items.length === 0 ? (
        <div className="bg-white border rounded-lg p-6 text-center">
          <p className="text-gray-600 mb-4">Keranjang Anda masih kosong.</p>
          <Link
            href={route('shop.index')}
            className="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
          >
            Mulai Belanja
          </Link>
        </div>
      ) : (
        <div className="grid md:grid-cols-3 gap-8">
          <div className="md:col-span-2 space-y-4">
            {items.map((item) => {
              const product = item.product;
              const primaryImage =
                product.images.find((img) => img.is_primary) ??
                product.images[0];

              const lineTotal =
                Number(item.price_snapshot) * item.quantity;

              return (
                <div
                  key={item.id}
                  className="bg-white border rounded-lg p-4 flex gap-4"
                >
                  {primaryImage && (
                    <img
                      src={`/storage/${primaryImage.path}`}
                      alt={product.name}
                      className="w-24 h-24 object-cover rounded"
                    />
                  )}
                  <div className="flex-1 flex flex-col">
                    <div className="flex items-center justify-between mb-1">
                      <div className="text-sm text-gray-500 flex items-center">
                        <span>{product.name}</span>
                        {renderTypeBadge(product.type)}
                      </div>
                      <div className="text-sm text-gray-500">
                        Qty: {item.quantity}
                      </div>
                    </div>
                    <div className="mt-auto flex items-center justify-between">
                      <div className="text-sm text-gray-500">
                        Harga: Rp{' '}
                        {Number(item.price_snapshot).toLocaleString('id-ID')}
                      </div>
                      <div className="font-semibold text-primary-600">
                        Rp {lineTotal.toLocaleString('id-ID')}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>

          <div className="bg-white border rounded-lg p-6 h-fit">
            <h2 className="text-lg font-semibold mb-4">Ringkasan</h2>
            <div className="flex items-center justify-between mb-2 text-sm">
              <span>Subtotal</span>
              <span>Rp {total.toLocaleString('id-ID')}</span>
            </div>
            <div className="flex items-center justify-between mb-4 text-sm text-gray-500">
              <span>Ongkir</span>
              <span>Belum termasuk</span>
            </div>
            <div className="flex items-center justify-between mb-6 font-semibold">
              <span>Total</span>
              <span>Rp {total.toLocaleString('id-ID')}</span>
            </div>
            <button
              type="button"
              onClick={() => {
                if (items.length === 0) return;
                router.get(route('shop.checkout'));
              }}
              className="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
            >
              Lanjut ke Checkout (segera)
            </button>
          </div>
        </div>
      )}
      </div>
    </MainLayout>
  );
}

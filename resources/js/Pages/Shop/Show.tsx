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
  description: string;
  price: string;
  type: string;
  images: ProductImage[];
  category?: {
    id: number;
    name: string;
    slug: string;
  } | null;
};

type PageProps = {
  product: Product;
  related: Product[];
};

export default function ShopShow() {
  const { product, related } = usePage<PageProps>().props;

  const primaryImage =
    product.images.find((img) => img.is_primary) ?? product.images[0];

  const handleAddToCart = () => {
    router.post(route('shop.cart.add', product.slug));
  };

  return (
    <MainLayout variant="full">
      <div className="max-w-5xl mx-auto px-4 py-8">
      <div className="mb-4">
        <Link href={route('shop.index')} className="text-sm text-blue-600">
          &larr; Kembali ke katalog
        </Link>
      </div>

      <div className="grid md:grid-cols-2 gap-8">
        <div>
          {primaryImage && (
            <img
              src={`/storage/${primaryImage.path}`}
              alt={product.name}
              className="w-full rounded-lg object-cover"
            />
          )}
          {product.images.length > 1 && (
            <div className="mt-4 grid grid-cols-4 gap-2">
              {product.images.map((img) => (
                <img
                  key={img.id}
                  src={`/storage/${img.path}`}
                  alt={product.name}
                  className="w-full h-20 object-cover rounded border"
                />
              ))}
            </div>
          )}
        </div>

        <div>
          <div className="text-sm text-gray-500 mb-1">
            {product.category?.name}
          </div>
          <h1 className="text-2xl font-semibold mb-2">{product.name}</h1>
          <div className="text-xl font-bold text-primary-600 mb-4">
            Rp {Number(product.price).toLocaleString('id-ID')}
          </div>
          <button
            type="button"
            onClick={handleAddToCart}
            className="inline-flex items-center px-4 py-2 mb-4 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
          >
            Tambah ke Keranjang
          </button>
          {product.description && (
            <div
              className="prose max-w-none"
              dangerouslySetInnerHTML={{ __html: product.description }}
            />
          )}
        </div>
      </div>

      {related.length > 0 && (
        <div className="mt-10">
          <h2 className="text-lg font-semibold mb-4">Produk Terkait</h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            {related.map((item) => {
              const relPrimary =
                item.images.find((img) => img.is_primary) ?? item.images[0];

              return (
                <Link
                  key={item.id}
                  href={route('shop.show', item.slug)}
                  className="border rounded-lg overflow-hidden hover:shadow-md transition-shadow bg-white"
                >
                  {relPrimary && (
                    <img
                      src={`/storage/${relPrimary.path}`}
                      alt={item.name}
                      className="w-full h-40 object-cover"
                    />
                  )}
                  <div className="p-4">
                    <div className="text-sm text-gray-500 mb-1">
                      {item.category?.name}
                    </div>
                    <h3 className="font-semibold mb-1 line-clamp-2">
                      {item.name}
                    </h3>
                    <div className="font-bold text-primary-600">
                      Rp {Number(item.price).toLocaleString('id-ID')}
                    </div>
                  </div>
                </Link>
              );
            })}
          </div>
        </div>
      )}
      </div>
    </MainLayout>
  );
}

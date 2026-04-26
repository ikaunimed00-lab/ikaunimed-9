import { Link, usePage, router } from '@inertiajs/react';
import { route } from 'ziggy-js';
import { useState } from 'react';
import MainLayout from '@/components/MainLayout';
import { SHOP_COPY } from './copy';

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
  category?: {
    id: number;
    name: string;
    slug: string;
  } | null;
};

type PaginatedProducts = {
  data: Product[];
};

type PageProps = {
  auth: {
    user: {
      id: number;
      roles: string[];
    } | null;
  };
  products: PaginatedProducts;
  filters: {
    search?: string;
    category?: string;
  };
  categories: {
    id: number;
    name: string;
    slug: string;
  }[];
};

export default function ShopIndex() {
  const { auth, products, filters, categories } = usePage<PageProps>().props;

  const [search, setSearch] = useState(filters.search || '');
  const [category, setCategory] = useState(filters.category || '');

  const items = products.data ?? [];
  const canAddToCart =
    !!auth?.user &&
    Array.isArray(auth.user.roles) &&
    auth.user.roles.includes('subscriber');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.get(
      route('shop.index'),
      {
        search,
        category,
      },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const resetFilters = () => {
    setSearch('');
    setCategory('');
    router.get(route('shop.index'));
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

  return (
    <MainLayout variant="full">
      <div className="max-w-6xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-semibold mb-6">{SHOP_COPY.heading.shop}</h1>

      <form
        onSubmit={handleSubmit}
        className="mb-6 flex flex-col md:flex-row gap-3 items-stretch md:items-center"
      >
        <div className="flex-1">
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Cari produk..."
            className="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
          />
        </div>
        <div className="w-full md:w-56">
          <select
            value={category}
            onChange={(e) => setCategory(e.target.value)}
            className="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
          >
            <option value="">Semua Kategori</option>
            {categories.map((cat) => (
              <option key={cat.id} value={cat.slug}>
                {cat.name}
              </option>
            ))}
          </select>
        </div>
        <div className="flex gap-2">
          <button
            type="submit"
            className="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-md hover:bg-emerald-700"
          >
            Cari
          </button>
          {(filters.search || filters.category) && (
            <button
              type="button"
              onClick={resetFilters}
              className="px-3 py-2 border text-sm rounded-md text-gray-600 hover:bg-gray-50"
            >
              Reset
            </button>
          )}
        </div>
      </form>

      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        {items.map((product) => {
          const primaryImage =
            product.images.find((img) => img.is_primary) ?? product.images[0];

          return (
            <Link
              key={product.id}
              href={route('shop.show', product.slug)}
              className="border rounded-lg overflow-hidden hover:shadow-md transition-shadow bg-white flex flex-col"
            >
              {primaryImage && (
                <img
                  src={`/storage/${primaryImage.path}`}
                  alt={product.name}
                  className="w-full h-48 object-cover"
                />
              )}
              <div className="p-4 flex-1 flex flex-col">
                <div className="text-xs text-gray-500 mb-1 flex items-center">
                  <span>{product.category?.name}</span>
                  {renderTypeBadge(product.type)}
                </div>
                <h2 className="font-semibold mb-2 line-clamp-2 flex-1">
                  {product.name}
                </h2>
                <div className="font-bold text-primary-600 mt-1 mb-3">
                  Rp {Number(product.price).toLocaleString('id-ID')}
                </div>
                {canAddToCart ? (
                  <button
                    type="button"
                    onClick={(e) => {
                      e.preventDefault();
                      router.post(route('shop.cart.add', product.slug));
                    }}
                    className="mt-auto inline-flex items-center justify-center px-3 py-2 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700"
                  >
                    {SHOP_COPY.cta.addToCart}
                  </button>
                ) : (
                  <Link
                    href={route('login')}
                    className="mt-auto inline-flex items-center justify-center px-3 py-2 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700"
                  >
                    {SHOP_COPY.cta.loginToShop}
                  </Link>
                )}
              </div>
            </Link>
          );
        })}
      </div>
      </div>
    </MainLayout>
  );
}

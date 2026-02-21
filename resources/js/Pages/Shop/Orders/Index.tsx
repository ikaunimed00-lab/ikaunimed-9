import { Link, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';

type Order = {
  id: number;
  status: string;
  fulfillment_status: string;
  grand_total: string;
  created_at: string;
  items_count: number;
  payments: {
    id: number;
    status: string;
  }[];
};

type PaginatedOrders = {
  data: Order[];
};

type PageProps = {
  orders: PaginatedOrders;
};

const formatRupiah = (value: string | number) => {
  const num = typeof value === 'string' ? Number(value) : value;
  return num.toLocaleString('id-ID');
};

const paymentStatusLabel = (status: string) => {
  if (status === 'paid') return 'Sudah Dibayar';
  if (status === 'pending') return 'Menunggu Pembayaran';
  if (status === 'expired') return 'Kadaluarsa';
  if (status === 'failed') return 'Gagal';
  return status;
};

const fulfillmentStatusLabel = (status: string) => {
  if (status === 'unfulfilled') return 'Belum Diproses';
  if (status === 'processing') return 'Sedang Diproses';
  if (status === 'shipped') return 'Dikirim';
  if (status === 'completed') return 'Selesai';
  return status;
};

const statusBadgeClass = (status: string) => {
  if (status === 'paid') {
    return 'bg-emerald-100 text-emerald-800';
  }

  if (status === 'pending') {
    return 'bg-yellow-100 text-yellow-800';
  }

  if (status === 'canceled' || status === 'failed' || status === 'expired') {
    return 'bg-red-100 text-red-800';
  }

  return 'bg-gray-100 text-gray-800';
};

const fulfillmentBadgeClass = (status: string) => {
  if (status === 'unfulfilled') {
    return 'bg-gray-100 text-gray-800';
  }

  if (status === 'processing') {
    return 'bg-blue-100 text-blue-800';
  }

  if (status === 'shipped') {
    return 'bg-indigo-100 text-indigo-800';
  }

  if (status === 'completed') {
    return 'bg-emerald-100 text-emerald-800';
  }

  return 'bg-gray-100 text-gray-800';
};

export default function OrdersIndex() {
  const { orders } = usePage<PageProps>().props;

  const items = orders.data ?? [];

  return (
    <div className="max-w-5xl mx-auto px-4 py-8">
      <div className="mb-4">
        <Link href={route('shop.index')} className="text-sm text-blue-600">
          &larr; Kembali ke katalog
        </Link>
      </div>

      <h1 className="text-2xl font-semibold mb-6">Riwayat Pesanan</h1>

      {items.length === 0 ? (
        <div className="bg-white border rounded-lg p-6 text-center">
          <p className="text-gray-600 mb-4">
            Anda belum memiliki pesanan.
          </p>
          <Link
            href={route('shop.index')}
            className="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"
          >
            Mulai Belanja
          </Link>
        </div>
      ) : (
        <div className="space-y-3">
          {items.map((order) => {
            const latestPayment = order.payments[0];
            const paymentStatus = latestPayment?.status ?? 'pending';

            return (
              <Link
                key={order.id}
                href={route('shop.orders.show', order.id)}
                className="block bg-white border rounded-lg p-4 hover:shadow-sm transition-shadow"
              >
                <div className="flex items-center justify-between mb-2">
                  <div className="text-sm text-gray-500">
                    <span className="font-medium text-gray-700">
                      Pesanan #{order.id}
                    </span>
                    <span className="ml-2">•</span>
                    <span className="ml-2">
                      {new Date(order.created_at).toLocaleString('id-ID')}
                    </span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span
                      className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusBadgeClass(
                        paymentStatus
                      )}`}
                    >
                      {paymentStatusLabel(paymentStatus)}
                    </span>
                    <span
                      className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${fulfillmentBadgeClass(
                        order.fulfillment_status
                      )}`}
                    >
                      {fulfillmentStatusLabel(order.fulfillment_status)}
                    </span>
                  </div>
                </div>
                <div className="flex items-center justify-between text-sm mt-1">
                  <div className="text-gray-500">
                    {order.items_count} item
                  </div>
                  <div className="font-semibold text-primary-600">
                    Total Rp {formatRupiah(order.grand_total)}
                  </div>
                </div>
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}


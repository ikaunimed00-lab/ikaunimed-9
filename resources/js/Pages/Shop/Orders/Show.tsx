import { Link, usePage } from '@inertiajs/react';
import { route } from 'ziggy-js';

type Product = {
  id: number;
  name: string;
  slug: string;
};

type OrderItem = {
  id: number;
  product_id: number | null;
  product_name: string;
  product_type: string;
  quantity: number;
  price: string;
  total: string;
  product?: Product | null;
};

type ShippingAddress = {
  name: string;
  phone: string;
  address: string;
};

type Order = {
  id: number;
  status: string;
  fulfillment_status: string;
  grand_total: string;
  total_amount: string;
  shipping_cost: string;
  created_at: string;
  shipping_address: ShippingAddress;
  items: OrderItem[];
};

type Payment = {
  id: number;
  amount: string;
  status: string;
  provider: string;
  method: string;
  paid_at: string | null;
};

type PageProps = {
  order: Order;
  payment: Payment | null;
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

export default function OrderShow() {
  const { order, payment } = usePage<PageProps>().props;

  const items = order.items ?? [];

  return (
    <div className="max-w-5xl mx-auto px-4 py-8">
      <div className="mb-4 flex items-center justify-between">
        <Link
          href={route('shop.orders.index')}
          className="text-sm text-blue-600"
        >
          &larr; Kembali ke riwayat pesanan
        </Link>
        <Link href={route('shop.index')} className="text-sm text-gray-600">
          Kembali ke katalog
        </Link>
      </div>

      <h1 className="text-2xl font-semibold mb-4">
        Pesanan #{order.id}
      </h1>

      <div className="grid md:grid-cols-3 gap-6">
        <div className="md:col-span-2 space-y-4">
          <div className="bg-white border rounded-lg p-4">
            <div className="flex items-center justify-between mb-2">
              <div className="text-sm text-gray-500">
                Dibuat pada{' '}
                {new Date(order.created_at).toLocaleString('id-ID')}
              </div>
              <div className="flex items-center gap-2">
                <span
                  className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusBadgeClass(
                    payment?.status ?? order.status
                  )}`}
                >
                  {paymentStatusLabel(payment?.status ?? order.status)}
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

            <div className="mt-3">
              <h2 className="text-sm font-semibold text-gray-700 mb-2">
                Alamat Pengiriman
              </h2>
              <div className="text-sm text-gray-600 space-y-1">
                <div>{order.shipping_address?.name}</div>
                <div>{order.shipping_address?.phone}</div>
                <div className="whitespace-pre-line">
                  {order.shipping_address?.address}
                </div>
              </div>
            </div>
          </div>

          <div className="bg-white border rounded-lg p-4">
            <h2 className="text-sm font-semibold text-gray-700 mb-3">
              Item Pesanan
            </h2>
            <div className="space-y-3">
              {items.map((item) => (
                <div
                  key={item.id}
                  className="flex items-start justify-between text-sm"
                >
                  <div>
                    <div className="font-medium text-gray-800">
                      {item.product_name}
                    </div>
                    <div className="text-gray-500">
                      Qty: {item.quantity}
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-gray-500">
                      Rp {formatRupiah(item.price)}
                    </div>
                    <div className="font-semibold text-primary-600">
                      Rp {formatRupiah(item.total)}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="space-y-4">
          <div className="bg-white border rounded-lg p-4">
            <h2 className="text-sm font-semibold text-gray-700 mb-3">
              Ringkasan Pembayaran
            </h2>
            <div className="space-y-2 text-sm">
              <div className="flex items-center justify-between">
                <span>Subtotal</span>
                <span>
                  Rp {formatRupiah(order.total_amount)}
                </span>
              </div>
              <div className="flex items-center justify-between text-gray-500">
                <span>Ongkir</span>
                <span>
                  Rp {formatRupiah(order.shipping_cost)}
                </span>
              </div>
              <div className="flex items-center justify-between font-semibold">
                <span>Total</span>
                <span>
                  Rp {formatRupiah(order.grand_total)}
                </span>
              </div>
            </div>
          </div>

          <div className="bg-white border rounded-lg p-4">
            <h2 className="text-sm font-semibold text-gray-700 mb-3">
              Status Pembayaran
            </h2>
            {payment ? (
              <div className="space-y-2 text-sm">
                <div className="flex items-center justify-between">
                  <span>Metode</span>
                  <span className="text-gray-700 uppercase">
                    {payment.provider} / {payment.method}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span>Status</span>
                  <span
                    className={`inline-flex px-2 py-0.5 rounded-full text-xs font-medium ${statusBadgeClass(
                      payment.status
                    )}`}
                  >
                    {paymentStatusLabel(payment.status)}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span>Jumlah</span>
                  <span className="font-semibold text-primary-600">
                    Rp {formatRupiah(payment.amount)}
                  </span>
                </div>
                {payment.paid_at && (
                  <div className="flex items-center justify-between text-gray-500">
                    <span>Dibayar pada</span>
                    <span>
                      {new Date(payment.paid_at).toLocaleString('id-ID')}
                    </span>
                  </div>
                )}
              </div>
            ) : (
              <p className="text-sm text-gray-600">
                Informasi pembayaran belum tersedia.
              </p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}


import { Link, useForm, usePage } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { route } from 'ziggy-js';
import { SHOP_COPY } from '../copy';

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
  shipment?: Shipment | null;
};

type Shipment = {
  courier_name: string | null;
  tracking_number: string | null;
  shipped_at: string | null;
  delivered_at: string | null;
  status: string | null;
  notes: string | null;
};

type Payment = {
  id: number;
  amount: string;
  status: string;
  provider: string;
  method: string;
  paid_at: string | null;
  manual_proof_path?: string | null;
};

type CourseSummary = {
  id: number;
  title: string;
  slug: string;
};

type PageProps = {
  order: Order;
  payment: Payment | null;
  digitalCourses: CourseSummary[];
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

const shipmentStatusLabel = (status: string | null | undefined) => {
  if (status === 'pending') return 'Menunggu Pengiriman';
  if (status === 'shipped') return 'Sedang Dikirim';
  if (status === 'delivered') return 'Terkirim';
  if (status === 'returned') return 'Dikembalikan';
  return status ?? '-';
};

export default function OrderShow() {
  const { order, payment, digitalCourses } = usePage<PageProps>().props;

  const { data, setData, post, processing, errors, reset } = useForm<{
    proof: File | null;
  }>({
    proof: null,
  });

  const handleProofChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files || e.target.files.length === 0) return;
    setData('proof', e.target.files[0]);
  };

  const handleUploadProof = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('shop.orders.upload-proof', order.id), {
      forceFormData: true,
      onSuccess: () => {
        reset();
      },
    });
  };

  const items = order.items ?? [];
  const isPaid = (payment?.status ?? order.status) === 'paid';
  const hasDigitalItems = items.some(
    (item) => item.product_type === 'digital'
  );
  const hasDigitalCourses = isPaid && (digitalCourses?.length ?? 0) > 0;
  const shipment = order.shipment;

  return (
    <MainLayout>
      <div className="max-w-5xl mx-auto px-4 py-8">
        <div className="mb-4 flex items-center justify-between">
        <Link
          href={route('shop.orders.index')}
          className="text-sm text-blue-600"
        >
          &larr; {SHOP_COPY.navigation.backToOrderHistory}
        </Link>
        <Link href={route('shop.index')} className="text-sm text-gray-600">
          {SHOP_COPY.navigation.backToShop}
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

          {hasDigitalItems && (
            <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
              <h2 className="text-sm font-semibold text-emerald-900 mb-2">
                Akses Produk Digital / Kursus
              </h2>
              {isPaid ? (
                <>
                  <p className="text-sm text-emerald-800">
                    Pembayaran Anda sudah dikonfirmasi. Akses kursus LMS untuk produk
                    digital di pesanan ini sudah aktif.
                  </p>
                  {hasDigitalCourses ? (
                    <div className="mt-3 space-y-2">
                      {digitalCourses.map((course) => (
                        <div
                          key={course.id}
                          className="flex items-center justify-between text-xs text-emerald-900"
                        >
                          <span className="font-semibold truncate">
                            {course.title}
                          </span>
                          <Link
                            href={route('courses.show', course.slug)}
                            className="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700"
                          >
                            Buka Kursus
                          </Link>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="mt-3">
                      <Link
                        href={route('dashboard.elearning.learner.courses.index')}
                        className="inline-flex items-center px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700"
                      >
                        Buka Kursus di LMS
                      </Link>
                    </div>
                  )}
                </>
              ) : (
                <p className="text-sm text-emerald-800">
                  Setelah pembayaran dikonfirmasi, akses kursus LMS akan aktif dan
                  dapat dibuka dari halaman ini.
                </p>
              )}
            </div>
          )}

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

          <div className="bg-white border rounded-lg p-4">
            <h2 className="text-sm font-semibold text-gray-700 mb-3">
              Status Pengiriman
            </h2>
            {shipment ? (
              <div className="space-y-2 text-sm">
                <div className="flex items-center justify-between">
                  <span>Status</span>
                  <span className="font-medium text-gray-700">
                    {shipmentStatusLabel(shipment.status)}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span>Kurir</span>
                  <span className="text-gray-700">
                    {shipment.courier_name || '-'}
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <span>No. Resi</span>
                  <span className="font-mono text-xs text-gray-700">
                    {shipment.tracking_number || '-'}
                  </span>
                </div>
                {shipment.shipped_at && (
                  <div className="flex items-center justify-between text-gray-500">
                    <span>Dikirim pada</span>
                    <span>
                      {new Date(shipment.shipped_at).toLocaleString('id-ID')}
                    </span>
                  </div>
                )}
                {shipment.delivered_at && (
                  <div className="flex items-center justify-between text-gray-500">
                    <span>Diterima pada</span>
                    <span>
                      {new Date(shipment.delivered_at).toLocaleString('id-ID')}
                    </span>
                  </div>
                )}
                {shipment.notes && (
                  <p className="rounded-md bg-gray-50 p-2 text-xs text-gray-600">
                    Catatan: {shipment.notes}
                  </p>
                )}
              </div>
            ) : (
              <p className="text-sm text-gray-600">
                Data pengiriman belum tersedia. Pesanan akan diproses setelah
                pembayaran dikonfirmasi.
              </p>
            )}
          </div>

          <div className="bg-white border rounded-lg p-4">
            <h2 className="text-sm font-semibold text-gray-700 mb-3">
              Pembayaran Manual / Transfer Bank
            </h2>
            <div className="space-y-3 text-sm">
              <div className="bg-gray-50 border border-dashed border-gray-200 rounded-md p-3">
                <p className="font-semibold text-gray-800 mb-1">
                  Transfer ke Rekening BCA
                </p>
                <p className="text-gray-700">A.N. IKA UNIMED</p>
                <p className="text-lg font-bold text-gray-900 tracking-wider">
                  8115141186
                </p>
                <p className="text-xs text-gray-500 mt-2">
                  {isPaid
                    ? 'Pembayaran Anda sudah dikonfirmasi admin. Pesanan akan segera diproses.'
                    : 'Setelah melakukan transfer, upload bukti pembayaran di bawah ini agar tim admin dapat memverifikasi dan mengkonfirmasi pesanan Anda.'}
                </p>
              </div>

              {!isPaid && (
                <>
                  <form onSubmit={handleUploadProof} className="space-y-3">
                    <div>
                      <label className="block text-xs font-medium text-gray-700 mb-1">
                        Upload Bukti Pembayaran (JPG, PNG, PDF, maks 5MB)
                      </label>
                      <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        onChange={handleProofChange}
                        className="block w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                      />
                      {errors.proof && (
                        <p className="mt-1 text-xs text-red-600">
                          {errors.proof}
                        </p>
                      )}
                    </div>

                    <button
                      type="submit"
                      disabled={processing || !data.proof}
                      className="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 disabled:opacity-50"
                    >
                      Kirim Bukti Pembayaran
                    </button>
                  </form>

                  {payment?.manual_proof_path && (
                    <p className="text-xs text-emerald-700">
                      Bukti pembayaran sudah diupload. Admin akan memverifikasi
                      pembayaran Anda.
                    </p>
                  )}
                </>
              )}
            </div>
          </div>
        </div>
      </div>
      </div>
    </MainLayout>
  );
}

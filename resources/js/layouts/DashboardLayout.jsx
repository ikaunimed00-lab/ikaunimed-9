import { Link, usePage } from '@inertiajs/react';

export default function DashboardLayout({ children }) {
  const { auth } = usePage().props;
  const role = auth.user.role;

  return (
    <div className="flex min-h-screen">
      {/* SIDEBAR */}
      <aside className="w-64 bg-gray-800 text-white p-4">
        <h2 className="text-xl font-bold mb-6">Dashboard</h2>

        <nav className="space-y-2">
          <Link href={route('home')} className="block hover:underline">
            🏠 Home
          </Link>

          <Link href={route('dashboard')} className="block hover:underline">
            📊 Dashboard
          </Link>

          {(role === 'admin' || role === 'editor') && (
            <a href="/admin/news" className="block hover:underline">
              📰 Kelola Berita
            </a>
          )}

          {role === 'admin' && (
            <>
              <a href="/admin/users" className="block hover:underline">
                👥 Kelola User (Filament)
              </a>

              <a href="/admin/legalizations" className="block hover:underline">
                📄 Kelola Legalisasi (Filament)
              </a>
            </>
          )}
        </nav>
      </aside>

      {/* CONTENT + FOOTER */}
      <div className="flex flex-col flex-1 bg-gray-100">
        <main className="flex-1 p-6">
          {children}
        </main>

        <footer className="border-t bg-white text-center text-sm text-gray-500 py-4">
          © {new Date().getFullYear()} IKA UNIMED
        </footer>
      </div>
    </div>
  );
}

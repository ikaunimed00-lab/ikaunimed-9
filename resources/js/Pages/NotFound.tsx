import { useLocation } from "react-router-dom";
import { useEffect } from "react";
import MainLayout from "@/components/MainLayout";

const NotFound = () => {
  const location = useLocation();

  useEffect(() => {
    console.error("404 Error: User attempted to access non-existent route:", location.pathname);
  }, [location.pathname]);

  return (
    <MainLayout>
      <div className="flex min-h-[60vh] items-center justify-center">
        <div className="text-center">
          <h1 className="mb-4 text-4xl font-bold">404</h1>
          <p className="mb-4 text-xl text-gray-600">Oops! Halaman tidak ditemukan</p>
          <a href="/" className="text-teal-600 underline hover:text-teal-700">
            Kembali ke Beranda
          </a>
        </div>
      </div>
    </MainLayout>
  );
};

export default NotFound;

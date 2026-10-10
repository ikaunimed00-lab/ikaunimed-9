import { Link } from "@inertiajs/react";
import { Lock } from "lucide-react";

export const REGISTER_URL = "/teacher-of-champions/daftar";

type RegisterButtonProps = {
  isOpen: boolean;
  label?: string;
  className?: string;
};

const baseClass =
  "inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-base font-bold transition focus:outline-none focus-visible:ring-4 focus-visible:ring-[#FFD700]/60";

const RegisterButton = ({ isOpen, label = "Daftar sekarang", className = "" }: RegisterButtonProps) => {
  if (!isOpen) {
    return (
      <button
        type="button"
        disabled
        aria-disabled="true"
        className={`${baseClass} cursor-not-allowed bg-gray-200 text-gray-600 ${className}`}
      >
        <Lock className="h-4 w-4" aria-hidden="true" />
        Pendaftaran ditutup
      </button>
    );
  }

  return (
    <Link
      href={REGISTER_URL}
      className={`${baseClass} bg-[#FF7E00] text-[#0B3D2E] shadow-lg shadow-black/10 hover:bg-[#FF9533] ${className}`}
    >
      {label}
    </Link>
  );
};

export default RegisterButton;

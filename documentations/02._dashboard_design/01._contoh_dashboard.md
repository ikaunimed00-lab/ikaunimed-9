// File: resources/js/layouts/SubscriberLayout.tsx
import { PropsWithChildren, useState } from "react";
import { Link, usePage, router } from "@inertiajs/react";
import { route } from "ziggy-js";
import { LogOut, Menu } from "lucide-react";
import { getMenuByRole } from "@/config/sidebar-menu-config";

export default function SubscriberLayout({ children }: PropsWithChildren) {
    const { auth }: any = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(true);

    const handleLogout = () => {
        router.post(route("logout"));
    };

    // ✅ Ambil menu dari config berdasarkan role
    const menuItems = getMenuByRole("subscriber");

    return (
        <div className="min-h-screen flex bg-gradient-to-br from-white via-emerald-50/20 to-white">
            {/* SIDEBAR */}
            <aside
                className={`${
                    sidebarOpen ? "w-64" : "w-20"
                } bg-gradient-to-b from-[#1F2937] via-[#111827] to-[#0F172A] text-white flex-shrink-0 transition-all duration-300 border-r border-emerald-500/20 shadow-xl overflow-y-auto`}
            >
                {/* LOGO */}
                <div className="p-6 border-b border-emerald-500/20">
                    <div className="flex items-center gap-3">
                        <img
                            src="/images/favicon_ikaunimed.png"
                            alt="IKA UNIMED"
                            className="h-10 w-10"
                        />
                        {sidebarOpen && (
                            <div>
                                <div className="font-bold text-lg">
                                    <span className="text-[#FF7E00]">IKA</span>
                                    <span className="text-[#00A69D]">UNI</span>
                                    <span className="text-[#e9cf35]">MED</span>
                                </div>
                                <div className="text-xs text-slate-400">
                                    Portal Alumni
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* NAV - DYNAMIC dari Config */}
                <nav className="p-3 space-y-2 text-sm">
                    {menuItems.map((item) => {
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                className="group flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gradient-to-r hover:from-emerald-500/20 hover:to-teal-500/20 text-slate-300 hover:text-white transition-all duration-200 border border-transparent hover:border-emerald-500/30"
                            >
                                <Icon className="w-5 h-5 text-emerald-400 group-hover:scale-110 transition-transform" />
                                {sidebarOpen && (
                                    <span className="font-semibold">{item.title}</span>
                                )}
                            </Link>
                        );
                    })}
                </nav>

                {/* LOGOUT */}
                <div className="p-3 border-t border-white/10 mt-auto">
                    <button
                        onClick={handleLogout}
                        className="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-red-600/20 hover:bg-red-600/30 transition-colors"
                    >
                        <LogOut className="w-5 h-5" />
                        {sidebarOpen && "Logout"}
                    </button>
                    {sidebarOpen && (
                        <p className="text-xs text-slate-400 mt-3 px-2">
                            👤 {auth.user.name}
                        </p>
                    )}
                </div>

                {/* TOGGLE */}
                <div className="p-3 border-t border-white/10">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="w-full flex justify-center p-2 hover:bg-white/10 rounded"
                    >
                        <Menu className="w-5 h-5" />
                    </button>
                </div>
            </aside>

            {/* CONTENT */}
            <main className="flex-1 p-6">{children}</main>
        </div>
    );
}
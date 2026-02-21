// File: resources/js/layouts/EditorLayout.tsx
import { PropsWithChildren, useState } from "react";
import { Link, usePage, router } from "@inertiajs/react";
import { route } from "ziggy-js";
import { LogOut, Menu } from "lucide-react";
import { SIDEBAR_MENU, SidebarGroup } from "@/config/sidebar-menu-config";

export default function EditorLayout({ children }: PropsWithChildren) {
    const { auth }: any = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(true);

    function handleLogout() {
        router.post(route("logout"));
    }

    const permissions = (auth?.user?.permissions as string[] | undefined) ?? [];
    const userRoles = auth?.user?.roles as string[] | undefined;
    const isSuperAdmin = userRoles?.includes("super_admin");

    const hasPermission = (required?: string[]) => {
        if (!required || required.length === 0) return true;
        if (isSuperAdmin) return true;
        return required.some((p) => permissions.includes(p));
    };

    const menuGroups: SidebarGroup[] = SIDEBAR_MENU
        .filter((group) => hasPermission(group.requiredPermissions))
        .map((group) => ({
            ...group,
            items: group.items.filter((item) =>
                hasPermission(item.requiredPermissions)
            ),
        }))
        .filter((group) => group.items.length > 0);

    return (
        <div className="min-h-screen flex bg-gradient-to-br from-white via-green-50/20 to-white">
            {/* SIDEBAR */}
            <aside
                className={`${
                    sidebarOpen ? "w-64" : "w-20"
                } bg-gradient-to-b from-[#1F2937] via-[#111827] to-[#0F172A] text-white flex-shrink-0 z-20 transition-all duration-300 border-r border-green-500/20 shadow-xl overflow-y-auto`}
            >
                {/* LOGO SECTION */}
                <div className="p-6 border-b border-green-500/20 sticky top-0 bg-gradient-to-r from-[#111827] to-[#1F2937] backdrop-blur-sm">
                    <div className="flex items-center gap-3">
                        <img
                            src="/images/favicon_ikaunimed.png"
                            alt="Logo IKA UNIMED"
                            className="h-10 w-10 transition-transform hover:scale-110"
                        />
                        {sidebarOpen && (
                            <div className="flex flex-col leading-none">
                                <div className="text-lg font-bold font-sans tracking-tighter flex items-center gap-0.5">
                                    <span className="text-[#FF7E00]">IKA</span>
                                    <span className="text-[#00A69D]">UNI</span>
                                    <span className="text-[#e9cf35]">MED</span>
                                </div>
                                <div className="text-[7px] text-slate-400 font-bold uppercase tracking-widest">
                                    Editor Panel
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* NAVIGATION - DYNAMIC dari Config */}
                <nav className="p-3 space-y-4 text-sm">
                    {menuGroups.map((group) => (
                        <div key={group.group} className="space-y-2">
                            {sidebarOpen && (
                                <div className="px-4 text-[10px] font-semibold tracking-widest text-slate-500 uppercase">
                                    {group.group}
                                </div>
                            )}
                            {group.items.map((item) => {
                                const Icon = item.icon;
                                let href = "#";
                                try {
                                    href = route(item.route);
                                } catch (e) {
                                    href = "#";
                                }

                                return (
                                    <Link
                                        key={item.route}
                                        href={href}
                                        className="group flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gradient-to-r hover:from-green-500/20 hover:to-emerald-500/20 text-slate-300 hover:text-white transition-all duration-200 border border-transparent hover:border-green-500/30"
                                    >
                                        <Icon className="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" />
                                        {sidebarOpen && (
                                            <span className="font-semibold">
                                                {item.label}
                                            </span>
                                        )}
                                    </Link>
                                );
                            })}
                        </div>
                    ))}
                </nav>

                {/* LOGOUT SECTION */}
                <div className="p-3 border-t border-green-500/20 mt-auto sticky bottom-0 bg-gradient-to-r from-[#111827] to-[#1F2937] backdrop-blur-sm">
                    <button
                        type="button"
                        onClick={handleLogout}
                        className="w-full group flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-red-600/20 to-red-600/10 hover:from-red-600/30 hover:to-red-600/20 text-red-300 hover:text-red-200 transition-all duration-200 border border-red-500/30 hover:border-red-500/50 font-semibold"
                    >
                        <LogOut className="w-5 h-5" />
                        {sidebarOpen && <span>Logout</span>}
                    </button>
                    {sidebarOpen && (
                        <p className="text-xs text-slate-400 mt-3 px-2">
                            ✏️ {auth.user.name}
                        </p>
                    )}
                </div>

                {/* Toggle Button */}
                <div className="p-3 border-t border-green-500/20">
                    <button
                        onClick={() => setSidebarOpen(!sidebarOpen)}
                        className="w-full flex items-center justify-center px-4 py-2 rounded-lg bg-green-500/10 hover:bg-green-500/20 text-slate-300 hover:text-green-400 transition-all"
                    >
                        <Menu className="w-5 h-5" />
                    </button>
                </div>
            </aside>

            {/* KONTEN */}
            <main className="flex-1 flex flex-col min-h-screen overflow-auto">
                <div className="flex-1 p-6 md:p-8">
                    {children}
                </div>
            </main>
        </div>
    );
}

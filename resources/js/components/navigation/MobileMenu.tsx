import React, { useState } from 'react';
import { MainMenuItem } from './menuConfig';
import { LayoutDashboard, LogOut } from 'lucide-react';
import { Link, router, usePage } from '@inertiajs/react';
import { ChevronDown, X } from 'lucide-react';

interface MobileMenuProps {
  isOpen: boolean;
  onClose: () => void;
  menuItems: MainMenuItem[];
  currentPath?: string;
}

export const MobileMenu: React.FC<MobileMenuProps> = ({
  isOpen,
  onClose,
  menuItems,
  currentPath = '/',
}) => {
  const [openSection, setOpenSection] = useState<string | null>(null);
  const { auth } = usePage().props as any;
  const user = auth?.user;

  const toggleSection = (sectionId: string) => {
    setOpenSection(openSection === sectionId ? null : sectionId);
  };

  const handleLogout = () => {
    router.post('/logout');
  };

  return (
    <>
      {/* Overlay */}
      <div
        className={`
          fixed inset-0 bg-black/50 z-40 lg:hidden
          transition-opacity duration-300
          ${isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}
        `}
        onClick={onClose}
      />

      {/* Mobile Menu Panel */}
      <div
        className={`
          fixed top-0 right-0 bottom-0 w-full max-w-sm
          bg-white shadow-2xl z-50 lg:hidden
          transform transition-transform duration-300 ease-out
          ${isOpen ? 'translate-x-0' : 'translate-x-full'}
        `}
      >
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div className="flex items-center gap-3">
            <img src="/images/favicon_ikaunimed.png" className="h-9 w-auto" />
            <div className="flex flex-col leading-none">
              <div className="text-xl font-bold font-sans tracking-tighter flex items-center">
                <span className="text-primary">IKA</span>
                <span className="text-oxygen-teal">UNI</span>
                <span style={{ color: '#FFD700' }}>MED</span>
              </div>
              <span className="text-[8px] text-gray-500 mt-1 font-bold uppercase tracking-widest">
                Connect, Collaborate, Contribute
              </span>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            aria-label="Tutup menu"
          >
            <X className="w-5 h-5 text-gray-600" />
          </button>
        </div>

        {/* Menu Items + CTA (scrollable) */}
        <div className="overflow-y-auto h-[calc(100vh-73px)]">
          <nav className="py-2">
            {menuItems.map((item, index) => {
              const Icon = item.icon;
              const isActive = currentPath === item.href;
              const hasSubMenu = item.sections && item.sections.length > 0;
              const isExpanded = openSection === item.id;

              // Menu tanpa submenu (Home)
              if (!hasSubMenu && item.href) {
                return (
                  <Link
                    key={item.id || `mobile-menu-${index}`}
                    href={item.href}
                    className={`
                      flex items-center gap-3 px-6 py-3.5
                      transition-colors duration-150
                      ${isActive ? 'bg-gray-50' : 'hover:bg-gray-50'}
                    `}
                    onClick={onClose}
                  >
                    {Icon && (
                      <Icon
                        className="w-5 h-5"
                        style={{ color: item.color.main }}
                        strokeWidth={2}
                      />
                    )}
                    <span
                      className={`font-semibold ${
                        isActive ? 'text-gray-900' : 'text-gray-700'
                      }`}
                    >
                      {item.label}
                    </span>
                  </Link>
                );
              }

              // Menu dengan submenu (Accordion)
              return (
                <div key={item.id || `mobile-menu-parent-${index}`} className="border-b border-gray-100">
                  {/* Parent Item - Clickable untuk expand */}
                  <button
                    onClick={() => toggleSection(item.id)}
                    className="
                      w-full flex items-center justify-between gap-3 
                      px-6 py-3.5
                      hover:bg-gray-50 transition-colors duration-150
                    "
                  >
                    <div className="flex items-center gap-3">
                      {Icon && (
                        <Icon
                          className="w-5 h-5"
                          style={{ color: item.color.main }}
                          strokeWidth={2}
                        />
                      )}
                      <span className="font-semibold text-gray-700 text-left">
                        {item.label}
                      </span>
                    </div>
                    <ChevronDown
                      className={`
                        w-4 h-4 text-gray-500
                        transition-transform duration-200
                        ${isExpanded ? 'rotate-180' : ''}
                      `}
                    />
                  </button>

                  {/* Submenu - Accordion Content */}
                  <div
                    className={`
                      overflow-hidden transition-all duration-300 ease-in-out
                      ${isExpanded ? 'max-h-[800px] opacity-100' : 'max-h-0 opacity-0'}
                    `}
                  >
                    <div className="bg-gray-50/50 py-2">
                      {item.sections?.map((section, sectionIndex) => {
                        const SectionIcon = section.icon;
                        return (
                          <div key={section.id || `section-${sectionIndex}`} className="mb-4 last:mb-0">
                            {/* Section Header */}
                            <div className="flex items-center gap-2 px-6 py-2">
                              {SectionIcon && (
                                <SectionIcon
                                  className="w-3.5 h-3.5"
                                  style={{ color: item.color.main }}
                                  strokeWidth={2.5}
                                />
                              )}
                              <h4
                                className="text-xs font-bold uppercase tracking-wide"
                                style={{ color: item.color.main }}
                              >
                                {section.title}
                              </h4>
                            </div>

                            {/* Section Items */}
                            <div className="space-y-0.5 mt-1">
                              {section.items.map((menuItem, itemIndex) => {
                                const ItemIcon = menuItem.icon;
                                const isItemActive = currentPath === menuItem.href;
                                return (
                                  <a
                                    key={menuItem.title || `menu-item-${itemIndex}`}
                                    href={menuItem.href}
                                    onClick={onClose}
                                    className={`
                                      flex items-start gap-3 px-6 py-2.5 pl-12
                                      transition-colors duration-150
                                      ${
                                        isItemActive
                                          ? 'bg-white'
                                          : 'hover:bg-white/70'
                                      }
                                    `}
                                  >
                                    {ItemIcon && (
                                      <div className="mt-0.5">
                                        <ItemIcon
                                          className="w-4 h-4"
                                          style={{ color: item.color.main }}
                                          strokeWidth={2}
                                        />
                                      </div>
                                    )}
                                    <div className="flex-1 min-w-0">
                                      <h5
                                        className={`
                                          text-sm font-medium
                                          ${
                                            isItemActive
                                              ? 'text-gray-900'
                                              : 'text-gray-700'
                                          }
                                        `}
                                      >
                                        {menuItem.title}
                                      </h5>
                                      {menuItem.description && (
                                        <p className="text-xs text-gray-600 mt-0.5 leading-relaxed">
                                          {menuItem.description}
                                        </p>
                                      )}
                                    </div>
                                  </a>
                                );
                              })}
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                </div>
              );
            })}
          </nav>
          {/* CTA Mobile */}
          <div className="border-t border-gray-100 px-6 py-3 mt-2">
            {user ? (
              <>
                {['admin', 'editor', 'writer'].includes(user.role) ? (
                  <a
                    href="/admin"
                    onClick={onClose}
                    className="block w-full mb-2.5"
                  >
                    <button className="w-full flex items-center justify-center gap-1.5 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-2.5 text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 hover:brightness-105">
                      <LayoutDashboard className="w-4 h-4" />
                      DASHBOARD ADMIN
                    </button>
                  </a>
                ) : (
                  <Link
                    href={user.role === 'subscriber' ? '/dashboard/subscriber' : '/dashboard'}
                    onClick={onClose}
                    className="block w-full mb-2.5"
                  >
                    <button className="w-full flex items-center justify-center gap-1.5 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-2.5 text-xs font-semibold rounded-lg shadow-sm transition-all duration-200 hover:brightness-105">
                      <LayoutDashboard className="w-4 h-4" />
                      DASHBOARD
                    </button>
                  </Link>
                )}
                <button
                  onClick={() => {
                    onClose();
                    handleLogout();
                  }}
                  className="w-full flex items-center justify-center gap-1.5 bg-red-500/90 text-white py-2.5 text-xs font-semibold rounded-lg hover:bg-red-500 transition-all duration-200"
                >
                  <LogOut className="w-4 h-4" />
                  LOGOUT
                </button>
              </>
            ) : (
              <div className="grid grid-cols-2 gap-2.5">
                <Link href="/login" onClick={onClose}>
                  <button className="w-full border border-emerald-600/80 text-emerald-800 bg-white py-2.5 text-xs font-semibold rounded-lg">
                    LOGIN
                  </button>
                </Link>
                <Link href="/register" onClick={onClose}>
                  <button className="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-2.5 text-xs font-semibold rounded-lg shadow-sm">
                    DAFTAR
                  </button>
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
};

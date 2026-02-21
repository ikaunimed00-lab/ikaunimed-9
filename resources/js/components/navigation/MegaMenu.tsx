import React from 'react';
import { MainMenuItem } from './menuConfig';
import { ChevronRight } from 'lucide-react';

interface MegaMenuProps {
  item: MainMenuItem;
  isOpen: boolean;
  onClose: () => void;
}

export const MegaMenu: React.FC<MegaMenuProps> = ({ item, isOpen, onClose }) => {
  if (!item.sections || item.sections.length === 0) return null;

  const Icon = item.icon;

  const isRightAligned = item.id === 'layanan-alumni' || item.id === 'berita';

  return (
    <div
      className={`
        absolute top-full pt-4 z-50
        ${isRightAligned ? 'right-0' : 'left-1/2 -translate-x-1/2'}
        transition-all duration-300 ease-out
        ${isOpen ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 -translate-y-3 pointer-events-none'}
      `}
    >
      <div className="w-[700px] max-w-[80vw] mx-auto">
        <div
          className="
            bg-white rounded-2xl shadow-2xl
            border border-gray-100
            overflow-hidden
            w-full
          "
        >
          {/* Header with gradient */}
          <div
            className={`
              px-6 py-5 
              bg-gradient-to-r ${item.color.gradient}
              border-b border-gray-100
            `}
          >
            <div className="flex items-start gap-3">
              {Icon && (
                <div
                  className={`
                    p-2.5 rounded-xl
                    ${item.color.iconClass || 'text-white'}
                    transition-transform duration-200
                    group-hover:scale-110
                  `}
                  style={{ backgroundColor: item.color.main }}
                >
                  <Icon className="w-6 h-6" strokeWidth={2} />
                </div>
              )}
              <div>
                <h3
                  className="text-lg font-semibold mb-1"
                  style={{ color: item.color.main }}
                >
                  {item.label}
                </h3>
                {item.description && (
                  <p className="text-sm text-gray-600 leading-relaxed">
                    {item.description}
                  </p>
                )}
              </div>
            </div>
          </div>

          {/* Content Grid */}
          <div className="grid grid-cols-2 gap-x-8 gap-y-6 p-6">
            {item.sections.map((section, sectionIndex) => {
              const SectionIcon = section.icon;
              return (
                <div key={section.id || `section-${sectionIndex}`} className="space-y-3">
                  {/* Section Title */}
                  <div className="flex items-center gap-2 mb-4">
                    {SectionIcon && (
                      <SectionIcon
                        className="w-4 h-4"
                        style={{ color: item.color.main }}
                        strokeWidth={2.5}
                      />
                    )}
                    <h4
                      className="text-sm font-semibold uppercase tracking-wide"
                      style={{ color: item.color.main }}
                    >
                      {section.title}
                    </h4>
                  </div>

                  {/* Section Items */}
                  <div className="space-y-1">
                    {section.items.map((menuItem, itemIndex) => {
                      const ItemIcon = menuItem.icon;
                      return (
                        <a
                          key={menuItem.title || `item-${itemIndex}`}
                          href={menuItem.href}
                          onClick={onClose}
                          className={`
                            group
                            flex items-start gap-3 p-3 rounded-lg
                            transition-all duration-150
                            ${item.color.hover}
                          `}
                        >
                          {ItemIcon && (
                            <div className="mt-0.5">
                              <ItemIcon
                                className={`
                                  w-4 h-4 transition-transform duration-200
                                  group-hover:scale-110
                                  ${item.color.iconClass || ''}
                                `}
                                style={{ color: item.color.main }}
                                strokeWidth={2}
                              />
                            </div>
                          )}
                          <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2">
                              <h5 className="text-sm font-semibold text-gray-900 group-hover:underline">
                                {menuItem.title}
                              </h5>
                              <ChevronRight className="w-3.5 h-3.5 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity duration-150" />
                            </div>
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
    </div>
  );
};

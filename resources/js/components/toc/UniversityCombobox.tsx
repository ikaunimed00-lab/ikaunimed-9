import { useEffect, useId, useMemo, useRef, useState } from "react";

export type UniversityOption = {
  name: string;
  aliases?: string[];
};

type UniversityComboboxProps = {
  id: string;
  value: string;
  options: UniversityOption[];
  className?: string;
  required?: boolean;
  onChange: (value: string) => void;
};

const normalize = (value: string): string =>
  value
    .toLowerCase()
    .normalize("NFKD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/[^\p{L}\p{N}\s]+/gu, " ")
    .replace(/\s+/g, " ")
    .trim();

const matchesQuery = (option: UniversityOption, query: string): boolean => {
  if (!query) {
    return true;
  }

  const haystacks = [option.name, ...(option.aliases ?? [])].map(normalize);

  return haystacks.some((text) => text.includes(query));
};

const UniversityCombobox = ({
  id,
  value,
  options,
  className = "",
  required = false,
  onChange,
}: UniversityComboboxProps) => {
  const listId = useId();
  const rootRef = useRef<HTMLDivElement>(null);
  const [open, setOpen] = useState(false);
  const [highlight, setHighlight] = useState(0);

  const query = normalize(value);
  const filtered = useMemo(
    () => options.filter((option) => matchesQuery(option, query)).slice(0, 12),
    [options, query],
  );

  const exactMatch = options.some((option) => normalize(option.name) === query);
  const showCustom = query.length >= 3 && !exactMatch;
  const items = showCustom
    ? [{ type: "custom" as const, label: `Gunakan: ${value.trim()}` }, ...filtered.map((option) => ({ type: "option" as const, option }))]
    : filtered.map((option) => ({ type: "option" as const, option }));

  useEffect(() => {
    setHighlight(0);
  }, [query, open]);

  useEffect(() => {
    const onPointerDown = (event: MouseEvent) => {
      if (!rootRef.current?.contains(event.target as Node)) {
        setOpen(false);
      }
    };

    document.addEventListener("mousedown", onPointerDown);

    return () => document.removeEventListener("mousedown", onPointerDown);
  }, []);

  const selectValue = (next: string) => {
    onChange(next);
    setOpen(false);
  };

  const onKeyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (!open && (event.key === "ArrowDown" || event.key === "Enter")) {
      setOpen(true);
      return;
    }

    if (!open) {
      return;
    }

    if (event.key === "ArrowDown") {
      event.preventDefault();
      setHighlight((current) => Math.min(current + 1, Math.max(items.length - 1, 0)));
    } else if (event.key === "ArrowUp") {
      event.preventDefault();
      setHighlight((current) => Math.max(current - 1, 0));
    } else if (event.key === "Enter" && items[highlight]) {
      event.preventDefault();
      const item = items[highlight];
      selectValue(item.type === "custom" ? value.trim() : item.option.name);
    } else if (event.key === "Escape") {
      setOpen(false);
    }
  };

  return (
    <div ref={rootRef} className="relative">
      <input
        id={id}
        role="combobox"
        aria-expanded={open}
        aria-controls={listId}
        aria-autocomplete="list"
        className={className}
        value={value}
        onChange={(event) => {
          onChange(event.target.value);
          setOpen(true);
        }}
        onFocus={() => setOpen(true)}
        onKeyDown={onKeyDown}
        autoComplete="off"
        required={required}
      />

      {open && items.length > 0 && (
        <ul
          id={listId}
          role="listbox"
          className="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-xl border border-gray-200 bg-white py-1 shadow-lg"
        >
          {items.map((item, index) => {
            const selected = index === highlight;
            const key = item.type === "custom" ? "custom" : item.option.name;

            return (
              <li key={key} role="option" aria-selected={selected}>
                <button
                  type="button"
                  className={`flex w-full px-3 py-2 text-left text-sm ${selected ? "bg-[#006837]/10 text-[#006837]" : "text-gray-800 hover:bg-gray-50"} ${item.type === "custom" ? "font-semibold" : ""}`}
                  onMouseEnter={() => setHighlight(index)}
                  onMouseDown={(event) => event.preventDefault()}
                  onClick={() => selectValue(item.type === "custom" ? value.trim() : item.option.name)}
                >
                  {item.type === "custom" ? item.label : item.option.name}
                </button>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
};

export default UniversityCombobox;

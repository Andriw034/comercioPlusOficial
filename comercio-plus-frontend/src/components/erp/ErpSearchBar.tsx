type ErpSearchBarProps = {
  value: string
  onChange: (value: string) => void
  placeholder?: string
}

export function ErpSearchBar({ value, onChange, placeholder = 'Buscar...' }: ErpSearchBarProps) {
  return (
    <div className="relative">
      <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[13px] text-slate-400">Q</span>
      <input
        type="text"
        value={value}
        onChange={(event) => onChange(event.target.value)}
        placeholder={placeholder}
        className="h-9 w-full rounded-xl border py-0 pl-9 pr-3 text-[13px] font-medium text-slate-700 outline-none transition-all focus:ring-2 focus:ring-[#FFA14F]/40"
        style={{
          border: '1px solid rgba(0,0,0,0.12)',
          background: '#FFFFFF',
        }}
      />
    </div>
  )
}

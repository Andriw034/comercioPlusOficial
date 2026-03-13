type Option = {
  value: string
  label: string
}

type ErpFilterSelectProps = {
  value: string
  onChange: (value: string) => void
  options: Option[]
  placeholder?: string
}

export function ErpFilterSelect({ value, onChange, options, placeholder = 'Todos' }: ErpFilterSelectProps) {
  return (
    <select
      value={value}
      onChange={(event) => onChange(event.target.value)}
      className="h-9 rounded-xl border px-3 text-[13px] font-medium text-slate-700 outline-none"
      style={{ border: '1px solid rgba(0,0,0,0.12)', background: '#FFFFFF' }}
    >
      <option value="">{placeholder}</option>
      {options.map((option) => (
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      ))}
    </select>
  )
}

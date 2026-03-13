import type { ReactNode } from 'react'

type ErpPageHeaderProps = {
  breadcrumb?: string
  title: string
  subtitle?: string
  actions?: ReactNode
}

export function ErpPageHeader({ breadcrumb, title, subtitle, actions }: ErpPageHeaderProps) {
  return (
    <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
      <div>
        {breadcrumb ? <p className="mb-0.5 text-[12px] font-medium text-slate-400">{breadcrumb}</p> : null}
        <h1 className="text-[26px] font-black leading-tight text-slate-900">{title}</h1>
        {subtitle ? <p className="mt-0.5 text-[12px] text-slate-500">{subtitle}</p> : null}
      </div>
      {actions ? <div className="flex flex-wrap items-center gap-2">{actions}</div> : null}
    </div>
  )
}

import { Icon } from '@/components/Icon'

type DashboardTopbarProps = {
  storeName?: string
  onMenuClick: () => void
}

export default function DashboardTopbar({
  storeName = 'Panel ComercioPlus',
  onMenuClick,
}: DashboardTopbarProps) {
  return (
    <header className="sticky top-0 z-20 mb-4 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm md:hidden dark:border-slate-800 dark:bg-slate-900">
      <button
        type="button"
        onClick={onMenuClick}
        className="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        aria-label="Abrir menu del dashboard"
      >
        <Icon name="menu" size={18} />
      </button>

      <p className="truncate text-right text-sm font-semibold text-slate-700 dark:text-slate-100">
        {storeName}
      </p>
    </header>
  )
}

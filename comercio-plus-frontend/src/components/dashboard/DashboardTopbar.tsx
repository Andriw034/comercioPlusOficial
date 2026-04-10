import { useEffect } from 'react'
import { Icon } from '@/components/Icon'
import InventorySearchBar from '@/components/inventory/InventorySearchBar'

type DashboardTopbarProps = {
  storeName?: string
  onMenuClick: () => void
}

export default function DashboardTopbar({
  storeName = 'Panel ComercioPlus',
  onMenuClick,
}: DashboardTopbarProps) {
  // Ctrl+K / Cmd+K → open AI chat (handled by FloatingChatButton)
  useEffect(() => {
    const handler = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault()
        window.dispatchEvent(new CustomEvent('open-ai-chat'))
      }
    }
    window.addEventListener('keydown', handler)
    return () => window.removeEventListener('keydown', handler)
  }, [])

  return (
    <header className="sticky top-0 z-20 mb-4 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <button
        type="button"
        onClick={onMenuClick}
        className="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100 md:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        aria-label="Abrir menu del dashboard"
      >
        <Icon name="menu" size={18} />
      </button>

      <div className="hidden flex-1 justify-center md:flex">
        <InventorySearchBar />
      </div>

      <p className="truncate text-right text-sm font-semibold text-slate-700 md:hidden dark:text-slate-100">
        {storeName}
      </p>
    </header>
  )
}

import { useEffect } from 'react'
import { Icon } from '@/components/Icon'
import Sidebar from '@/components/dashboard/Sidebar'

type SidebarDrawerProps = {
  open: boolean
  store: Parameters<typeof Sidebar>[0]['store']
  onClose: () => void
}

export default function SidebarDrawer({ open, store, onClose }: SidebarDrawerProps) {
  useEffect(() => {
    if (!open) return

    const originalOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'

    return () => {
      document.body.style.overflow = originalOverflow
    }
  }, [open])

  useEffect(() => {
    if (!open) return

    const handleEsc = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onClose()
      }
    }

    window.addEventListener('keydown', handleEsc)
    return () => window.removeEventListener('keydown', handleEsc)
  }, [open, onClose])

  if (!open) return null

  return (
    <div className="fixed inset-0 z-50 md:hidden">
      <button
        type="button"
        className="absolute inset-0 bg-slate-950/50"
        onClick={onClose}
        aria-label="Cerrar menu lateral"
      />

      <div className="relative h-full max-w-[88vw]">
        <button
          type="button"
          className="absolute right-2 top-2 z-10 inline-flex h-8 w-8 items-center justify-center rounded-md border border-white/25 bg-black/40 text-white"
          onClick={onClose}
          aria-label="Cerrar"
        >
          <Icon name="x" size={16} />
        </button>
        <Sidebar store={store} />
      </div>
    </div>
  )
}

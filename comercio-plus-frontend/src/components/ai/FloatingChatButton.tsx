import { useState, useEffect } from 'react'
import { MessageCircle } from 'lucide-react'
import ChatOverlay from './ChatOverlay'

export default function FloatingChatButton() {
  const [isOpen, setIsOpen] = useState(false)

  // Listen for open-ai-chat events (from search bar, Ctrl+K, etc.)
  useEffect(() => {
    const handler = () => setIsOpen(true)
    window.addEventListener('open-ai-chat', handler)
    return () => window.removeEventListener('open-ai-chat', handler)
  }, [])

  return (
    <>
      <button
        onClick={() => setIsOpen(true)}
        className="group fixed bottom-6 right-6 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-comercioplus-600 text-white shadow-lg transition-all duration-200 hover:scale-110 hover:bg-comercioplus-700 hover:shadow-xl"
        aria-label="Abrir asistente de repuestos"
      >
        <MessageCircle className="h-6 w-6" />

        {/* Tooltip */}
        <span className="pointer-events-none absolute right-full mr-3 whitespace-nowrap rounded-lg bg-slate-900 px-3 py-2 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
          Asistente IA (Ctrl+K)
        </span>

        {/* Badge */}
        <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[9px] font-bold text-white">
          AI
        </span>
      </button>

      <ChatOverlay isOpen={isOpen} onClose={() => setIsOpen(false)} />
    </>
  )
}

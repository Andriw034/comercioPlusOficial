import { createContext, useContext, useState, type ReactNode } from 'react'

export type ActiveStore = {
  id: string
  name: string
  logo: string | null
  slug: string
} | null

interface StoreContextType {
  activeStore: ActiveStore
  setActiveStore: (store: ActiveStore) => void
}

const StoreContext = createContext<StoreContextType | undefined>(undefined)

export function StoreProvider({ children }: { children: ReactNode }) {
  const [activeStore, setActiveStore] = useState<ActiveStore>(null)

  return (
    <StoreContext.Provider value={{ activeStore, setActiveStore }}>
      {children}
    </StoreContext.Provider>
  )
}

export function useStoreContext() {
  const ctx = useContext(StoreContext)
  if (!ctx) throw new Error('useStoreContext must be used within a StoreProvider')
  return ctx
}

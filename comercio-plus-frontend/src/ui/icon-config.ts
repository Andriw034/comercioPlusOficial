export type IconVariant = 'fa' | 'emoji'

const ICON_VARIANT_STORAGE_KEY = 'cp-icon-variant'
const DEFAULT_ICON_VARIANT: IconVariant = 'fa'

export const emojiIconMap = {
  cart: '🛒',
  store: '🏪',
  package: '📦',
  'file-text': '🧾',
  users: '👥',
  tag: '🏷️',
  camera: '📷',
  chart: '📊',
  settings: '⚙️',
  logout: '🚪',
} as const

export const getGlobalIconVariant = (): IconVariant => {
  if (typeof window === 'undefined') return DEFAULT_ICON_VARIANT
  const stored = localStorage.getItem(ICON_VARIANT_STORAGE_KEY)
  return stored === 'emoji' || stored === 'fa' ? stored : DEFAULT_ICON_VARIANT
}

export const setGlobalIconVariant = (variant: IconVariant) => {
  if (typeof window === 'undefined') return
  localStorage.setItem(ICON_VARIANT_STORAGE_KEY, variant)
}

export const resolveIconVariant = (variant?: IconVariant): IconVariant => variant || getGlobalIconVariant()

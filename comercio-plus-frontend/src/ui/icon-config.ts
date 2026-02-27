export type IconVariant = 'fa' | 'emoji'

const ICON_VARIANT_STORAGE_KEY = 'cp-icon-variant'
const ENV_ICON_VARIANT = String(import.meta.env.VITE_ICON_VARIANT || '')
  .trim()
  .toLowerCase()

const DEFAULT_ICON_VARIANT: IconVariant = ENV_ICON_VARIANT === 'fa' || ENV_ICON_VARIANT === 'emoji'
  ? (ENV_ICON_VARIANT as IconVariant)
  : 'emoji'

export const emojiIconMap = {
  cart: '\uD83D\uDED2',
  store: '\uD83C\uDFEA',
  package: '\uD83D\uDCE6',
  'file-text': '\uD83D\uDCC4',
  users: '\uD83D\uDC65',
  tag: '\uD83C\uDFF7\uFE0F',
  camera: '\uD83D\uDCF7',
  chart: '\uD83D\uDCCA',
  settings: '\u2699\uFE0F',
  logout: '\uD83D\uDEAA',
} as const

export const getGlobalIconVariant = (): IconVariant => {
  if (ENV_ICON_VARIANT === 'fa' || ENV_ICON_VARIANT === 'emoji') {
    return ENV_ICON_VARIANT as IconVariant
  }
  if (typeof window === 'undefined') return DEFAULT_ICON_VARIANT
  const stored = localStorage.getItem(ICON_VARIANT_STORAGE_KEY)
  return stored === 'emoji' || stored === 'fa' ? stored : DEFAULT_ICON_VARIANT
}

export const setGlobalIconVariant = (variant: IconVariant) => {
  if (typeof window === 'undefined') return
  if (ENV_ICON_VARIANT === 'fa' || ENV_ICON_VARIANT === 'emoji') return
  localStorage.setItem(ICON_VARIANT_STORAGE_KEY, variant)
}

export const resolveIconVariant = (variant?: IconVariant): IconVariant => variant || getGlobalIconVariant()

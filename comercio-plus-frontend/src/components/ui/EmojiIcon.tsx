import { useMemo, useState } from 'react'

type EmojiIconProps = {
  name: string
  size?: number
  className?: string
  decorative?: boolean
}

const iconifyBase = String(import.meta.env.VITE_EMOJI_API_BASE || 'https://api.iconify.design')
  .trim()
  .replace(/\/+$/, '')

const emojiSet = String(import.meta.env.VITE_EMOJI_ICON_SET || 'fluent-emoji').trim() || 'fluent-emoji'

export const emojiIconNameMap: Record<string, string> = {
  home: 'house',
  search: 'magnifying-glass-tilted-left',
  menu: 'hamburger',
  x: 'cross-mark',
  'arrow-left': 'left-arrow',
  'arrow-right': 'right-arrow',
  'chevron-down': 'down-arrow',
  'chevron-up': 'up-arrow',
  'chevron-left': 'left-arrow',
  'chevron-right': 'right-arrow',
  cart: 'shopping-cart',
  store: 'department-store',
  bike: 'bicycle',
  package: 'package',
  tag: 'label',
  'credit-card': 'credit-card',
  truck: 'delivery-truck',
  user: 'bust-in-silhouette',
  'user-plus': 'bust-in-silhouette',
  users: 'busts-in-silhouette',
  heart: 'red-heart',
  bell: 'bell',
  settings: 'gear',
  logout: 'open-mailbox-with-raised-flag',
  edit: 'memo',
  trash: 'wastebasket',
  save: 'floppy-disk',
  upload: 'up-arrow',
  download: 'down-arrow',
  eye: 'eyes',
  'eye-off': 'prohibited',
  check: 'check-mark-button',
  alert: 'warning',
  info: 'information',
  'x-circle': 'cross-mark-button',
  'check-circle': 'check-mark-button',
  loader: 'hourglass-done',
  mail: 'e-mail',
  message: 'speech-balloon',
  phone: 'telephone-receiver',
  send: 'outbox-tray',
  trending: 'chart-increasing',
  dollar: 'money-bag',
  chart: 'bar-chart',
  calendar: 'calendar',
  shield: 'shield',
  lock: 'locked',
  unlock: 'unlocked',
  key: 'key',
  image: 'framed-picture',
  file: 'page-facing-up',
  'file-text': 'page-facing-up',
  folder: 'file-folder',
  camera: 'camera-with-flash',
  grid: 'squared-key',
  list: 'spiral-notepad',
  star: 'star',
  filter: 'funnel',
  rocket: 'rocket',
  headset: 'headphone',
  bank: 'bank',
  wallet: 'wallet',
  smartphone: 'mobile-phone',
  refresh: 'counterclockwise-arrows-button',
  external: 'right-arrow-curving-up',
  link: 'link',
  copy: 'clipboard',
  share: 'outbox-tray',
}

const fallbackEmojiMap: Record<string, string> = {
  home: '\uD83C\uDFE0',
  search: '\uD83D\uDD0D',
  menu: '\u2630\uFE0F',
  x: '\u274C',
  'arrow-left': '\u2B05\uFE0F',
  'arrow-right': '\u27A1\uFE0F',
  'chevron-down': '\u2B07\uFE0F',
  'chevron-up': '\u2B06\uFE0F',
  'chevron-left': '\u2B05\uFE0F',
  'chevron-right': '\u27A1\uFE0F',
  cart: '\uD83D\uDED2',
  store: '\uD83C\uDFEA',
  bike: '\uD83D\uDEB2',
  package: '\uD83D\uDCE6',
  tag: '\uD83C\uDFF7\uFE0F',
  'credit-card': '\uD83D\uDCB3',
  truck: '\uD83D\uDE9A',
  user: '\uD83D\uDC64',
  'user-plus': '\uD83D\uDC64',
  users: '\uD83D\uDC65',
  heart: '\u2764\uFE0F',
  bell: '\uD83D\uDD14',
  settings: '\u2699\uFE0F',
  logout: '\uD83D\uDEAA',
  plus: '\u2795',
  minus: '\u2796',
  edit: '\u270F\uFE0F',
  trash: '\uD83D\uDDD1\uFE0F',
  save: '\uD83D\uDCBE',
  upload: '\u2B06\uFE0F',
  download: '\u2B07\uFE0F',
  eye: '\uD83D\uDC40',
  'eye-off': '\uD83D\uDEAB',
  check: '\u2705',
  alert: '\u26A0\uFE0F',
  info: '\u2139\uFE0F',
  'x-circle': '\u274C',
  'check-circle': '\u2705',
  loader: '\u23F3',
  mail: '\u2709\uFE0F',
  message: '\uD83D\uDCAC',
  phone: '\uD83D\uDCF1',
  send: '\uD83D\uDCE4',
  trending: '\uD83D\uDCC8',
  dollar: '\uD83D\uDCB0',
  chart: '\uD83D\uDCCA',
  calendar: '\uD83D\uDCC5',
  clock: '\uD83D\uDD52',
  shield: '\uD83D\uDEE1\uFE0F',
  lock: '\uD83D\uDD12',
  unlock: '\uD83D\uDD13',
  key: '\uD83D\uDD11',
  image: '\uD83D\uDDBC\uFE0F',
  file: '\uD83D\uDCC4',
  'file-text': '\uD83D\uDCC4',
  folder: '\uD83D\uDCC1',
  camera: '\uD83D\uDCF7',
  grid: '\uD83D\uDD22',
  list: '\uD83D\uDCDD',
  star: '\u2B50',
  filter: '\uD83D\uDD3D',
  rocket: '\uD83D\uDE80',
  headset: '\uD83C\uDFA7',
  bank: '\uD83C\uDFE6',
  wallet: '\uD83D\uDC5B',
  smartphone: '\uD83D\uDCF1',
  refresh: '\uD83D\uDD04',
  external: '\u2197\uFE0F',
  link: '\uD83D\uDD17',
  copy: '\uD83D\uDCCB',
  share: '\uD83D\uDCE4',
}

export const hasEmojiIcon = (name: string): boolean => Boolean(emojiIconNameMap[name])

function resolveSrc(name: string): string {
  const icon = emojiIconNameMap[name]
  if (!icon) return ''
  return `${iconifyBase}/${emojiSet}/${icon}.svg`
}

export default function EmojiIcon({ name, size = 16, className = '', decorative = true }: EmojiIconProps) {
  const [failedSrc, setFailedSrc] = useState('')
  const src = useMemo(() => resolveSrc(name), [name])
  const failed = failedSrc === src
  const fallbackEmoji = fallbackEmojiMap[name] || '\u2728'

  if (!src || failed) {
    return (
      <span
        className={`inline-flex shrink-0 items-center justify-center ${className}`.trim()}
        style={{ width: size, height: size, fontSize: Math.max(12, Math.round(size * 0.82)), lineHeight: 1 }}
        aria-hidden={decorative}
      >
        {fallbackEmoji}
      </span>
    )
  }

  return (
    <img
      src={src}
      alt=""
      width={size}
      height={size}
      loading="lazy"
      decoding="async"
      onError={() => setFailedSrc(src)}
      className={`inline-flex shrink-0 align-middle ${className}`.trim()}
      style={{ width: size, height: size }}
      aria-hidden={decorative}
    />
  )
}

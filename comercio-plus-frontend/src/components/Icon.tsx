import * as LucideIcons from 'lucide-react'
import { resolveIconVariant, type IconVariant } from '@/ui/icon-config'
import EmojiIcon, { hasEmojiIcon } from '@/components/ui/EmojiIcon'

const iconMap = {
  home: LucideIcons.Home,
  search: LucideIcons.Search,
  menu: LucideIcons.Menu,
  'x': LucideIcons.X,
  'arrow-left': LucideIcons.ArrowLeft,
  'arrow-right': LucideIcons.ArrowRight,
  'chevron-down': LucideIcons.ChevronDown,
  'chevron-up': LucideIcons.ChevronUp,
  'chevron-left': LucideIcons.ChevronLeft,
  'chevron-right': LucideIcons.ChevronRight,

  cart: LucideIcons.ShoppingCart,
  store: LucideIcons.Store,
  bike: LucideIcons.Bike,
  package: LucideIcons.Package,
  tag: LucideIcons.Tag,
  'credit-card': LucideIcons.CreditCard,
  truck: LucideIcons.Truck,

  user: LucideIcons.User,
  'user-plus': LucideIcons.UserPlus,
  users: LucideIcons.Users,
  heart: LucideIcons.Heart,
  bell: LucideIcons.Bell,
  settings: LucideIcons.Settings,
  logout: LucideIcons.LogOut,

  plus: LucideIcons.Plus,
  minus: LucideIcons.Minus,
  edit: LucideIcons.Edit,
  trash: LucideIcons.Trash2,
  save: LucideIcons.Save,
  upload: LucideIcons.Upload,
  download: LucideIcons.Download,
  'eye': LucideIcons.Eye,
  'eye-off': LucideIcons.EyeOff,

  check: LucideIcons.Check,
  alert: LucideIcons.AlertCircle,
  info: LucideIcons.Info,
  'x-circle': LucideIcons.XCircle,
  'check-circle': LucideIcons.CheckCircle,
  loader: LucideIcons.Loader2,

  mail: LucideIcons.Mail,
  message: LucideIcons.MessageSquare,
  phone: LucideIcons.Phone,
  send: LucideIcons.Send,
  facebook: LucideIcons.Facebook,
  instagram: LucideIcons.Instagram,
  twitter: LucideIcons.Twitter,
  linkedin: LucideIcons.Linkedin,
  youtube: LucideIcons.Youtube,

  trending: LucideIcons.TrendingUp,
  dollar: LucideIcons.DollarSign,
  chart: LucideIcons.BarChart3,
  'pie-chart': LucideIcons.PieChart,
  calendar: LucideIcons.Calendar,
  clock: LucideIcons.Clock,

  shield: LucideIcons.Shield,
  lock: LucideIcons.Lock,
  unlock: LucideIcons.Unlock,
  key: LucideIcons.Key,

  image: LucideIcons.Image,
  file: LucideIcons.File,
  'file-text': LucideIcons.FileText,
  folder: LucideIcons.Folder,
  camera: LucideIcons.Camera,

  grid: LucideIcons.Grid,
  list: LucideIcons.List,
  star: LucideIcons.Star,
  filter: LucideIcons.Filter,
  'more-vertical': LucideIcons.MoreVertical,
  'more-horizontal': LucideIcons.MoreHorizontal,

  rocket: LucideIcons.Rocket,
  headset: LucideIcons.Headphones,
  bank: LucideIcons.Building2,
  wallet: LucideIcons.Wallet,
  smartphone: LucideIcons.Smartphone,
  refresh: LucideIcons.RefreshCw,
  external: LucideIcons.ExternalLink,
  link: LucideIcons.Link,
  copy: LucideIcons.Copy,
  share: LucideIcons.Share2,

  default: LucideIcons.Circle,
}

export type IconName = keyof typeof iconMap

interface IconProps {
  name: IconName
  size?: number
  className?: string
  strokeWidth?: number
  variant?: IconVariant
}

export function Icon({ name, size = 20, className = '', strokeWidth, variant }: IconProps) {
  const IconComponent = iconMap[name] || iconMap.default
  const iconVariant = resolveIconVariant(variant)
  const normalizedSize = Math.round(size)

  if (iconVariant === 'emoji' && hasEmojiIcon(name)) {
    return <EmojiIcon name={name} size={normalizedSize} className={className} />
  }

  const computedStrokeWidth =
    strokeWidth ??
    (normalizedSize <= 16
      ? 2.2
      : normalizedSize <= 20
        ? 2.0
        : normalizedSize <= 24
          ? 1.9
          : normalizedSize <= 32
            ? 1.8
            : 1.7)

  return (
    <IconComponent
      size={normalizedSize}
      strokeWidth={computedStrokeWidth}
      className={`inline-flex shrink-0 align-middle ${className}`.trim()}
      style={{
        shapeRendering: 'geometricPrecision',
        WebkitFontSmoothing: 'antialiased',
        MozOsxFontSmoothing: 'grayscale',
      }}
      aria-hidden="true"
      focusable="false"
    />
  )
}

export function IconCircle({
  name,
  size = 40,
  iconSize = 20,
  className = '',
  iconClassName = '',
  variant,
}: {
  name: IconName
  size?: number
  iconSize?: number
  className?: string
  iconClassName?: string
  variant?: IconVariant
}) {
  return (
    <div
      className={`inline-flex items-center justify-center rounded-full ${className}`.trim()}
      style={{ width: size, height: size }}
    >
      <Icon name={name} size={iconSize} className={iconClassName} variant={variant} />
    </div>
  )
}

export function IconBox({
  name,
  size = 40,
  iconSize = 20,
  className = '',
  iconClassName = '',
  variant,
}: {
  name: IconName
  size?: number
  iconSize?: number
  className?: string
  iconClassName?: string
  variant?: IconVariant
}) {
  return (
    <div
      className={`inline-flex items-center justify-center rounded-xl ${className}`.trim()}
      style={{ width: size, height: size }}
    >
      <Icon name={name} size={iconSize} className={iconClassName} variant={variant} />
    </div>
  )
}

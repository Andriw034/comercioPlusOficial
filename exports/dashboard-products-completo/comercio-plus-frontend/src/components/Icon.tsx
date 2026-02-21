import * as LucideIcons from 'lucide-react'

// Mapeo de nombres simples a componentes Lucide
const iconMap = {
  // Navegación
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
  
  // E-commerce
  cart: LucideIcons.ShoppingCart,
  store: LucideIcons.Store,
  package: LucideIcons.Package,
  tag: LucideIcons.Tag,
  'credit-card': LucideIcons.CreditCard,
  truck: LucideIcons.Truck,
  
  // Usuario
  user: LucideIcons.User,
  'user-plus': LucideIcons.UserPlus,
  users: LucideIcons.Users,
  heart: LucideIcons.Heart,
  bell: LucideIcons.Bell,
  settings: LucideIcons.Settings,
  logout: LucideIcons.LogOut,
  
  // Acciones
  plus: LucideIcons.Plus,
  minus: LucideIcons.Minus,
  edit: LucideIcons.Edit,
  trash: LucideIcons.Trash2,
  save: LucideIcons.Save,
  upload: LucideIcons.Upload,
  download: LucideIcons.Download,
  'eye': LucideIcons.Eye,
  'eye-off': LucideIcons.EyeOff,
  
  // Estado
  check: LucideIcons.Check,
  alert: LucideIcons.AlertCircle,
  info: LucideIcons.Info,
  'x-circle': LucideIcons.XCircle,
  'check-circle': LucideIcons.CheckCircle,
  loader: LucideIcons.Loader2,
  
  // Comunicación
  mail: LucideIcons.Mail,
  message: LucideIcons.MessageSquare,
  phone: LucideIcons.Phone,
  send: LucideIcons.Send,
  
  // Negocio
  trending: LucideIcons.TrendingUp,
  dollar: LucideIcons.DollarSign,
  chart: LucideIcons.BarChart3,
  'pie-chart': LucideIcons.PieChart,
  calendar: LucideIcons.Calendar,
  clock: LucideIcons.Clock,
  
  // Seguridad
  shield: LucideIcons.Shield,
  lock: LucideIcons.Lock,
  unlock: LucideIcons.Unlock,
  key: LucideIcons.Key,
  
  // Multimedia
  image: LucideIcons.Image,
  file: LucideIcons.File,
  'file-text': LucideIcons.FileText,
  folder: LucideIcons.Folder,
  camera: LucideIcons.Camera,
  
  // UI
  grid: LucideIcons.Grid,
  list: LucideIcons.List,
  star: LucideIcons.Star,
  filter: LucideIcons.Filter,
  'more-vertical': LucideIcons.MoreVertical,
  'more-horizontal': LucideIcons.MoreHorizontal,
  
  // Misceláneos
  rocket: LucideIcons.Rocket,
  headset: LucideIcons.Headphones,
  bank: LucideIcons.Building2,
  smartphone: LucideIcons.Smartphone,
  refresh: LucideIcons.RefreshCw,
  external: LucideIcons.ExternalLink,
  link: LucideIcons.Link,
  copy: LucideIcons.Copy,
  share: LucideIcons.Share2,
  
  // Fallback
  default: LucideIcons.Circle,
}

export type IconName = keyof typeof iconMap

interface IconProps {
  name: IconName
  size?: number
  className?: string
  strokeWidth?: number
}

/**
 * Componente Icon optimizado para render natural
 * 
 * Características:
 * - Grosor adaptativo según tamaño (iconos pequeños más gruesos, grandes más finos)
 * - Tamaños normalizados para consistencia
 * - Render optimizado con geometricPrecision
 * - Accesibilidad integrada
 */
export function Icon({ name, size = 20, className = '', strokeWidth }: IconProps) {
  const IconComponent = iconMap[name] || iconMap.default

  // Normalizar tamaño a entero para consistencia
  const normalizedSize = Math.round(size)
  
  // Grosor adaptativo: iconos pequeños más gruesos, grandes más finos
  const computedStrokeWidth = strokeWidth ?? (
    normalizedSize <= 16 ? 2.2 :
    normalizedSize <= 20 ? 2.0 :
    normalizedSize <= 24 ? 1.9 :
    normalizedSize <= 32 ? 1.8 :
    1.7
  )

  return (
    <IconComponent
      size={normalizedSize}
      strokeWidth={computedStrokeWidth}
      className={`inline-flex shrink-0 align-middle ${className}`.trim()}
      style={{ 
        shapeRendering: 'geometricPrecision',
        // Optimización para render suave
        WebkitFontSmoothing: 'antialiased',
        MozOsxFontSmoothing: 'grayscale',
      }}
      aria-hidden="true"
      focusable="false"
    />
  )
}

/**
 * Variante de ícono con fondo circular
 */
export function IconCircle({ 
  name, 
  size = 40, 
  iconSize = 20,
  className = '',
  iconClassName = '',
}: {
  name: IconName
  size?: number
  iconSize?: number
  className?: string
  iconClassName?: string
}) {
  return (
    <div 
      className={`inline-flex items-center justify-center rounded-full ${className}`.trim()}
      style={{ width: size, height: size }}
    >
      <Icon name={name} size={iconSize} className={iconClassName} />
    </div>
  )
}

/**
 * Variante de ícono con fondo redondeado
 */
export function IconBox({ 
  name, 
  size = 40, 
  iconSize = 20,
  className = '',
  iconClassName = '',
}: {
  name: IconName
  size?: number
  iconSize?: number
  className?: string
  iconClassName?: string
}) {
  return (
    <div 
      className={`inline-flex items-center justify-center rounded-xl ${className}`.trim()}
      style={{ width: size, height: size }}
    >
      <Icon name={name} size={iconSize} className={iconClassName} />
    </div>
  )
}

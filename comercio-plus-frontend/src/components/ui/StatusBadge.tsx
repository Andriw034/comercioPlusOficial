import type { HTMLAttributes } from 'react'
import Badge from './Badge'

type BadgeVariant = 'brand' | 'neutral' | 'success' | 'warning' | 'danger'
type StatusBadgeSize = 'sm' | 'md'

type StatusConfig = {
  label: string
  variant: BadgeVariant
}

const STATUS_CONFIG: Record<string, StatusConfig> = {
  pending: { label: 'Pendiente', variant: 'warning' },
  processing: { label: 'Procesando', variant: 'neutral' },
  approved: { label: 'Aprobado', variant: 'success' },
  paid: { label: 'Pagado', variant: 'success' },
  completed: { label: 'Completado', variant: 'success' },
  picking: { label: 'Alistando', variant: 'brand' },
  picked: { label: 'Alistado', variant: 'brand' },
  packed: { label: 'Empacado', variant: 'brand' },
  ready: { label: 'Listo', variant: 'brand' },
  shipped: { label: 'Enviado', variant: 'brand' },
  delivered: { label: 'Entregado', variant: 'success' },
  canceled: { label: 'Cancelado', variant: 'danger' },
  cancelled: { label: 'Cancelado', variant: 'danger' },
  active: { label: 'Activo', variant: 'success' },
  inactive: { label: 'Inactivo', variant: 'neutral' },
  low: { label: 'Bajo', variant: 'warning' },
  out: { label: 'Agotado', variant: 'danger' },
  normal: { label: 'Normal', variant: 'success' },
}

const SIZE_CLASSES: Record<StatusBadgeSize, string> = {
  sm: 'px-2 py-0.5 text-[11px]',
  md: 'px-3 py-1 text-[12px]',
}

function normalizeStatus(status: string | null | undefined): string {
  return String(status || '')
    .trim()
    .toLowerCase()
    .replace(/-/g, '_')
    .replace(/\s+/g, '_')
    .replace(/[^a-z0-9_]+/g, '_')
    .replace(/_+/g, '_')
    .replace(/^_+|_+$/g, '')
}

function toFallbackLabel(normalizedStatus: string): string {
  const source = normalizedStatus || 'estado'
  return source
    .split('_')
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

export interface StatusBadgeProps extends Omit<HTMLAttributes<HTMLSpanElement>, 'children'> {
  status?: string | null
  size?: StatusBadgeSize
  label?: string
}

export default function StatusBadge({
  status,
  size = 'md',
  label,
  className = '',
  ...props
}: StatusBadgeProps) {
  const normalized = normalizeStatus(status)
  const mapped = STATUS_CONFIG[normalized] || { label: toFallbackLabel(normalized), variant: 'neutral' as const }

  return (
    <Badge
      variant={mapped.variant}
      className={`${SIZE_CLASSES[size]} ${className}`.trim()}
      data-status={normalized || 'unknown'}
      {...props}
    >
      {label || mapped.label}
    </Badge>
  )
}

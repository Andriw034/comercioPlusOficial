type Status =
  | 'paid'
  | 'pending'
  | 'cancelled'
  | 'processing'
  | 'active'
  | 'inactive'
  | 'draft'
  | 'sent'
  | 'accepted'
  | 'converted'
  | 'rejected'
  | 'low'
  | 'critical'
  | 'ok'
  | 'high'
  | 'medium'
  | 'completed'
  | 'approved'
  | 'overdue'
  | 'new'
  | 'vip'
  | 'regular'

type ErpBadgeProps = {
  status: Status
  label?: string
}

const CONFIG: Record<Status, { bg: string; color: string; dot: string }> = {
  paid: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  completed: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  approved: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  ok: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  active: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  accepted: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  pending: { bg: 'rgba(245,158,11,0.1)', color: '#F59E0B', dot: '#F59E0B' },
  processing: { bg: 'rgba(59,130,246,0.1)', color: '#3B82F6', dot: '#3B82F6' },
  medium: { bg: 'rgba(245,158,11,0.1)', color: '#F59E0B', dot: '#F59E0B' },
  sent: { bg: 'rgba(59,130,246,0.1)', color: '#3B82F6', dot: '#3B82F6' },
  converted: { bg: 'rgba(139,92,246,0.1)', color: '#8B5CF6', dot: '#8B5CF6' },
  vip: { bg: 'rgba(139,92,246,0.1)', color: '#8B5CF6', dot: '#8B5CF6' },
  cancelled: { bg: 'rgba(239,68,68,0.1)', color: '#EF4444', dot: '#EF4444' },
  critical: { bg: 'rgba(239,68,68,0.1)', color: '#EF4444', dot: '#EF4444' },
  rejected: { bg: 'rgba(239,68,68,0.1)', color: '#EF4444', dot: '#EF4444' },
  overdue: { bg: 'rgba(239,68,68,0.1)', color: '#EF4444', dot: '#EF4444' },
  inactive: { bg: 'rgba(148,163,184,0.15)', color: '#94A3B8', dot: '#94A3B8' },
  draft: { bg: 'rgba(148,163,184,0.15)', color: '#64748B', dot: '#94A3B8' },
  low: { bg: 'rgba(245,158,11,0.1)', color: '#F59E0B', dot: '#F59E0B' },
  high: { bg: 'rgba(16,185,129,0.1)', color: '#10B981', dot: '#10B981' },
  new: { bg: 'rgba(59,130,246,0.1)', color: '#3B82F6', dot: '#3B82F6' },
  regular: { bg: 'rgba(148,163,184,0.15)', color: '#64748B', dot: '#94A3B8' },
}

const LABELS: Record<Status, string> = {
  paid: 'Pagado',
  pending: 'Pendiente',
  cancelled: 'Cancelado',
  processing: 'Procesando',
  active: 'Activo',
  inactive: 'Inactivo',
  draft: 'Borrador',
  sent: 'Enviado',
  accepted: 'Aceptado',
  converted: 'Convertido',
  rejected: 'Rechazado',
  low: 'Bajo stock',
  critical: 'Critico',
  ok: 'OK',
  high: 'Alta',
  medium: 'Media',
  completed: 'Completado',
  approved: 'Aprobado',
  overdue: 'Vencido',
  new: 'Nuevo',
  vip: 'VIP',
  regular: 'Regular',
}

export function ErpBadge({ status, label }: ErpBadgeProps) {
  const cfg = CONFIG[status] || CONFIG.draft
  return (
    <span
      className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold"
      style={{ background: cfg.bg, color: cfg.color }}
    >
      <span className="h-1.5 w-1.5 rounded-full" style={{ background: cfg.dot }} />
      {label ?? LABELS[status]}
    </span>
  )
}

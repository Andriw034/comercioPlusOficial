import { useCallback, useEffect, useMemo, useState } from 'react'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'
import GlassCard from '@/components/ui/GlassCard'
import type { ElectronicDocument, DianStatus, InvoicingStats } from '@/types/api'
import {
  fetchDocuments,
  fetchDocument,
  fetchStats,
  createInvoice,
  sendToDAIN,
  cancelDocument,
  createCreditNote,
  fetchLogs,
  type InvoicingFilters,
} from '@/services/invoicing'
import type { ElectronicDocumentLog } from '@/types/api'

// ─── Helpers ───

const fmtCOP = (v: number) =>
  new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v)

const fmtDate = (d: string) =>
  new Date(d).toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' })

const statusLabel: Record<DianStatus, string> = {
  draft: 'Borrador',
  pending: 'Pendiente',
  approved: 'Aprobada',
  rejected: 'Rechazada',
  cancelled: 'Anulada',
}

const statusBadge: Record<DianStatus, 'pending' | 'paid' | 'processing' | 'cancelled'> = {
  draft: 'pending',
  pending: 'processing',
  approved: 'paid',
  rejected: 'cancelled',
  cancelled: 'cancelled',
}

const docTypeLabel: Record<string, string> = {
  invoice: 'Factura',
  credit_note: 'Nota Crédito',
  debit_note: 'Nota Débito',
}

const ID_TYPES = [
  { value: 'CC', label: 'CC - Cédula de Ciudadanía' },
  { value: 'NIT', label: 'NIT' },
  { value: 'CE', label: 'CE - Cédula de Extranjería' },
  { value: 'PP', label: 'PP - Pasaporte' },
  { value: 'TI', label: 'TI - Tarjeta de Identidad' },
]

type View = 'list' | 'detail' | 'create'

// ─── Componente principal ───

export default function DashboardInvoicingPage() {
  const [view, setView] = useState<View>('list')
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [documents, setDocuments] = useState<ElectronicDocument[]>([])
  const [stats, setStats] = useState<InvoicingStats | null>(null)
  const [meta, setMeta] = useState({ total: 0, current_page: 1, last_page: 1 })
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState('')
  const [selected, setSelected] = useState<ElectronicDocument | null>(null)
  const [logs, setLogs] = useState<ElectronicDocumentLog[]>([])
  const [actionLoading, setActionLoading] = useState(false)

  // ─── Cargar lista ───

  const loadData = useCallback(async (filters: InvoicingFilters = {}) => {
    setLoading(true)
    setError('')
    try {
      const [listRes, statsRes] = await Promise.all([
        fetchDocuments({ ...filters, per_page: 20 }),
        fetchStats(),
      ])
      setDocuments(listRes.data)
      setMeta({
        total: listRes.meta.total ?? 0,
        current_page: listRes.meta.current_page ?? 1,
        last_page: listRes.meta.last_page ?? 1,
      })
      setStats(statsRes.data)
    } catch {
      setError('Error al cargar documentos')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { loadData() }, [loadData])

  const filteredDocs = useMemo(() => {
    if (!search) return documents
    const q = search.toLowerCase()
    return documents.filter(
      d => d.customer_name.toLowerCase().includes(q)
        || d.customer_identification.includes(q)
        || `${d.prefix}-${d.number}`.includes(q)
    )
  }, [documents, search])

  const applyFilters = useCallback(() => {
    const filters: InvoicingFilters = {}
    if (statusFilter) filters.status = statusFilter
    if (search) filters.customer = search
    loadData(filters)
  }, [statusFilter, search, loadData])

  useEffect(() => { applyFilters() }, [statusFilter]) // eslint-disable-line react-hooks/exhaustive-deps

  // ─── Detalle ───

  const openDetail = useCallback(async (id: number) => {
    setActionLoading(true)
    try {
      const [docRes, logsRes] = await Promise.all([fetchDocument(id), fetchLogs(id)])
      setSelected(docRes.data)
      setLogs(logsRes.data)
      setView('detail')
    } catch {
      setError('Error al cargar detalle')
    } finally {
      setActionLoading(false)
    }
  }, [])

  // ─── Acciones ───

  const handleSend = useCallback(async (id: number) => {
    if (!confirm('¿Enviar este documento a la DIAN?')) return
    setActionLoading(true)
    try {
      await sendToDAIN(id)
      await openDetail(id)
      loadData()
    } catch {
      setError('Error al enviar a la DIAN')
    } finally {
      setActionLoading(false)
    }
  }, [openDetail, loadData])

  const handleCancel = useCallback(async (id: number) => {
    const reason = prompt('Razón de anulación:')
    if (!reason) return
    setActionLoading(true)
    try {
      await cancelDocument(id, reason)
      await openDetail(id)
      loadData()
    } catch {
      setError('Error al anular documento')
    } finally {
      setActionLoading(false)
    }
  }, [openDetail, loadData])

  const handleCreditNote = useCallback(async (id: number) => {
    const reason = prompt('Razón de la nota crédito:')
    if (!reason) return
    setActionLoading(true)
    try {
      const res = await createCreditNote(id, reason)
      await openDetail(res.data.id)
      loadData()
    } catch {
      setError('Error al crear nota crédito')
    } finally {
      setActionLoading(false)
    }
  }, [openDetail, loadData])

  // ─── Crear factura ───

  const [form, setForm] = useState({
    issuer_nit: '',
    issuer_name: '',
    customer_identification_type: 'CC',
    customer_identification: '',
    customer_name: '',
    customer_email: '',
    payment_method: 'contado',
    payment_means: 'efectivo',
    notes: '',
    items: [{ description: '', quantity: '1', unit_price: '', tax_rate: '19' }],
  })
  const [formError, setFormError] = useState('')
  const [saving, setSaving] = useState(false)

  const addItem = () => setForm(f => ({
    ...f,
    items: [...f.items, { description: '', quantity: '1', unit_price: '', tax_rate: '19' }],
  }))

  const removeItem = (i: number) => setForm(f => ({
    ...f,
    items: f.items.filter((_, idx) => idx !== i),
  }))

  const updateItem = (i: number, field: string, value: string) => setForm(f => ({
    ...f,
    items: f.items.map((item, idx) => idx === i ? { ...item, [field]: value } : item),
  }))

  const handleCreate = useCallback(async () => {
    setFormError('')
    if (!form.issuer_nit || !form.customer_identification || !form.customer_name) {
      setFormError('Complete los campos obligatorios (NIT emisor, identificación y nombre del cliente)')
      return
    }
    if (form.items.some(it => !it.description || !it.unit_price)) {
      setFormError('Cada item debe tener descripción y precio')
      return
    }
    setSaving(true)
    try {
      const payload = {
        ...form,
        items: form.items.map(it => ({
          description: it.description,
          quantity: Number(it.quantity) || 1,
          unit_price: Number(it.unit_price) || 0,
          tax_rate: Number(it.tax_rate) || 19,
        })),
      }
      const res = await createInvoice(payload)
      await openDetail(res.data.id)
      loadData()
      setForm({
        issuer_nit: form.issuer_nit,
        issuer_name: form.issuer_name,
        customer_identification_type: 'CC',
        customer_identification: '',
        customer_name: '',
        customer_email: '',
        payment_method: 'contado',
        payment_means: 'efectivo',
        notes: '',
        items: [{ description: '', quantity: '1', unit_price: '', tax_rate: '19' }],
      })
    } catch {
      setFormError('Error al crear factura')
    } finally {
      setSaving(false)
    }
  }, [form, openDetail, loadData])

  // ─── Render ───

  if (view === 'create') {
    return (
      <div className="space-y-6">
        <ErpPageHeader title="Nueva Factura Electrónica" actions={<ErpBtn variant="ghost" onClick={() => setView('list')}>Volver</ErpBtn>} />

        {formError && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{formError}</div>}

        <GlassCard>
          <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Emisor</h3>
          <div className="grid gap-4 sm:grid-cols-2">
            <input className="rounded-lg border px-3 py-2 text-sm" placeholder="NIT emisor *" value={form.issuer_nit} onChange={e => setForm(f => ({ ...f, issuer_nit: e.target.value }))} />
            <input className="rounded-lg border px-3 py-2 text-sm" placeholder="Nombre emisor" value={form.issuer_name} onChange={e => setForm(f => ({ ...f, issuer_name: e.target.value }))} />
          </div>
        </GlassCard>

        <GlassCard>
          <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Cliente</h3>
          <div className="grid gap-4 sm:grid-cols-2">
            <select className="rounded-lg border px-3 py-2 text-sm" value={form.customer_identification_type} onChange={e => setForm(f => ({ ...f, customer_identification_type: e.target.value }))}>
              {ID_TYPES.map(t => <option key={t.value} value={t.value}>{t.label}</option>)}
            </select>
            <input className="rounded-lg border px-3 py-2 text-sm" placeholder="Número identificación *" value={form.customer_identification} onChange={e => setForm(f => ({ ...f, customer_identification: e.target.value }))} />
            <input className="rounded-lg border px-3 py-2 text-sm" placeholder="Nombre cliente *" value={form.customer_name} onChange={e => setForm(f => ({ ...f, customer_name: e.target.value }))} />
            <input className="rounded-lg border px-3 py-2 text-sm" placeholder="Email cliente" value={form.customer_email} onChange={e => setForm(f => ({ ...f, customer_email: e.target.value }))} />
          </div>
        </GlassCard>

        <GlassCard>
          <h3 className="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Pago</h3>
          <div className="grid gap-4 sm:grid-cols-2">
            <select className="rounded-lg border px-3 py-2 text-sm" value={form.payment_method} onChange={e => setForm(f => ({ ...f, payment_method: e.target.value }))}>
              <option value="contado">Contado</option>
              <option value="credito">Crédito</option>
            </select>
            <select className="rounded-lg border px-3 py-2 text-sm" value={form.payment_means} onChange={e => setForm(f => ({ ...f, payment_means: e.target.value }))}>
              <option value="efectivo">Efectivo</option>
              <option value="transferencia">Transferencia</option>
              <option value="tarjeta">Tarjeta</option>
            </select>
          </div>
        </GlassCard>

        <GlassCard>
          <div className="mb-4 flex items-center justify-between">
            <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">Items</h3>
            <ErpBtn size="sm" onClick={addItem}>+ Agregar item</ErpBtn>
          </div>
          <div className="space-y-3">
            {form.items.map((item, i) => (
              <div key={i} className="grid gap-2 sm:grid-cols-5 items-end">
                <input className="rounded-lg border px-3 py-2 text-sm sm:col-span-2" placeholder="Descripción *" value={item.description} onChange={e => updateItem(i, 'description', e.target.value)} />
                <input className="rounded-lg border px-3 py-2 text-sm" type="number" min="1" placeholder="Cant." value={item.quantity} onChange={e => updateItem(i, 'quantity', e.target.value)} />
                <input className="rounded-lg border px-3 py-2 text-sm" type="number" min="0" placeholder="Precio *" value={item.unit_price} onChange={e => updateItem(i, 'unit_price', e.target.value)} />
                <div className="flex gap-2">
                  <input className="w-20 rounded-lg border px-3 py-2 text-sm" type="number" min="0" max="100" placeholder="IVA%" value={item.tax_rate} onChange={e => updateItem(i, 'tax_rate', e.target.value)} />
                  {form.items.length > 1 && (
                    <button className="rounded-lg border border-red-200 px-2 py-1 text-xs text-red-600 hover:bg-red-50" onClick={() => removeItem(i)}>X</button>
                  )}
                </div>
              </div>
            ))}
          </div>
        </GlassCard>

        <GlassCard>
          <textarea className="w-full rounded-lg border px-3 py-2 text-sm" rows={2} placeholder="Notas (opcional)" value={form.notes} onChange={e => setForm(f => ({ ...f, notes: e.target.value }))} />
        </GlassCard>

        <div className="flex justify-end">
          <ErpBtn onClick={handleCreate} disabled={saving}>
            {saving ? 'Creando...' : 'Crear Factura'}
          </ErpBtn>
        </div>
      </div>
    )
  }

  if (view === 'detail' && selected) {
    return (
      <div className="space-y-6">
        <ErpPageHeader title={`${docTypeLabel[selected.document_type] ?? 'Documento'} ${selected.prefix}-${String(selected.number).padStart(10, '0')}`} actions={<ErpBtn variant="ghost" onClick={() => { setView('list'); setSelected(null) }}>Volver</ErpBtn>} />

        <div className="grid gap-4 sm:grid-cols-4">
          <ErpKpiCard label="Estado" value={statusLabel[selected.dian_status]} icon="file-text" iconBg="rgba(59,130,246,0.1)" iconColor="#3B82F6" />
          <ErpKpiCard label="Subtotal" value={fmtCOP(Number(selected.subtotal))} icon="dollar" iconBg="rgba(16,185,129,0.1)" iconColor="#10B981" />
          <ErpKpiCard label="IVA" value={fmtCOP(Number(selected.tax_total))} icon="tag" iconBg="rgba(245,158,11,0.1)" iconColor="#F59E0B" />
          <ErpKpiCard label="Total" value={fmtCOP(Number(selected.total))} icon="trending" iconBg="rgba(139,92,246,0.1)" iconColor="#8B5CF6" />
        </div>

        {/* Acciones */}
        <div className="flex flex-wrap gap-2">
          {selected.dian_status === 'draft' && (
            <ErpBtn onClick={() => handleSend(selected.id)} disabled={actionLoading}>Enviar a DIAN</ErpBtn>
          )}
          {selected.dian_status === 'approved' && (
            <>
              <ErpBtn variant="ghost" onClick={() => handleCreditNote(selected.id)} disabled={actionLoading}>Nota Crédito</ErpBtn>
              <ErpBtn variant="ghost" onClick={() => handleCancel(selected.id)} disabled={actionLoading}>Anular</ErpBtn>
            </>
          )}
        </div>

        {/* Emisor y cliente */}
        <div className="grid gap-4 sm:grid-cols-2">
          <GlassCard>
            <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Emisor</h3>
            <p className="text-sm font-medium">{selected.issuer_name}</p>
            <p className="text-xs text-slate-500">NIT: {selected.issuer_nit}</p>
            {selected.issuer_email && <p className="text-xs text-slate-500">{selected.issuer_email}</p>}
            {selected.issuer_city && <p className="text-xs text-slate-500">{selected.issuer_city}, {selected.issuer_department}</p>}
          </GlassCard>
          <GlassCard>
            <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Cliente</h3>
            <p className="text-sm font-medium">{selected.customer_name}</p>
            <p className="text-xs text-slate-500">{selected.customer_identification_type}: {selected.customer_identification}</p>
            {selected.customer_email && <p className="text-xs text-slate-500">{selected.customer_email}</p>}
            {selected.customer_city && <p className="text-xs text-slate-500">{selected.customer_city}, {selected.customer_department}</p>}
          </GlassCard>
        </div>

        {/* CUFE */}
        {selected.cufe && (
          <GlassCard>
            <h3 className="mb-2 text-sm font-semibold uppercase tracking-wider text-slate-500">CUFE</h3>
            <p className="break-all font-mono text-xs text-slate-600">{selected.cufe}</p>
          </GlassCard>
        )}

        {/* Items */}
        <GlassCard>
          <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Items ({selected.items?.length ?? 0})</h3>
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b text-xs text-slate-500">
                <tr>
                  <th className="pb-2">#</th>
                  <th className="pb-2">Descripción</th>
                  <th className="pb-2 text-right">Cant.</th>
                  <th className="pb-2 text-right">P. Unit</th>
                  <th className="pb-2 text-right">IVA</th>
                  <th className="pb-2 text-right">Total</th>
                </tr>
              </thead>
              <tbody>
                {selected.items?.map(item => (
                  <tr key={item.id} className="border-b border-slate-100">
                    <td className="py-2 text-slate-400">{item.line_number}</td>
                    <td className="py-2">{item.description}</td>
                    <td className="py-2 text-right">{item.quantity}</td>
                    <td className="py-2 text-right">{fmtCOP(Number(item.unit_price))}</td>
                    <td className="py-2 text-right">{item.tax_rate}%</td>
                    <td className="py-2 text-right font-medium">{fmtCOP(Number(item.line_total))}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </GlassCard>

        {/* Impuestos consolidados */}
        {selected.taxes && selected.taxes.length > 0 && (
          <GlassCard>
            <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Impuestos</h3>
            <div className="space-y-1">
              {selected.taxes.map(t => (
                <div key={t.id} className="flex justify-between text-sm">
                  <span className="text-slate-600">{t.tax_type} {t.tax_rate}% (base: {fmtCOP(Number(t.taxable_amount))})</span>
                  <span className="font-medium">{fmtCOP(Number(t.tax_amount))}</span>
                </div>
              ))}
            </div>
          </GlassCard>
        )}

        {/* Historial */}
        <GlassCard>
          <h3 className="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500">Historial</h3>
          <div className="space-y-2">
            {logs.map(log => (
              <div key={log.id} className="flex items-start gap-3 border-b border-slate-50 pb-2 text-sm">
                <span className="mt-0.5 shrink-0 rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{log.action}</span>
                <div className="min-w-0 flex-1">
                  {log.message && <p className="text-slate-700">{log.message}</p>}
                  <p className="text-xs text-slate-400">{fmtDate(log.created_at)} {log.user?.name ? `· ${log.user.name}` : ''}</p>
                </div>
              </div>
            ))}
          </div>
        </GlassCard>
      </div>
    )
  }

  // ─── Lista ───

  return (
    <div className="space-y-6">
      <ErpPageHeader title="Facturación Electrónica" actions={<ErpBtn onClick={() => setView('create')}>+ Nueva Factura</ErpBtn>} />

      {error && <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}

      {/* KPIs */}
      {stats && (
        <div className="grid gap-4 sm:grid-cols-4">
          <ErpKpiCard label="Total documentos" value={String(stats.total_documents)} icon="file-text" iconBg="rgba(59,130,246,0.1)" iconColor="#3B82F6" />
          <ErpKpiCard label="Aprobadas" value={String(stats.by_status?.approved ?? 0)} icon="check-circle" iconBg="rgba(16,185,129,0.1)" iconColor="#10B981" />
          <ErpKpiCard label="Pendientes" value={String((stats.by_status?.draft ?? 0) + (stats.by_status?.pending ?? 0))} icon="clock" iconBg="rgba(245,158,11,0.1)" iconColor="#F59E0B" />
          <ErpKpiCard label="Total facturado" value={fmtCOP(stats.total_invoiced_amount)} icon="dollar" iconBg="rgba(139,92,246,0.1)" iconColor="#8B5CF6" />
        </div>
      )}

      {/* Filtros */}
      <div className="flex flex-wrap items-center gap-3">
        <div className="flex-1">
          <ErpSearchBar value={search} onChange={setSearch} placeholder="Buscar por cliente, NIT o número..." />
        </div>
        <ErpFilterSelect
          value={statusFilter}
          onChange={setStatusFilter}
          options={[
            { value: '', label: 'Todos los estados' },
            { value: 'draft', label: 'Borrador' },
            { value: 'pending', label: 'Pendiente' },
            { value: 'approved', label: 'Aprobada' },
            { value: 'rejected', label: 'Rechazada' },
            { value: 'cancelled', label: 'Anulada' },
          ]}
        />
      </div>

      {/* Tabla */}
      <GlassCard>
        {loading ? (
          <div className="space-y-3">
            {[1, 2, 3, 4, 5].map(i => (
              <div key={i} className="h-12 animate-pulse rounded-lg bg-slate-100" />
            ))}
          </div>
        ) : filteredDocs.length === 0 ? (
          <p className="py-8 text-center text-sm text-slate-400">No hay documentos electrónicos</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b text-xs text-slate-500">
                <tr>
                  <th className="pb-2">Número</th>
                  <th className="pb-2">Tipo</th>
                  <th className="pb-2">Cliente</th>
                  <th className="pb-2">Estado</th>
                  <th className="pb-2 text-right">Total</th>
                  <th className="pb-2">Fecha</th>
                  <th className="pb-2"></th>
                </tr>
              </thead>
              <tbody>
                {filteredDocs.map(doc => (
                  <tr key={doc.id} className="cursor-pointer border-b border-slate-50 transition-colors hover:bg-slate-50/50" onClick={() => openDetail(doc.id)}>
                    <td className="py-3 font-mono text-xs font-medium">{doc.prefix}-{String(doc.number).padStart(10, '0')}</td>
                    <td className="py-3 text-slate-600">{docTypeLabel[doc.document_type] ?? doc.document_type}</td>
                    <td className="py-3">
                      <p className="font-medium">{doc.customer_name}</p>
                      <p className="text-xs text-slate-400">{doc.customer_identification_type} {doc.customer_identification}</p>
                    </td>
                    <td className="py-3"><ErpBadge status={statusBadge[doc.dian_status]} label={statusLabel[doc.dian_status]} /></td>
                    <td className="py-3 text-right font-medium">{fmtCOP(Number(doc.total))}</td>
                    <td className="py-3 text-xs text-slate-500">{fmtDate(doc.created_at)}</td>
                    <td className="py-3 text-right">
                      <ErpBtn size="sm" variant="ghost" onClick={e => { e.stopPropagation(); openDetail(doc.id) }}>Ver</ErpBtn>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Paginación */}
        {meta.last_page > 1 && (
          <div className="mt-4 flex items-center justify-between border-t pt-3 text-xs text-slate-500">
            <span>{meta.total} documentos</span>
            <span>Página {meta.current_page} de {meta.last_page}</span>
          </div>
        )}
      </GlassCard>
    </div>
  )
}

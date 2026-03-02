import { useCallback, useEffect, useMemo, useState } from 'react'
import API from '@/lib/api'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'
import GlassCard from '@/components/ui/GlassCard'
import type { CreditAccountRow, CreditTransactionRow, PaginatedResponse } from '@/types/api'

type CreditIndexResponse = {
  status: string
  data: PaginatedResponse<CreditAccountRow>
  stats?: {
    total_accounts?: number
    total_balance?: number
    total_overdue?: number
  }
}

type CreditShowResponse = {
  status: string
  data: CreditAccountRow
  transactions: CreditTransactionRow[]
}

type AccountFilter = 'all' | 'active' | 'suspended'

const fmtCOP = (v: number) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(v)

const toNumber = (value: number | string | null | undefined) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : 0
}

const normalizeAccountStatus = (value: string | null | undefined): 'active' | 'suspended' => {
  if ((value || '').toLowerCase() === 'suspended') return 'suspended'
  return 'active'
}

const txLabel = (type: CreditTransactionRow['type']) => {
  if (type === 'charge') return 'Cargo'
  if (type === 'payment') return 'Pago'
  return 'Ajuste'
}

const txBadgeStatus = (type: CreditTransactionRow['type']): 'pending' | 'paid' | 'processing' => {
  if (type === 'charge') return 'pending'
  if (type === 'payment') return 'paid'
  return 'processing'
}

export default function DashboardCreditPage() {
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [accounts, setAccounts] = useState<CreditAccountRow[]>([])
  const [stats, setStats] = useState({
    total_accounts: 0,
    total_balance: 0,
    total_overdue: 0,
  })
  const [selectedAccount, setSelectedAccount] = useState<CreditAccountRow | null>(null)
  const [transactions, setTransactions] = useState<CreditTransactionRow[]>([])
  const [detailLoading, setDetailLoading] = useState(false)
  const [detailError, setDetailError] = useState('')
  const [formMode, setFormMode] = useState<'charge' | 'payment' | null>(null)
  const [amount, setAmount] = useState('')
  const [note, setNote] = useState('')
  const [formError, setFormError] = useState('')
  const [saving, setSaving] = useState(false)
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<AccountFilter>('all')

  const loadAccounts = useCallback(async (force = false) => {
    setLoading(true)
    setError('')

    try {
      const params = force ? { _t: Date.now() } : undefined
      const { data } = await API.get<CreditIndexResponse>('/merchant/credit', { params })
      setAccounts(data?.data?.data || [])
      setStats({
        total_accounts: Number(data?.stats?.total_accounts || 0),
        total_balance: Number(data?.stats?.total_balance || 0),
        total_overdue: Number(data?.stats?.total_overdue || 0),
      })
    } catch (err: any) {
      setError(err?.response?.data?.message || 'Error al cargar cuentas de fiado.')
      setAccounts([])
    } finally {
      setLoading(false)
    }
  }, [])

  const loadAccountDetail = useCallback(async (id: number, force = false) => {
    setDetailLoading(true)
    setDetailError('')

    try {
      const params = force ? { _t: Date.now() } : undefined
      const { data } = await API.get<CreditShowResponse>(`/merchant/credit/${id}`, { params })
      setSelectedAccount(data?.data || null)
      setTransactions(Array.isArray(data?.transactions) ? data.transactions : [])
    } catch (err: any) {
      setDetailError(err?.response?.data?.message || 'No se pudo cargar el detalle de la cuenta.')
      setTransactions([])
    } finally {
      setDetailLoading(false)
    }
  }, [])

  useEffect(() => {
    void loadAccounts()
  }, [loadAccounts])

  const selectedBalance = useMemo(() => toNumber(selectedAccount?.balance), [selectedAccount?.balance])
  const selectedLimit = useMemo(() => toNumber(selectedAccount?.credit_limit), [selectedAccount?.credit_limit])

  const filteredAccounts = useMemo(() => {
    const query = search.trim().toLowerCase()

    return accounts.filter((account) => {
      const accountStatus = normalizeAccountStatus(account.status)
      if (statusFilter !== 'all' && accountStatus !== statusFilter) return false

      if (!query) return true

      const name = account.customer?.user?.name || ''
      const email = account.customer?.user?.email || ''
      const searchable = `${name} ${email} ${account.id}`.toLowerCase()
      return searchable.includes(query)
    })
  }, [accounts, search, statusFilter])

  const openForm = (mode: 'charge' | 'payment') => {
    setFormMode(mode)
    setAmount('')
    setNote('')
    setFormError('')
  }

  const closeForm = () => {
    setFormMode(null)
    setAmount('')
    setNote('')
    setFormError('')
  }

  const submitForm = async () => {
    if (!selectedAccount || !formMode) return

    const parsed = Number(amount)
    if (!Number.isFinite(parsed) || parsed <= 0) {
      setFormError('Ingresa un monto valido.')
      return
    }

    setSaving(true)
    setFormError('')

    try {
      const endpoint = `/merchant/credit/${selectedAccount.id}/${formMode}`
      await API.post(endpoint, {
        amount: parsed,
        note: note.trim() || undefined,
      })

      closeForm()
      await Promise.all([loadAccountDetail(selectedAccount.id, true), loadAccounts(true)])
    } catch (err: any) {
      setFormError(err?.response?.data?.message || 'No se pudo guardar el movimiento.')
    } finally {
      setSaving(false)
    }
  }

  const accountFilterOptions: Array<{ value: string; label: string }> = [
    { value: 'all', label: `Todas (${accounts.length})` },
    {
      value: 'active',
      label: `Activas (${accounts.filter((account) => normalizeAccountStatus(account.status) === 'active').length})`,
    },
    {
      value: 'suspended',
      label: `Suspendidas (${accounts.filter((account) => normalizeAccountStatus(account.status) === 'suspended').length})`,
    },
  ]

  return (
    <div className="space-y-4">
      <ErpPageHeader
        breadcrumb="Dashboard / Fiado"
        title="Fiado digital"
        subtitle="Credito informal por cliente"
        actions={
          <ErpBtn variant="secondary" size="md" onClick={() => void loadAccounts(true)}>
            Recargar
          </ErpBtn>
        }
      />

      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Cuentas"
          value={stats.total_accounts}
          hint="Clientes con cupo"
          icon="users"
          iconBg="rgba(59,130,246,0.14)"
          iconColor="#3B82F6"
        />
        <ErpKpiCard
          label="Deuda total"
          value={fmtCOP(stats.total_balance)}
          hint="Saldo pendiente"
          icon="dollar"
          iconBg="rgba(255,161,79,0.14)"
          iconColor="#FFA14F"
        />
        <ErpKpiCard
          label="Sobre limite"
          value={stats.total_overdue}
          hint="Cuentas vencidas"
          icon="alert"
          iconBg="rgba(239,68,68,0.14)"
          iconColor="#EF4444"
        />
        <ErpKpiCard
          label="Deuda seleccionada"
          value={selectedAccount ? fmtCOP(selectedBalance) : fmtCOP(0)}
          hint={selectedAccount ? selectedAccount.customer?.user?.name || 'Cliente' : 'Sin cuenta seleccionada'}
          icon="credit-card"
          iconBg="rgba(16,185,129,0.14)"
          iconColor="#10B981"
        />
      </div>

      {loading && (
        <GlassCard className="rounded-2xl border border-slate-200 bg-white px-4 py-12 text-center text-[13px] text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-white/40">
          Cargando cuentas de fiado...
        </GlassCard>
      )}

      {!loading && error && (
        <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          {error}
        </div>
      )}

      {!loading && !error && (
        <GlassCard className="overflow-hidden border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] p-0 shadow-[0_20px_45px_rgba(15,23,42,0.08)]">
          <div className="border-b border-slate-200 px-4 py-4 sm:px-5 dark:border-white/10">
            <div className="flex flex-wrap items-end justify-between gap-3">
              <div>
                <h2 className="text-[28px] font-black leading-none tracking-[-0.025em] text-slate-900 sm:text-[32px] dark:text-white">Cuentas de fiado</h2>
                <p className="mt-1 text-[13px] text-slate-500 dark:text-white/50">Selecciona una cuenta para ver detalle y registrar movimientos.</p>
              </div>
            </div>

            <div className="mt-4 grid gap-2 lg:grid-cols-[minmax(0,1fr)_280px_auto]">
              <ErpSearchBar
                value={search}
                onChange={(value: string) => setSearch(value)}
                placeholder="Buscar por cliente o correo"
              />

              <ErpFilterSelect
                value={statusFilter}
                onChange={(value: string) => {
                  if (value === 'all' || value === 'active' || value === 'suspended') {
                    setStatusFilter(value)
                  }
                }}
                options={accountFilterOptions}
                placeholder="Estado"
              />

              <div className="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 dark:border-white/10 dark:bg-white/5">
                <ErpBadge status="active" label={`${stats.total_accounts - stats.total_overdue} al dia`} />
                <ErpBadge status="overdue" label={`${stats.total_overdue} vencidas`} />
              </div>
            </div>
          </div>

          {filteredAccounts.length === 0 ? (
            <div className="px-4 py-12 text-center text-[13px] text-slate-500 dark:text-white/40">
              Aun no hay cuentas de fiado para este filtro.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[820px]">
                <thead>
                  <tr className="border-b border-slate-100 bg-slate-50/70 dark:border-white/5 dark:bg-white/5">
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Cliente</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Email</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Estado</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Deuda actual</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Limite</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredAccounts.map((account) => {
                    const isSelected = selectedAccount?.id === account.id
                    const accountStatus = normalizeAccountStatus(account.status)
                    const balance = toNumber(account.balance)
                    const limit = toNumber(account.credit_limit)
                    const overLimit = balance > limit

                    return (
                      <tr
                        key={account.id}
                        className={`border-b border-slate-100 last:border-b-0 dark:border-white/5 ${
                          isSelected ? 'bg-orange-50/60 dark:bg-orange-500/10' : 'hover:bg-slate-50/60 dark:hover:bg-white/5'
                        }`}
                      >
                        <td className="px-4 py-3 text-[13px] font-semibold text-slate-900 dark:text-white">
                          {account.customer?.user?.name || 'Cliente'}
                        </td>
                        <td className="px-4 py-3 text-[13px] text-slate-600 dark:text-white/70">
                          {account.customer?.user?.email || '-'}
                        </td>
                        <td className="px-4 py-3">
                          <ErpBadge status={accountStatus === 'active' ? 'active' : 'inactive'} label={accountStatus === 'active' ? 'Activa' : 'Suspendida'} />
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex items-center gap-2">
                            <span className="text-[13px] font-semibold text-slate-900 dark:text-white">{fmtCOP(balance)}</span>
                            {overLimit && <ErpBadge status="overdue" label="Sobre limite" />}
                          </div>
                        </td>
                        <td className="px-4 py-3 text-[13px] text-slate-700 dark:text-white/70">{fmtCOP(limit)}</td>
                        <td className="px-4 py-3">
                          <ErpBtn variant="secondary" size="sm" onClick={() => void loadAccountDetail(account.id, true)}>
                            Ver detalle
                          </ErpBtn>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
            </div>
          )}
        </GlassCard>
      )}

      {selectedAccount && (
        <GlassCard className="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 className="text-[22px] font-black text-slate-900 dark:text-white">
                {selectedAccount.customer?.user?.name || 'Cliente'}
              </h2>
              <p className="text-[12px] text-slate-500 dark:text-white/50">{selectedAccount.customer?.user?.email || '-'}</p>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <ErpBadge
                status={normalizeAccountStatus(selectedAccount.status) === 'active' ? 'active' : 'inactive'}
                label={normalizeAccountStatus(selectedAccount.status) === 'active' ? 'Cuenta activa' : 'Cuenta suspendida'}
              />
              <ErpBadge status={selectedBalance > selectedLimit ? 'overdue' : 'ok'} label={`Deuda ${fmtCOP(selectedBalance)}`} />
              <ErpBadge status="regular" label={`Limite ${fmtCOP(selectedLimit)}`} />
            </div>
          </div>

          <div className="flex flex-wrap gap-2">
            <ErpBtn variant="secondary" size="sm" onClick={() => openForm('charge')}>
              Registrar cargo
            </ErpBtn>
            <ErpBtn variant="success" size="sm" onClick={() => openForm('payment')}>
              Registrar pago
            </ErpBtn>
          </div>

          {formMode && (
            <div className="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/5">
              <p className="text-[13px] font-semibold text-slate-800 dark:text-white">
                {formMode === 'charge' ? 'Nuevo cargo' : 'Nuevo pago'}
              </p>

              <div className="grid gap-3 sm:grid-cols-2">
                <div>
                  <label className="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-white/40">
                    Monto COP
                  </label>
                  <input
                    type="number"
                    min={0.01}
                    step="0.01"
                    value={amount}
                    onChange={(event) => setAmount(event.target.value)}
                    className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 dark:border-white/20 dark:bg-white/5 dark:text-white"
                  />
                </div>

                <div>
                  <label className="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-white/40">
                    Nota
                  </label>
                  <textarea
                    value={note}
                    onChange={(event) => setNote(event.target.value)}
                    rows={2}
                    className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-[13px] text-slate-900 outline-none focus:border-orange-400 dark:border-white/20 dark:bg-white/5 dark:text-white"
                  />
                </div>
              </div>

              {formError && (
                <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                  {formError}
                </div>
              )}

              <div className="flex items-center gap-2">
                <ErpBtn variant="primary" size="sm" onClick={() => void submitForm()} disabled={saving}>
                  {saving ? 'Guardando...' : 'Guardar'}
                </ErpBtn>
                <ErpBtn variant="ghost" size="sm" onClick={closeForm} disabled={saving}>
                  Cancelar
                </ErpBtn>
              </div>
            </div>
          )}

          {detailLoading && <p className="text-[13px] text-slate-500 dark:text-white/40">Cargando transacciones...</p>}

          {!detailLoading && detailError && (
            <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[12px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
              {detailError}
            </div>
          )}

          {!detailLoading && !detailError && (
            <div className="overflow-hidden rounded-xl border border-slate-200 dark:border-white/10">
              {transactions.length === 0 ? (
                <div className="px-4 py-6 text-[13px] text-slate-500 dark:text-white/40">
                  Esta cuenta aun no tiene transacciones.
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[720px]">
                    <thead>
                      <tr className="border-b border-slate-100 bg-slate-50/60 dark:border-white/5 dark:bg-white/5">
                        <th className="px-4 py-2 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Fecha</th>
                        <th className="px-4 py-2 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Tipo</th>
                        <th className="px-4 py-2 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Monto</th>
                        <th className="px-4 py-2 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Saldo despues</th>
                        <th className="px-4 py-2 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Nota</th>
                      </tr>
                    </thead>
                    <tbody>
                      {transactions.map((transaction) => (
                        <tr key={transaction.id} className="border-b border-slate-100 last:border-b-0 dark:border-white/5">
                          <td className="px-4 py-2.5 text-[12px] text-slate-600 dark:text-white/70">
                            {new Date(transaction.created_at).toLocaleString('es-CO')}
                          </td>
                          <td className="px-4 py-2.5">
                            <ErpBadge status={txBadgeStatus(transaction.type)} label={txLabel(transaction.type)} />
                          </td>
                          <td className="px-4 py-2.5 text-[12px] font-semibold text-slate-900 dark:text-white">
                            {fmtCOP(toNumber(transaction.amount))}
                          </td>
                          <td className="px-4 py-2.5 text-[12px] font-semibold text-slate-900 dark:text-white">
                            {fmtCOP(toNumber(transaction.balance_after))}
                          </td>
                          <td className="px-4 py-2.5 text-[12px] text-slate-600 dark:text-white/70">
                            {transaction.note || '-'}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          )}
        </GlassCard>
      )}
    </div>
  )
}
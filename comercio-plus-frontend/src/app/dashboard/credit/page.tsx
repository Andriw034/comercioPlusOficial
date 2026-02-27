import { useCallback, useEffect, useMemo, useState } from 'react'
import API from '@/lib/api'
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

  const selectedBalance = useMemo(
    () => toNumber(selectedAccount?.balance),
    [selectedAccount?.balance],
  )

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
      setFormError('Ingresa un monto válido.')
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

  return (
    <div className="space-y-5">
      <div>
        <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
        <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Fiado digital</h1>
        <p className="text-[12px] text-slate-500 dark:text-white/40">Credito informal por cliente</p>
      </div>

      <div className="grid gap-3 sm:grid-cols-3">
        <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
          <p className="text-[11px] uppercase tracking-wide text-slate-500 dark:text-white/40">Cuentas</p>
          <p className="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{stats.total_accounts}</p>
        </div>
        <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
          <p className="text-[11px] uppercase tracking-wide text-slate-500 dark:text-white/40">Deuda total</p>
          <p className="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{fmtCOP(stats.total_balance)}</p>
        </div>
        <div className="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
          <p className="text-[11px] uppercase tracking-wide text-slate-500 dark:text-white/40">Sobre limite</p>
          <p className="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{stats.total_overdue}</p>
        </div>
      </div>

      {loading && (
        <div className="rounded-2xl border border-slate-200 bg-white px-4 py-12 text-center text-[13px] text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-white/40">
          Cargando cuentas de fiado...
        </div>
      )}

      {!loading && error && (
        <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          {error}
        </div>
      )}

      {!loading && !error && (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/5">
          {accounts.length === 0 ? (
            <div className="px-4 py-12 text-center text-[13px] text-slate-500 dark:text-white/40">
              Aun no hay cuentas de fiado creadas.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[760px]">
                <thead>
                  <tr className="border-b border-slate-100 bg-slate-50/60 dark:border-white/5 dark:bg-white/5">
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Cliente</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Email</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Deuda actual</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Limite</th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {accounts.map((account) => {
                    const isSelected = selectedAccount?.id === account.id
                    return (
                      <tr
                        key={account.id}
                        className={`border-b border-slate-100 last:border-b-0 dark:border-white/5 ${
                          isSelected ? 'bg-orange-50/60 dark:bg-orange-500/10' : ''
                        }`}
                      >
                        <td className="px-4 py-3 text-[13px] font-semibold text-slate-900 dark:text-white">
                          {account.customer?.user?.name || 'Cliente'}
                        </td>
                        <td className="px-4 py-3 text-[13px] text-slate-600 dark:text-white/70">
                          {account.customer?.user?.email || '-'}
                        </td>
                        <td className="px-4 py-3 text-[13px] font-semibold text-slate-900 dark:text-white">
                          {fmtCOP(toNumber(account.balance))}
                        </td>
                        <td className="px-4 py-3 text-[13px] text-slate-700 dark:text-white/70">
                          {fmtCOP(toNumber(account.credit_limit))}
                        </td>
                        <td className="px-4 py-3">
                          <button
                            type="button"
                            onClick={() => void loadAccountDetail(account.id, true)}
                            className="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-white/20 dark:bg-transparent dark:text-white/80 dark:hover:bg-white/10"
                          >
                            Ver detalle
                          </button>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}

      {selectedAccount && (
        <div className="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 className="text-[18px] font-bold text-slate-900 dark:text-white">
                {selectedAccount.customer?.user?.name || 'Cliente'}
              </h2>
              <p className="text-[12px] text-slate-500 dark:text-white/50">
                Deuda actual: {fmtCOP(selectedBalance)}
              </p>
            </div>
            <div className="flex flex-wrap gap-2">
              <button
                type="button"
                onClick={() => openForm('charge')}
                className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[12px] font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300"
              >
                Registrar cargo
              </button>
              <button
                type="button"
                onClick={() => openForm('payment')}
                className="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[12px] font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
              >
                Registrar pago
              </button>
            </div>
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

              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => void submitForm()}
                  disabled={saving}
                  className="rounded-lg bg-orange-500 px-3 py-1.5 text-[12px] font-semibold text-white transition hover:bg-orange-600 disabled:opacity-50"
                >
                  {saving ? 'Guardando...' : 'Guardar'}
                </button>
                <button
                  type="button"
                  onClick={closeForm}
                  disabled={saving}
                  className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[12px] font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50 dark:border-white/20 dark:bg-transparent dark:text-white/70 dark:hover:bg-white/10"
                >
                  Cancelar
                </button>
              </div>
            </div>
          )}

          {detailLoading && (
            <p className="text-[13px] text-slate-500 dark:text-white/40">Cargando transacciones...</p>
          )}

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
                  <table className="w-full min-w-[620px]">
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
                            <span
                              className={`inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold ${
                                transaction.type === 'charge'
                                  ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300'
                                  : transaction.type === 'payment'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
                                    : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-white/20 dark:bg-white/10 dark:text-white/70'
                              }`}
                            >
                              {transaction.type}
                            </span>
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
        </div>
      )}
    </div>
  )
}

import { useEffect, useState } from 'react'
import API from '@/lib/api'
import { formatDate } from '@/lib/format'
import type { CustomerRow, PaginatedResponse } from '@/types/api'

type ApiResponse = {
  status: string
  data: PaginatedResponse<CustomerRow>
}

const fmtCurrency = (value: number) =>
  new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(value)

const initialsFromName = (name: string) => {
  const clean = name.trim()
  if (!clean) return 'C'
  return clean.charAt(0).toUpperCase()
}

export default function DashboardCustomers() {
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [rows, setRows] = useState<CustomerRow[]>([])

  useEffect(() => {
    const fetchCustomers = async () => {
      try {
        setLoading(true)
        setError('')
        const { data } = await API.get<ApiResponse>('/merchant/customers')
        setRows(data?.data?.data || [])
      } catch (err: any) {
        setError(err?.response?.data?.message || 'Error al cargar clientes')
      } finally {
        setLoading(false)
      }
    }

    void fetchCustomers()
  }, [])

  const escapeCsv = (value: string | number | null | undefined) => {
    const raw = String(value ?? '')
    const escaped = raw.replace(/"/g, '""')
    return `"${escaped}"`
  }

  const exportCsv = () => {
    if (!rows.length) return

    const headers = ['Cliente', 'Email', 'Pedidos', 'Total gastado', 'Primera visita']
    const records = rows.map((item) => [
      item.user?.name || 'Cliente',
      item.user?.email || '',
      Number(item.total_orders || 0),
      Number(item.total_spent || 0),
      formatDate(item.first_visited_at),
    ])

    const csvContent = [
      headers.map((header) => escapeCsv(header)).join(','),
      ...records.map((record) => record.map((cell) => escapeCsv(cell)).join(',')),
    ].join('\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const timestamp = new Date().toISOString().slice(0, 10)
    const link = document.createElement('a')
    link.href = url
    link.download = `clientes-${timestamp}.csv`
    document.body.appendChild(link)
    link.click()
    link.remove()
    URL.revokeObjectURL(url)
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <p className="text-[13px] text-slate-500 dark:text-white/50">Dashboard</p>
          <h1 className="font-display text-[32px] font-bold text-slate-950 dark:text-white">Clientes</h1>
          <p className="text-[12px] text-slate-500 dark:text-white/40">Base de compradores</p>
        </div>

        <button
          type="button"
          onClick={exportCsv}
          disabled={!rows.length}
          className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[12px] font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white/70"
        >
          <span aria-hidden>📊</span>
          Exportar
        </button>
      </div>

      {loading && (
        <div className="rounded-2xl border border-slate-200 bg-white px-4 py-12 text-center text-[13px] text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-white/40">
          Cargando clientes...
        </div>
      )}

      {!loading && error && (
        <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
          {error}
        </div>
      )}

      {!loading && !error && (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.06)] dark:border-white/10 dark:bg-white/5">
          {rows.length === 0 ? (
            <div className="px-4 py-12 text-center text-[13px] text-slate-500 dark:text-white/40">
              Aun no tienes clientes registrados.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[760px]">
                <thead>
                  <tr className="border-b border-slate-100 bg-slate-50/60 dark:border-white/5 dark:bg-white/5">
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Cliente
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Pedidos
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Total gastado
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Estado
                    </th>
                  </tr>
                </thead>

                <tbody>
                  {rows.map((item) => {
                    const name = item.user?.name || 'Cliente'
                    const email = item.user?.email || '-'
                    const totalOrders = Number(item.total_orders || 0)
                    const totalSpent = Number(item.total_spent || 0)
                    const isActive = totalOrders > 0

                    return (
                      <tr key={item.id} className="border-b border-slate-100 last:border-b-0 dark:border-white/5">
                        <td className="px-4 py-3.5">
                          <div className="flex items-center gap-2.5">
                            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-[11px] font-bold text-white">
                              {initialsFromName(name)}
                            </div>
                            <div className="min-w-0">
                              <p className="truncate text-[13px] font-semibold text-slate-900 dark:text-white">{name}</p>
                              <p className="truncate text-[11px] text-slate-400 dark:text-white/30">{email}</p>
                            </div>
                          </div>
                        </td>

                        <td className="px-4 py-3.5 text-[13px] font-semibold text-slate-800 dark:text-white/80">
                          {totalOrders}
                        </td>

                        <td className="px-4 py-3.5 text-[13px] font-semibold text-slate-900 dark:text-white">
                          {fmtCurrency(totalSpent)}
                        </td>

                        <td className="px-4 py-3.5">
                          <span
                            className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold ${
                              isActive
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
                                : 'border-slate-200 bg-slate-100 text-slate-600 dark:border-white/20 dark:bg-white/10 dark:text-white/60'
                            }`}
                            title={`Primera visita: ${formatDate(item.first_visited_at)}`}
                          >
                            {isActive ? 'Activo' : 'Inactivo'}
                          </span>
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
    </div>
  )
}

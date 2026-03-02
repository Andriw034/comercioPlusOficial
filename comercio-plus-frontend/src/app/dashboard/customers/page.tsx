import { useEffect, useMemo, useState } from 'react'
import API from '@/lib/api'
import { formatDate } from '@/lib/format'
import { ErpBadge, ErpBtn, ErpFilterSelect, ErpKpiCard, ErpPageHeader, ErpSearchBar } from '@/components/erp'
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
  const [search, setSearch] = useState('')
  const [statusFilter, setStatusFilter] = useState<'all' | 'active' | 'inactive'>('all')

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

  const totalCustomers = rows.length
  const activeCustomers = rows.filter((item) => Number(item.total_orders || 0) > 0).length
  const totalOrders = rows.reduce((sum, item) => sum + Number(item.total_orders || 0), 0)
  const totalSpent = rows.reduce((sum, item) => sum + Number(item.total_spent || 0), 0)

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase()

    return rows.filter((item) => {
      const totalOrdersByCustomer = Number(item.total_orders || 0)
      const isActive = totalOrdersByCustomer > 0

      if (statusFilter === 'active' && !isActive) return false
      if (statusFilter === 'inactive' && isActive) return false

      if (!query) return true

      const name = item.user?.name || ''
      const email = item.user?.email || ''
      const searchable = `${name} ${email} ${item.id}`.toLowerCase()
      return searchable.includes(query)
    })
  }, [rows, search, statusFilter])

  const statusFilterOptions: Array<{ value: string; label: string }> = [
    { value: 'all', label: `Todos (${totalCustomers})` },
    { value: 'active', label: `Activos (${activeCustomers})` },
    { value: 'inactive', label: `Inactivos (${Math.max(totalCustomers - activeCustomers, 0)})` },
  ]

  return (
    <div className="space-y-4">
      <ErpPageHeader
        breadcrumb="Dashboard / Clientes"
        title="Clientes ERP"
        subtitle="Base de compradores y comportamiento de compra."
        actions={
          <ErpBtn variant="primary" size="md" onClick={exportCsv} disabled={!rows.length}>
            Exportar CSV
          </ErpBtn>
        }
      />

      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <ErpKpiCard
          label="Clientes"
          value={totalCustomers}
          hint="Registros totales"
          icon="users"
          iconBg="rgba(59,130,246,0.14)"
          iconColor="#3B82F6"
        />
        <ErpKpiCard
          label="Activos"
          value={activeCustomers}
          hint="Con al menos un pedido"
          icon="check-circle"
          iconBg="rgba(16,185,129,0.14)"
          iconColor="#10B981"
        />
        <ErpKpiCard
          label="Pedidos"
          value={totalOrders}
          hint="Total acumulado"
          icon="file-text"
          iconBg="rgba(245,158,11,0.14)"
          iconColor="#F59E0B"
        />
        <ErpKpiCard
          label="Facturacion"
          value={fmtCurrency(totalSpent)}
          hint="Gasto total de clientes"
          icon="dollar"
          iconBg="rgba(255,161,79,0.16)"
          iconColor="#FFA14F"
        />
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
        <div className="overflow-hidden rounded-2xl border border-[#DDE3EF] bg-[linear-gradient(145deg,#FFFFFF_0%,#F8FAFC_100%)] shadow-[0_20px_45px_rgba(15,23,42,0.08)]">
          <div className="border-b border-slate-200 px-4 py-4 sm:px-5">
            <div className="flex flex-wrap items-end justify-between gap-3">
              <div>
                <h2 className="text-[28px] font-black leading-none tracking-[-0.025em] text-slate-900 sm:text-[32px]">
                  Directorio de clientes
                </h2>
                <p className="mt-1 text-[13px] text-slate-500">Explora, filtra y exporta tu base de compradores.</p>
              </div>
            </div>

            <div className="mt-4 grid gap-2 lg:grid-cols-[minmax(0,1fr)_280px_auto]">
              <ErpSearchBar
                value={search}
                onChange={(value: string) => setSearch(value)}
                placeholder="Buscar por nombre, correo o ID"
              />

              <ErpFilterSelect
                value={statusFilter}
                onChange={(value: string) => {
                  if (value === 'all' || value === 'active' || value === 'inactive') {
                    setStatusFilter(value)
                  }
                }}
                options={statusFilterOptions}
                placeholder="Estado"
              />

              <div className="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5">
                <ErpBadge status="active" label={`${activeCustomers} activos`} />
                <ErpBadge status="inactive" label={`${Math.max(totalCustomers - activeCustomers, 0)} inactivos`} />
              </div>
            </div>
          </div>

          {filteredRows.length === 0 ? (
            <div className="px-4 py-12 text-center text-[13px] text-slate-500 dark:text-white/40">
              Aun no tienes clientes registrados.
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[860px]">
                <thead>
                  <tr className="border-b border-slate-100 bg-slate-50/70 dark:border-white/5 dark:bg-white/5">
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Cliente
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Email
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Pedidos
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Total gastado
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Primera visita
                    </th>
                    <th className="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-[0.11em] text-slate-400 dark:text-white/30">
                      Estado
                    </th>
                  </tr>
                </thead>

                <tbody>
                  {filteredRows.map((item) => {
                    const name = item.user?.name || 'Cliente'
                    const email = item.user?.email || '-'
                    const totalOrdersByCustomer = Number(item.total_orders || 0)
                    const totalSpentByCustomer = Number(item.total_spent || 0)
                    const isActive = totalOrdersByCustomer > 0

                    return (
                      <tr key={item.id} className="border-b border-slate-100 last:border-b-0 hover:bg-slate-50/70 dark:border-white/5">
                        <td className="px-4 py-3.5">
                          <div className="flex items-center gap-2.5">
                            <div className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-[11px] font-bold text-white">
                              {initialsFromName(name)}
                            </div>
                            <div className="min-w-0">
                              <p className="truncate text-[13px] font-semibold text-slate-900 dark:text-white">{name}</p>
                            </div>
                          </div>
                        </td>

                        <td className="px-4 py-3.5 text-[13px] text-slate-600 dark:text-white/70">{email}</td>

                        <td className="px-4 py-3.5 text-[13px] font-semibold text-slate-800 dark:text-white/80">
                          {totalOrdersByCustomer}
                        </td>

                        <td className="px-4 py-3.5 text-[13px] font-semibold text-slate-900 dark:text-white">
                          {fmtCurrency(totalSpentByCustomer)}
                        </td>

                        <td className="px-4 py-3.5 text-[12px] text-slate-600 dark:text-white/70">
                          {formatDate(item.first_visited_at)}
                        </td>

                        <td className="px-4 py-3.5">
                          <ErpBadge status={isActive ? 'active' : 'inactive'} label={isActive ? 'Activo' : 'Inactivo'} />
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
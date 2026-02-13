import { useEffect, useState } from 'react'
import API from '@/lib/api'
import { formatDate } from '@/lib/format'
import type { CustomerRow, MerchantCustomersStats, PaginatedResponse } from '@/types/api'

type ApiResponse = {
  status: string
  data: PaginatedResponse<CustomerRow>
  stats: MerchantCustomersStats
}

export default function DashboardCustomers() {
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [rows, setRows] = useState<CustomerRow[]>([])
  const [stats, setStats] = useState<MerchantCustomersStats>({
    total_customers: 0,
    new_this_month: 0,
    with_orders: 0,
    total_revenue: 0,
  })

  useEffect(() => {
    const fetchCustomers = async () => {
      try {
        setLoading(true)
        setError('')
        const { data } = await API.get<ApiResponse>('/merchant/customers')
        setRows(data?.data?.data || [])
        setStats(
          data?.stats || {
            total_customers: 0,
            new_this_month: 0,
            with_orders: 0,
            total_revenue: 0,
          },
        )
      } catch (err: any) {
        setError(err?.response?.data?.message || 'Error al cargar clientes')
      } finally {
        setLoading(false)
      }
    }

    fetchCustomers()
  }, [])

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between gap-4">
        <h1 className="font-display text-[32px]">Mis Clientes</h1>
        <button className="inline-flex items-center justify-center rounded-[8px] border-2 border-[#D1D5DB] px-6 py-3 text-[15px] font-medium text-[#1F2937]">
          Exportar Datos
        </button>
      </div>

      <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div className="rounded-xl border border-[#E5E7EB] bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
          <p className="text-[36px] font-bold text-[#1A1A2E]">{stats.total_customers}</p>
          <p className="text-[13px] uppercase tracking-[0.5px] text-[#4B5563]">Total Clientes</p>
        </div>
        <div className="rounded-xl border border-[#E5E7EB] bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
          <p className="text-[36px] font-bold text-[#1A1A2E]">{stats.new_this_month}</p>
          <p className="text-[13px] uppercase tracking-[0.5px] text-[#4B5563]">Nuevos (30 dias)</p>
        </div>
        <div className="rounded-xl border border-[#E5E7EB] bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
          <p className="text-[36px] font-bold text-[#1A1A2E]">{stats.with_orders}</p>
          <p className="text-[13px] uppercase tracking-[0.5px] text-[#4B5563]">Con Pedidos</p>
        </div>
        <div className="rounded-xl bg-[linear-gradient(135deg,#FF6B35_0%,#E65A2B_100%)] p-6 shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
          <p className="text-[36px] font-bold text-white">${Number(stats.total_revenue || 0).toLocaleString('es-CO')}</p>
          <p className="text-[13px] uppercase tracking-[0.5px] text-white/90">Ingresos Totales</p>
        </div>
      </div>

      {loading && <p className="text-[15px] text-[#4B5563]">Cargando clientes...</p>}
      {!loading && error && <p className="text-[15px] text-red-600">{error}</p>}

      {!loading && !error && (
        <div className="overflow-x-auto rounded-xl border border-[#E5E7EB] bg-white shadow-[0_2px_8px_rgba(0,0,0,0.05)]">
          {rows.length === 0 ? (
            <div className="p-6 text-[14px] text-[#4B5563]">Aun no tienes clientes registrados.</div>
          ) : (
            <table className="w-full min-w-[980px] text-left">
              <thead className="bg-[#F9FAFB]">
                <tr className="text-[13px] uppercase tracking-[0.5px] text-[#4B5563]">
                  <th className="px-6 py-4 font-semibold">Cliente</th>
                  <th className="px-6 py-4 font-semibold">Email</th>
                  <th className="px-6 py-4 font-semibold">Pedidos</th>
                  <th className="px-6 py-4 font-semibold">Total Gastado</th>
                  <th className="px-6 py-4 font-semibold">Estado</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((item) => {
                  const isActive = Number(item.total_orders || 0) > 0
                  return (
                    <tr key={item.id} className="border-t border-[#E5E7EB] text-[14px]">
                      <td className="px-6 py-5 font-semibold text-[#1F2937]">{item.user?.name || 'Cliente'}</td>
                      <td className="px-6 py-5 text-[#4B5563]">{item.user?.email || '-'}</td>
                      <td className="px-6 py-5 text-[#1F2937]">{item.total_orders}</td>
                      <td className="px-6 py-5 font-semibold text-[#1F2937]">
                        ${Number(item.total_spent || 0).toLocaleString('es-CO')}
                      </td>
                      <td className="px-6 py-5">
                        <span
                          className={`inline-flex rounded-full px-3 py-1 text-[12px] font-medium ${
                            isActive
                              ? 'bg-[rgba(6,214,160,0.1)] text-[#06D6A0]'
                              : 'bg-[rgba(255,166,43,0.1)] text-[#FFA62B]'
                          }`}
                          title={`Primera visita: ${formatDate(item.first_visited_at)}`}
                        >
                          {isActive ? 'Activo' : 'Nuevo'}
                        </span>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          )}
        </div>
      )}
    </div>
  )
}

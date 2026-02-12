@extends('layouts.dashboard')
@section('title','Estadísticas — ComercioPlus')

@push('styles')
<style>
  /* Tarjetas limpias, minimalistas */
  .kpi-card { @apply p-4 rounded-xl border bg-white shadow-sm; }
  .kpi-label { @apply text-sm text-gray-500; }
  .kpi-value { @apply text-3xl font-semibold; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto p-6">
  <header class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-bold">Estadísticas</h1>
    <p class="text-gray-600">Resumen de pedidos, ventas e ingresos.</p>
  </header>

  {{-- Filtros simples --}}
  <section class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Desde</label>
      <input id="f_desde" type="date" class="w-full rounded-lg border-gray-300" value="{{ now()->subDays(30)->toDateString() }}">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Hasta</label>
      <input id="f_hasta" type="date" class="w-full rounded-lg border-gray-300" value="{{ now()->toDateString() }}">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Tienda (opcional)</label>
      <input id="f_store" type="number" class="w-full rounded-lg border-gray-300" placeholder="ID de tienda">
    </div>
    <div class="sm:col-span-3">
      <button id="btnAplicar" class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white transition">
        Aplicar filtros
      </button>
    </div>
  </section>

  {{-- KPIs --}}
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="cards">
    <div class="kpi-card">
      <p class="kpi-label">Pedidos</p>
      <p class="kpi-value" id="kpiPedidos">—</p>
    </div>
    <div class="kpi-card">
      <p class="kpi-label">Ventas pagadas</p>
      <p class="kpi-value" id="kpiPagadas">—</p>
    </div>
    <div class="kpi-card">
      <p class="kpi-label">Ingresos</p>
      <p class="kpi-value" id="kpiIngresos">$—</p>
    </div>
    <div class="kpi-card">
      <p class="kpi-label">Ticket promedio</p>
      <p class="kpi-value" id="kpiTicket">$—</p>
    </div>
  </section>

  {{-- Serie temporal --}}
  <section class="mt-10">
    <h2 class="text-lg font-semibold mb-3">Ventas diarias</h2>
    <canvas id="chartVentas" height="100"></canvas>
  </section>

  {{-- Top productos --}}
  <section class="mt-10">
    <h2 class="text-lg font-semibold mb-3">Top productos</h2>
    <div id="topProductos" class="space-y-2"></div>
  </section>

  <p id="msgError" class="mt-6 text-red-600 font-medium hidden"></p>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  let chart; // referencia al gráfico para poder actualizarlo

  function moneyCOP(v){
    try {
      return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP' }).format(v ?? 0);
    } catch (e) {
      return '$' + (v ?? 0);
    }
  }

  function getParams(){
    const from = document.getElementById('f_desde').value;
    const to   = document.getElementById('f_hasta').value;
    const storeRaw = document.getElementById('f_store').value;
    const store_id = storeRaw ? parseInt(storeRaw, 10) : undefined;
    return { from, to, ...(store_id ? { store_id } : {}) };
  }

  async function cargar(){
    const msg = document.getElementById('msgError');
    msg.classList.add('hidden'); msg.textContent = '';

    const params = getParams();

    try {
      const [sumRes, tsRes, topRes] = await Promise.all([
        axios.get('/api/stats/summary',   { params }),
        axios.get('/api/stats/timeseries',{ params }),
        axios.get('/api/stats/top-products',{ params: { ...params, limit: 5 } }),
      ]);

      // KPIs
      const s = sumRes.data || {};
      document.getElementById('kpiPedidos').textContent  = s.total_pedidos ?? '0';
      document.getElementById('kpiPagadas').textContent  = s.ventas_pagadas ?? '0';
      document.getElementById('kpiIngresos').textContent = moneyCOP(s.ingresos);
      document.getElementById('kpiTicket').textContent   = moneyCOP(s.ticket_promedio);

      // Serie temporal
      const series = tsRes.data || [];
      const labels = series.map(i => i.dia);
      const data   = series.map(i => i.ingresos);
      const ctx = document.getElementById('chartVentas').getContext('2d');

      if (chart) chart.destroy();
      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [{ label: 'Ingresos', data, tension: 0.25, fill: false }]
        },
        options: {
          responsive: true,
          plugins: {
            tooltip: {
              callbacks: {
                label: (item) => ' ' + moneyCOP(item.raw)
              }
            }
          },
          scales: {
            y: {
              ticks: {
                callback: (v) => moneyCOP(v)
              }
            }
          }
        }
      });

      // Top productos
      const top = topRes.data || [];
      const cont = document.getElementById('topProductos');
      cont.innerHTML = top.length
        ? top.map(p => `
          <div class="flex items-center justify-between p-3 rounded-lg border bg-white">
            <div class="font-medium">Producto #${p.product_id}</div>
            <div class="text-sm text-gray-600">${p.unidades} uds • ${moneyCOP(p.total)}</div>
          </div>
        `).join('')
        : '<p class="text-gray-600">Sin datos en el rango seleccionado.</p>';

    } catch (e) {
      console.error(e);
      msg.textContent = 'No fue posible cargar las estadísticas. Verifica que los endpoints /api/stats estén activos y tu sesión esté autenticada.';
      msg.classList.remove('hidden');
    }
  }

  document.getElementById('btnAplicar').addEventListener('click', cargar);
  // carga inicial
  document.addEventListener('DOMContentLoaded', cargar);
})();
</script>
@endpush

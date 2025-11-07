@extends('layouts.dashboard')

@section('title', 'Analítica (PostHog)')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <a href="{{ route('admin.settings.index') }}" class="text-sm text-slate-400 hover:text-white transition">&larr; Volver a configuración</a>
        <h1 class="mt-2 text-3xl font-semibold text-white">Analítica (PostHog)</h1>
        <p class="text-slate-400">Resumen de eventos capturados desde la demo y acciones clave de tus usuarios.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-[#121b27] p-5 shadow-xl shadow-black/30">
            <p class="text-sm text-slate-400">Eventos últimas 24h</p>
            <p class="mt-2 text-4xl font-bold text-white">{{ number_format($total24h) }}</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[#121b27] p-5 shadow-xl shadow-black/30">
            <p class="text-sm text-slate-400">Favoritos guardados (7 días)</p>
            <p class="mt-2 text-4xl font-bold text-white">{{ number_format($fav7d) }}</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-[#121b27] p-5 shadow-xl shadow-black/30">
            <p class="text-sm text-slate-400">Estado</p>
            <span class="mt-3 inline-flex items-center gap-2 rounded-xl bg-[#ff8a3d]/90 px-3 py-1 text-sm font-semibold text-slate-900">
                <span class="h-2 w-2 rounded-full bg-slate-900"></span>
                {{ collect($apiErrors)->filter()->isEmpty() ? 'Conectado' : 'Con errores' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-[#121b27] p-5 shadow-xl shadow-black/30">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Likes por día (7 días)</h2>
            </div>
            <canvas id="chartLikes" class="mt-6 h-64 w-full"></canvas>
        </div>

        <div class="rounded-2xl border border-white/10 bg-[#121b27] p-5 shadow-xl shadow-black/30">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Búsquedas por día (7 días)</h2>
            </div>
            <canvas id="chartSearch" class="mt-6 h-64 w-full"></canvas>
        </div>
    </div>

    @php
        $hasErrors = collect($apiErrors)->filter()->isNotEmpty();
    @endphp
    @if($hasErrors)
        <div class="rounded-2xl border border-rose-400/40 bg-rose-500/10 p-4 text-rose-100">
            <p class="font-semibold mb-2">Aviso</p>
            <pre class="text-xs whitespace-pre-wrap">{{ json_encode($apiErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const likesCtx = document.getElementById('chartLikes');
        const searchCtx = document.getElementById('chartSearch');

        const likesLabels = @json($likesLabels);
        const likesData   = @json($likesData);
        const searchLabels = @json($searchLabels);
        const searchData   = @json($searchData);

        const baseOptions = {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#cfd8e3' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)' },
                    ticks: { color: '#cfd8e3', precision: 0 }
                }
            }
        };

        if (likesCtx) {
            new Chart(likesCtx, {
                type: 'line',
                data: {
                    labels: likesLabels,
                    datasets: [{
                        data: likesData,
                        borderColor: '#ff8a3d',
                        backgroundColor: 'rgba(255,138,61,0.15)',
                        fill: true,
                        borderWidth: 3,
                        tension: 0.35
                    }]
                },
                options: baseOptions
            });
        }

        if (searchCtx) {
            new Chart(searchCtx, {
                type: 'bar',
                data: {
                    labels: searchLabels,
                    datasets: [{
                        data: searchData,
                        backgroundColor: '#ff6000',
                        borderRadius: 8
                    }]
                },
                options: baseOptions
            });
        }
    })();
</script>
@endpush

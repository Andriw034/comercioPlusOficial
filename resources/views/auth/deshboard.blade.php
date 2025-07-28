@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="container  max-w-7xl mx-auto px-6 py-12">
        {{-- Banner de bienvenida con fondo degradado --}}
        <div
            class="bg-gradient-to-r from-gray-700 via-gray-500 to-yellow-200 rounded-xl shadow-xl p-10 text-white mb-12 text-center">
            <h1 class="text-5xl font-extrabold mb-3 drop-shadow-lg animate-fadeIn">
                ¡Bienvenido, {{ Auth::user()->name }}! 👋
            </h1>
            <p class="text-xl max-w-3xl mx-auto drop-shadow-sm animate-fadeIn delay-200">
                Tu panel de control centralizado para gestionar productos, usuarios y monitorear todas las métricas clave de
                tu negocio en tiempo real.
            </p>
        </div>



        {{-- Info rápida --}}
        <div class="bg-white rounded-xl shadow-lg p-8 mb-12 transition-colors duration-500">
            <h2 class="text-2xl font-semibold mb-5 text-gray-800 border-b border-gray-200 pb-3">
                ¿Qué puedes hacer aquí?
            </h2>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700 text-lg">
                <li class="flex items-start space-x-3">
                    <span class="text-indigo-600 text-3xl">📦</span>
                    <span><strong>Gestionar productos:</strong> crea, edita o elimina productos con facilidad para mantener
                        tu inventario al día.</span>
                </li>
                <li class="flex items-start space-x-3">
                    <span class="text-green-600 text-3xl">📈</span>
                    <span><strong>Visualizar estadísticas:</strong> monitorea usuarios activos, stock y ventas del día con
                        métricas claras y actualizadas.</span>
                </li>
                <li class="flex items-start space-x-3">
                    <span class="text-pink-600 text-3xl">👥</span>
                    <span><strong>Administrar usuarios:</strong> controla accesos y permisos para mantener la seguridad de
                        tu sistema.</span>
                </li>
                <li class="flex items-start space-x-3">
                    <span class="text-yellow-500 text-3xl">🔒</span>
                    <span><strong>Seguridad:</strong> autenticación robusta para proteger tu información y la de tus
                        clientes.</span>
                </li>
            </ul>
        </div>

        {{-- Métricas con estilo moderno --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            @php
                $metrics = [
                    [
                        'title' => 'Usuarios Activos',
                        'value' => 154,
                        'icon' => '👥',
                        'color' => 'text-indigo-600 bg-indigo-100',
                        'growth' => 12,
                    ],
                    [
                        'title' => 'Productos en Stock',
                        'value' => 320,
                        'icon' => '📦',
                        'color' => 'text-green-600 bg-green-100',
                        'growth' => -3,
                    ],
                    [
                        'title' => 'Ventas del Día',
                        'value' => 25,
                        'icon' => '💰',
                        'color' => 'text-pink-600 bg-pink-100',
                        'growth' => 8,
                    ],
                    [
                        'title' => 'Pedidos Pendientes',
                        'value' => 8,
                        'icon' => '📋',
                        'color' => 'text-yellow-500 bg-yellow-100',
                        'growth' => 0,
                    ],
                ];
            @endphp

            @foreach ($metrics as $metric)
                <div
                    class="flex flex-col items-center bg-white rounded-2xl shadow-lg p-8 cursor-pointer transform transition hover:scale-105 hover:shadow-xl animate-fadeIn delay-100">
                    <div
                        class="w-16 h-16 flex items-center justify-center rounded-full mb-4 {{ $metric['color'] }} text-4xl">
                        {{ $metric['icon'] }}
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">{{ $metric['title'] }}</h3>
                    <p class="text-4xl font-extrabold text-gray-900">{{ $metric['value'] }}</p>
                    @if ($metric['growth'] !== 0)
                        <p
                            class="mt-2 flex items-center space-x-1 text-sm font-semibold {{ $metric['growth'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                            @if ($metric['growth'] > 0)
                                <span>▲</span>
                            @else
                                <span>▼</span>
                            @endif
                            <span>{{ abs($metric['growth']) }}%</span>
                            <span class="text-gray-400">desde ayer</span>
                        </p>
                    @else
                        <p class="mt-2 text-gray-400 text-sm">Sin cambios recientes</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Gráfico sencillo de ventas --}}
        <div class="mt-14 bg-white rounded-xl shadow-lg p-8 transition-colors duration-500">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                📊 Ventas últimas 7 días
            </h2>

            <svg viewBox="0 0 350 120" class="w-full h-32" xmlns="http://www.w3.org/2000/svg" role="img"
                aria-label="Gráfico de barras de ventas">
                @php
                    $sales = [5, 12, 9, 15, 20, 18, 25];
                    $maxSales = max($sales);
                    $barWidth = 40;
                    $barGap = 10;
                @endphp
                @foreach ($sales as $index => $sale)
                    @php
                        $barHeight = ($sale / $maxSales) * 100;
                        $x = $index * ($barWidth + $barGap);
                        $y = 110 - $barHeight;
                    @endphp
                    <rect x="{{ $x }}" y="110" width="{{ $barWidth }}" height="0"
                        class="fill-indigo-600 cursor-pointer transition-all duration-700 delay-{{ $index * 100 }}"
                        data-target-height="{{ $barHeight }}" />
                    <text x="{{ $x + $barWidth / 2 }}" y="115" text-anchor="middle"
                        class="text-gray-700 text-xs font-semibold" fill="currentColor">
                        Día {{ $index + 1 }}
                    </text>
                @endforeach
            </svg>
        </div>

        <script>
            // Animación barras SVG
            window.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('rect').forEach(rect => {
                    const height = rect.getAttribute('data-target-height');
                    rect.setAttribute('height', height);
                    rect.setAttribute('y', 110 - height);
                });
            });
        </script>

        <style>
            /* Animación fadeIn simple */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(15px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fadeIn {
                animation: fadeIn 0.5s ease forwards;
            }

            .delay-100 {
                animation-delay: 0.1s;
            }

            .delay-200 {
                animation-delay: 0.2s;
            }
        </style>


    @endsection

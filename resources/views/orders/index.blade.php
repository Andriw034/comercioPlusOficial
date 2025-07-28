@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 text-black">
    <h1 class="text-4xl font-bold mb-8 border-b-2 border-orange-400 pb-2">Órdenes Registradas</h1>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-orange-100 text-orange-800 uppercase text-xs font-semibold sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Total</th>
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">Método de Pago</th>
                    <th class="px-6 py-4">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-6 py-4">{{ $order->id }}</td>
                        <td class="px-6 py-4">{{ $order->user->name ?? 'Sin usuario' }}</td>
                        <td class="px-6 py-4 font-semibold text-green-700">${{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 capitalize">{{ $order->payment_method }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('orders.show', $order) }}"
                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium hover:underline transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24">
                                    <path d="M15 12H9m12 0A9 9 0 1112 3a9 9 0 019 9z" />
                                </svg>
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500 italic">
                            No hay órdenes registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

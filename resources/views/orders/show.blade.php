@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 text-black">
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <h1 class="text-3xl font-bold mb-6 border-b-2 border-orange-400 pb-2">
            Detalle de la Orden #{{ $order->id }}
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 text-base">
            <div>
                <p class="mb-2">
                    <span class="font-semibold text-gray-700">👤 Cliente:</span>
                    {{ $order->user->name }}
                </p>
                <p>
                    <span class="font-semibold text-gray-700">💳 Método de pago:</span>
                    {{ ucfirst($order->payment_method) }}
                </p>
            </div>
            <div>
                <p class="mb-2">
                    <span class="font-semibold text-gray-700">📅 Fecha:</span>
                    {{ \Carbon\Carbon::parse($order->date)->format('d/m/Y H:i') }}
                </p>
                <p>
                    <span class="font-semibold text-gray-700">💰 Total:</span>
                    <span class="text-green-700 font-semibold">${{ number_format($order->total, 0, ',', '.') }}</span>
                </p>
            </div>
        </div>

        <h2 class="text-2xl font-semibold mb-4 border-b border-gray-200 pb-2">
            🛒 Productos comprados
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border border-gray-200 rounded-lg">
                <thead class="bg-orange-100 text-orange-800 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Precio Unitario</th>
                        <th class="px-4 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white text-gray-800 divide-y divide-gray-100">
                    @forelse ($order->ordenproducts as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">{{ $item->product->name }}</td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3">${{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-medium text-green-700">
                                ${{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center px-4 py-6 text-gray-500 italic">
                                No hay productos en esta orden.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

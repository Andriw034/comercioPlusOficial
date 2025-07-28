@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-10 text-black">
    <h1 class="text-4xl font-bold mb-8 text-gray-800 border-b-2 border-orange-500 pb-2">
        Reclamos de Clientes
    </h1>

    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded shadow">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabla de reclamos --}}
    <div class="bg-white shadow-xl rounded-2xl overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Mensaje</th>
                    <th class="px-6 py-4">Fecha del Reclamo</th>
                    <th class="px-6 py-4">Método de Contacto</th>
                    <th class="px-6 py-4">Fecha de Creación</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($claims as $claim)
                    <tr class="hover:bg-gray-50 transition duration-200">
                        <td class="px-6 py-4 font-semibold">{{ $claim->id }}</td>
                        <td class="px-6 py-4">{{ $claim->user->name ?? 'Desconocido' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $claim->message }}</td>
                        <td class="px-6 py-4">📅 {{ \Carbon\Carbon::parse($claim->date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 capitalize">✉ {{ $claim->contact_method }}</td>
                        <td class="px-6 py-4">⏱ {{ $claim->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500 italic">
                            No hay reclamos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

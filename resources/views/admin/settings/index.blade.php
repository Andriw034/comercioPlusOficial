@extends('layouts.dashboard')

@section('title', 'Configuraciones')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-6">Configuraciones</h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-800 text-emerald-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-gray-800 border border-gray-700 rounded-lg">
        <div class="border-b border-gray-700">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <a href="#general" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'general' || !request('tab') ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-300 hover:text-gray-100 hover:border-gray-300' }}" data-tab="general">
                    General
                </a>
                <a href="#appearance" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'appearance' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-300 hover:text-gray-100 hover:border-gray-300' }}" data-tab="appearance">
                    Apariencia
                </a>
                <a href="#payments" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'payments' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-300 hover:text-gray-100 hover:border-gray-300' }}" data-tab="payments">
                    Pagos
                </a>
                <a href="#shipping" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'shipping' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-300 hover:text-gray-100 hover:border-gray-300' }}" data-tab="shipping">
                    Envíos
                </a>
                <a href="#taxes" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'taxes' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-300 hover:text-gray-100 hover:border-gray-300' }}" data-tab="taxes">
                    Impuestos
                </a>
                <a href="#notifications" class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ request('tab') == 'notifications' ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-300 hover:text-gray-100 hover:border-gray-300' }}" data-tab="notifications">
                    Notificaciones
                </a>
            </nav>
        </div>

        <div class="p-6">
            <div id="general" class="tab-content {{ request('tab') == 'general' || !request('tab') ? '' : 'hidden' }}">
                @include('admin.settings.tabs._general')
            </div>
            <div id="appearance" class="tab-content {{ request('tab') == 'appearance' ? '' : 'hidden' }}">
                @include('admin.settings.tabs._appearance')
            </div>
            <div id="payments" class="tab-content {{ request('tab') == 'payments' ? '' : 'hidden' }}">
                @include('admin.settings.tabs._payments')
            </div>
            <div id="shipping" class="tab-content {{ request('tab') == 'shipping' ? '' : 'hidden' }}">
                @include('admin.settings.tabs._shipping')
            </div>
            <div id="taxes" class="tab-content {{ request('tab') == 'taxes' ? '' : 'hidden' }}">
                @include('admin.settings.tabs._taxes')
            </div>
            <div id="notifications" class="tab-content {{ request('tab') == 'notifications' ? '' : 'hidden' }}">
                @include('admin.settings.tabs._notifications')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const tab = this.getAttribute('data-tab');

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);

            // Hide all tabs
            tabContents.forEach(content => content.classList.add('hidden'));

            // Show selected tab
            document.getElementById(tab).classList.remove('hidden');

            // Update active link
            tabLinks.forEach(l => {
                l.classList.remove('border-orange-500', 'text-orange-500');
                l.classList.add('border-transparent', 'text-gray-300');
            });
            this.classList.remove('border-transparent', 'text-gray-300');
            this.classList.add('border-orange-500', 'text-orange-500');
        });
    });
});
</script>
@endpush
@endsection

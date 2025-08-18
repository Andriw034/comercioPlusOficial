@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Configuración de la Tienda</h2>
            <p class="text-sm text-gray-600 mt-1">Personaliza la apariencia de tu tienda</p>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('store.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Información Básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la tienda</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $store->name ?? '') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700">URL personalizada</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                /tienda/
                            </span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $store->slug ?? '') }}" required
                                   class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-orange-500 focus:border-orange-500">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">{{ old('description', $store->description ?? '') }}</textarea>
                </div>

                <!-- Personalización de Colores -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Personalización de Colores</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700">Color Principal</label>
                            <div class="mt-1 flex items-center">
                                <input type="color" name="primary_color" id="primary_color" 
                                       value="{{ old('primary_color', $store->primary_color ?? '#FF6000') }}"
                                       class="h-10 w-20 rounded border-gray-300">
                                <input type="text" name="primary_color_hex" 
                                       value="{{ old('primary_color', $store->primary_color ?? '#FF6000') }}"
                                       class="ml-2 flex-1 rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                            </div>
                        </div>

                        <div>
                            <label for="background_color" class="block text-sm font-medium text-gray-700">Color de Fondo</label>
                            <div class="mt-1 flex items-center">
                                <input type="color" name="background_color" id="background_color"
                                       value="{{ old('background_color', $store->background_color ?? '#f9f9f9') }}"
                                       class="h-10 w-20 rounded border-gray-300">
                                <input type="text" name="background_color_hex"
                                       value="{{ old('background_color', $store->background_color ?? '#f9f9f9') }}"
                                       class="ml-2 flex-1 rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                            </div>
                        </div>

                        <div>
                            <label for="text_color" class="block text-sm font-medium text-gray-700">Color de Texto</label>
                            <div class="mt-1 flex items-center">
                                <input type="color" name="text_color" id="text_color"
                                       value="{{ old('text_color', $store->text_color ?? '#333333') }}"
                                       class="h-10 w-20 rounded border-gray-300">
                                <input type="text" name="text_color_hex"
                                       value="{{ old('text_color', $store->text_color ?? '#333333') }}"
                                       class="ml-2 flex-1 rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                            </div>
                        </div>

                        <div>
                            <label for="button_color" class="block text-sm font-medium text-gray-700">Color de Botones</label>
                            <div class="mt-1 flex items-center">
                                <input type="color" name="button_color" id="button_color"
                                       value="{{ old('button_color', $store->button_color ?? '#FF6000') }}"
                                       class="h-10 w-20 rounded border-gray-300">
                                <input type="text" name="button_color_hex"
                                       value="{{ old('button_color', $store->button_color ?? '#FF6000') }}"
                                       class="ml-2 flex-1 rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mt-4 p-4 border rounded-lg bg-gray-50">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Vista previa</h4>
                        <div id="color-preview" class="p-4 rounded-lg border" style="background-color: {{ old('background_color', $store->background_color ?? '#f9f9f9') }}">
                            <h5 style="color: {{ old('text_color', $store->text_color ?? '#333333') }}">Mi Tienda</h5>
                            <button class="px-4 py-2 rounded" style="background-color: {{ old('button_color', $store->button_color ?? '#FF6000') }}; color: white">
                                Comprar ahora
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Imágenes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700">Logo</label>
                        <input type="file" name="logo" id="logo" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500">
                        @if($store->logo)
                            <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo actual" class="mt-2 h-16 rounded">
                        @endif
                    </div>

                    <div>
                        <label for="cover_image" class="block text-sm font-medium text-gray-700">Imagen de portada</label>
                        <input type="file" name="cover_image" id="cover_image" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500">
                        @if($store->cover_image)
                            <img src="{{ asset('storage/' . $store->cover_image) }}" alt="Portada actual" class="mt-2 h-32 rounded">
                        @endif
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                        <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $store->direccion ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" value="{{ old('telefono', $store->telefono ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500">
                    </div>
                </div>

                <div class="flexHere is the plan for the next steps before implementation:

Information Gathered:
- User model and store model are structured with relationships.
- Store model updated with customization fields.
- Registration view created with role selection.

Plan:
- Update registration controller to handle role_id and redirect accordingly.
- Update login controller to redirect based on role and store existence.
- Implement store creation flow for merchants.
- Implement public store view for clients.
- Implement store settings update with color and image uploads.
- Implement product management for merchants.
- Implement store sharing with copy link and QR code generation.

Dependent Files to be edited:
- app/Http/Controllers/Auth/RegisterController.php (or equivalent)
- app/Http/Controllers/Auth/LoginController.php
- app/Http/Controllers/StoreController.php
- routes/web.php
- resources/views/store/create.blade.php (if needed)
- resources/views/store/public.blade.php
- resources/views/store/settings.blade.php (already reviewed)
- resources/views/admin/dashboard.blade.php (for share button)

Followup steps:
- Implement controller logic for registration and login redirects.
- Implement store creation and settings update.
- Implement public store view with custom colors.
- Implement share button with QR code generation.
- Test all flows and UI responsiveness.

<ask_followup_question>
<question>Do you confirm this plan so I can proceed with the controller and route updates for registration and login redirects?</question>
</ask_followup_question>

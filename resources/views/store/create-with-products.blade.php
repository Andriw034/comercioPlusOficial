@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Crear Nueva Tienda con Productos Iniciales</h1>
        
        <form action="{{ route('store.store-with-products') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- Información de la Tienda -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Información de la Tienda</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Tienda *</label>
                        <input type="text" name="name" id="name" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (URL personalizada)</label>
                        <input type="text" name="slug" id="slug" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea name="description" id="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <label for="categoria_principal" class="block text-sm font-medium text-gray-700 mb-2">Categoría Principal</label>
                        <select name="categoria_principal" id="categoria_principal" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccionar categoría</option>
                            <option value="tecnologia">Tecnología</option>
                            <option value="ropa">Ropa y Moda</option>
                            <option value="hogar">Hogar y Decoración</option>
                            <option value="alimentos">Alimentos y Bebidas</option>
                            <option value="belleza">Belleza y Cuidado Personal</option>
                            <option value="deportes">Deportes y Fitness</option>
                            <option value="juguetes">Juguetes y Juegos</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>

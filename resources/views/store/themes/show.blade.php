@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $theme->name }}</h1>
            <p class="mt-2 text-sm text-gray-600">Theme details and preview</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('stores.themes.edit', [$store, $theme]) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Edit Theme
            </a>
            <a href="{{ route('stores.themes.index', $store) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Back to Themes
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Theme Details -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Theme Configuration</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Current theme settings and values.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $theme->name }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Primary Color</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded border border-gray-300" style="background-color: {{ $theme->primary_color }}"></div>
                                <span>{{ $theme->primary_color }}</span>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Secondary Color</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded border border-gray-300" style="background-color: {{ $theme->secondary_color }}"></div>
                                <span>{{ $theme->secondary_color }}</span>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Background Color</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded border border-gray-300" style="background-color: {{ $theme->background_color }}"></div>
                                <span>{{ $theme->background_color }}</span>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Text Color</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded border border-gray-300" style="background-color: {{ $theme->text_color }}"></div>
                                <span>{{ $theme->text_color }}</span>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Font Family</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $theme->font_family }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Background Image</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($theme->background_image)
                                <img src="{{ Storage::url($theme->background_image) }}" alt="Background" class="h-20 w-auto rounded">
                            @else
                                <span class="text-gray-500">No background image</span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Logo</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($theme->logo)
                                <img src="{{ Storage::url($theme->logo) }}" alt="Logo" class="h-20 w-auto rounded">
                            @else
                                <span class="text-gray-500">No logo</span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Custom CSS</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($theme->custom_css)
                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ $theme->custom_css }}</pre>
                            @else
                                <span class="text-gray-500">No custom CSS</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Live Preview</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">How your store will look with this theme.</p>
            </div>
            <div class="border-t border-gray-200 p-4">
                <div class="border rounded-lg overflow-hidden" style="background-color: {{ $theme->background_color }}; color: {{ $theme->text_color }}; font-family: {{ $theme->font_family }}">
                    @if($theme->background_image)
                        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ Storage::url($theme->background_image) }}')"></div>
                    @endif
                    
                    <!-- Mock Store Header -->
                    <div class="relative bg-opacity-90 p-4" style="background-color: {{ $theme->primary_color }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($theme->logo)
                                    <img src="{{ Storage::url($theme->logo) }}" alt="Logo" class="h-8 w-auto">
                                @else
                                    <div class="w-8 h-8 rounded" style="background-color: {{ $theme->secondary_color }}"></div>
                                @endif
                                <h2 class="text-xl font-bold" style="color: {{ $theme->text_color }}">Your Store</h2>
                            </div>
                            <nav class="flex space-x-4">
                                <a href="#" class="text-sm hover:opacity-80" style="color: {{ $theme->text_color }}">Home</a>
                                <a href="#" class="text-sm hover:opacity-80" style="color: {{ $theme->text_color }}">Products</a>
                                <a href="#" class="text-sm hover:opacity-80" style="color: {{ $theme->text_color }}">About</a>
                            </nav>
                        </div>
                    </div>

                    <!-- Mock Store Content -->
                    <div class="relative p-6">
                        <h3 class="text-2xl font-bold mb-4">Featured Products</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border rounded-lg p-4" style="border-color: {{ $theme->secondary_color }}">
                                <div class="w-full h-32 rounded mb-2" style="background-color: {{ $theme->secondary_color }}; opacity: 0.3"></div>
                                <h4 class="font-semibold mb-1">Product 1</h4>
                                <p class="text-sm opacity-75 mb-2">$29.99</p>
                                <button class="w-full py-2 px-4 rounded text-sm" style="background-color: {{ $theme->primary_color }}; color: {{ $theme->text_color }}">Add to Cart</button>
                            </div>
                            <div class="border rounded-lg p-4" style="border-color: {{ $theme->secondary_color }}">
                                <div class="w-full h-32 rounded mb-2" style="background-color: {{ $theme->secondary_color }}; opacity: 0.3"></div>
                                <h4 class="font-semibold mb-1">Product 2</h4>
                                <p class="text-sm opacity-75 mb-2">$49.99</p>
                                <button class="w-full py-2 px-4 rounded text-sm" style="background-color: {{ $theme->primary_color }}; color: {{ $theme->text_color }}">Add to Cart</button>
                            </div>
                        </div>
                    </div>

                    <!-- Mock Store Footer -->
                    <div class="relative p-4 border-t" style="border-color: {{ $theme->secondary_color }}">
                        <p class="text-center text-sm opacity-75">© 2024 Your Store. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

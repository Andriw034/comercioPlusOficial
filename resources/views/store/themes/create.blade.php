@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New Theme</h1>
        <p class="mt-2 text-sm text-gray-600">Customize the appearance of your store</p>
    </div>

    <form action="{{ route('stores.themes.store', $store) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6">
                    <!-- Theme Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Theme Name</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Color Scheme -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700">Primary Color</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <input type="color" name="primary_color" id="primary_color" value="#3B82F6" required
                                       class="h-10 w-20 rounded-md border-gray-300">
                                <input type="text" name="primary_color_text" id="primary_color_text" value="#3B82F6" required
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700">Secondary Color</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <input type="color" name="secondary_color" id="secondary_color" value="#6B7280" required
                                       class="h-10 w-20 rounded-md border-gray-300">
                                <input type="text" name="secondary_color_text" id="secondary_color_text" value="#6B7280" required
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="background_color" class="block text-sm font-medium text-gray-700">Background Color</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <input type="color" name="background_color" id="background_color" value="#FFFFFF" required
                                       class="h-10 w-20 rounded-md border-gray-300">
                                <input type="text" name="background_color_text" id="background_color_text" value="#FFFFFF" required
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="text_color" class="block text-sm font-medium text-gray-700">Text Color</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <input type="color" name="text_color" id="text_color" value="#111827" required
                                       class="h-10 w-20 rounded-md border-gray-300">
                                <input type="text" name="text_color_text" id="text_color_text" value="#111827" required
                                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Font Family -->
                    <div>
                        <label for="font_family" class="block text-sm font-medium text-gray-700">Font Family</label>
                        <select name="font_family" id="font_family" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="Helvetica, sans-serif">Helvetica</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="Times New Roman, serif">Times New Roman</option>
                            <option value="Courier New, monospace">Courier New</option>
                            <option value="Verdana, sans-serif">Verdana</option>
                            <option value="Inter, sans-serif">Inter</option>
                        </select>
                    </div>

                    <!-- Images -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="background_image" class="block text-sm font-medium text-gray-700">Background Image</label>
                            <input type="file" name="background_image" id="background_image" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <div>
                            <label for="logo" class="block text-sm font-medium text-gray-700">Logo</label>
                            <input type="file" name="logo" id="logo" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>

                    <!-- Custom CSS -->
                    <div>
                        <label for="custom_css" class="block text-sm font-medium text-gray-700">Custom CSS</label>
                        <textarea name="custom_css" id="custom_css" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                  placeholder="Enter custom CSS here..."></textarea>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                <a href="{{ route('stores.themes.index', $store) }}" 
                   class="inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Theme
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Sync color inputs with text inputs
    document.getElementById('primary_color').addEventListener('input', function(e) {
        document.getElementById('primary_color_text').value = e.target.value;
    });
    document.getElementById('primary_color_text').addEventListener('input', function(e) {
        document.getElementById('primary_color').value = e.target.value;
    });
    
    document.getElementById('secondary_color').addEventListener('input', function(e) {
        document.getElementById('secondary_color_text').value = e.target.value;
    });
    document.getElementById('secondary_color_text').addEventListener('input', function(e) {
        document.getElementById('secondary_color').value = e.target.value;
    });
    
    document.getElementById('background_color').addEventListener('input', function(e) {
        document.getElementById('background_color_text').value = e.target.value;
    });
    document.getElementById('background_color_text').addEventListener('input', function(e) {
        document.getElementById('background_color').value = e.target.value;
    });
    
    document.getElementById('text_color').addEventListener('input', function(e) {
        document.getElementById('text_color_text').value = e.target.value;
    });
    document.getElementById('text_color_text').addEventListener('input', function(e) {
        document.getElementById('text_color').value = e.target.value;
    });
</script>
@endsection

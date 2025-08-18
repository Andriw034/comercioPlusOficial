@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Store Themes</h1>
        <a href="{{ route('stores.themes.create', $store) }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            Create New Theme
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($themes as $theme)
                <li class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">{{ $theme->name }}</h3>
                            <div class="mt-2 flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <span class="w-4 h-4 rounded" style="background-color: {{ $theme->primary_color }}"></span>
                                    <span class="text-sm text-gray-500">Primary</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-4 h-4 rounded" style="background-color: {{ $theme->secondary_color }}"></span>
                                    <span class="text-sm text-gray-500">Secondary</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-4 h-4 rounded" style="background-color: {{ $theme->background_color }}"></span>
                                    <span class="text-sm text-gray-500">Background</span>
                                </div>
                            </div>
                            @if($theme->is_active)
                                <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            @if(!$theme->is_active)
                                <form action="{{ route('stores.themes.activate', [$store, $theme]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 text-sm">Activate</button>
                                </form>
                            @endif
                            <a href="{{ route('stores.themes.edit', [$store, $theme]) }}" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</a>
                            <form action="{{ route('stores.themes.destroy', [$store, $theme]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm"
                                        onclick="return confirm('Are you sure you want to delete this theme?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-4 text-center text-gray-500">
                    No themes created yet. Create your first theme!
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection

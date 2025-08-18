@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
    $baseClasses = 'font-medium rounded-xl transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $variants = [
        'primary' => 'bg-primary text-white hover:bg-primary-light focus:ring-primary',
        'secondary' => 'bg-secondary text-text-dark hover:bg-secondary-light focus:ring-secondary',
        'outline' => 'border border-primary text-primary hover:bg-primary hover:text-white focus:ring-primary',
        'ghost' => 'text-primary hover:bg-secondary-light focus:ring-primary',
    ];
    
    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
    ];
    
    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>

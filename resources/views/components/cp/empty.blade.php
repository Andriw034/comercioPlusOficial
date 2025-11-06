@props([
  'icon' => '📦',
  'title' => 'Sin datos',
  'text' => 'No hay información para mostrar.'
])

<div class="cp-empty">
  <div class="cp-empty-icon">{{ $icon }}</div>
  <div class="cp-empty-title">{{ $title }}</div>
  <div class="cp-empty-text">{{ $text }}</div>

  @if(trim($slot))
    <div class="cp-empty-actions">
      {{ $slot }}
    </div>
  @endif
</div>

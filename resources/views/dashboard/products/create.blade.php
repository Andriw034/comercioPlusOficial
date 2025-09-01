@extends('layouts.app')
@section('title','Nuevo producto - ComercioPlus')

@section('content')
<div class="container py-8 max-w-6xl">
  <div class="mb-6">
    <h1 class="text-3xl font-bold text-foreground mb-2">Agregar nuevo producto</h1>
    <p class="text-muted-foreground">Completa la información de tu producto para agregarlo a tu catálogo</p>
  </div>

  <form method="POST" action="{{ route('dashboard.products.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    @csrf
    
    <!-- Panel de imagen -->
    <div class="space-y-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Imagen del producto</h3>
          <p class="card-description">Sube una imagen atractiva de tu producto</p>
        </div>
        <div class="card-content">
          <div class="space-y-4">
            <!-- Vista previa de imagen -->
            <div id="imagePreview" class="h-64 w-full bg-muted rounded-lg border-2 border-dashed border-border flex items-center justify-center">
              <div class="text-center">
                <svg class="h-12 w-12 text-muted-foreground mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-muted-foreground">Haz clic para seleccionar una imagen</p>
                <p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF hasta 10MB</p>
              </div>
            </div>
            
            <!-- Input de archivo -->
            <div class="space-y-2">
              <label for="image" class="label">Seleccionar imagen</label>
              <input 
                type="file" 
                id="image"
                name="image" 
                accept="image/*"
                class="input cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                onchange="previewImage(this)"
              >
              @error('image') 
                <p class="text-destructive text-sm flex items-center gap-1">
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  {{ $message }}
                </p> 
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel de información -->
    <div class="space-y-6">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Información del producto</h3>
          <p class="card-description">Proporciona los detalles básicos del producto</p>
        </div>
        <div class="card-content space-y-4">
          <!-- Nombre -->
          <div class="space-y-2">
            <label for="name" class="label">Nombre del producto *</label>
            <input 
              id="name"
              name="name" 
              type="text"
              class="input" 
              placeholder="Ej: iPhone 15 Pro Max"
              value="{{ old('name') }}"
              required
            >
            @error('name') 
              <p class="text-destructive text-sm flex items-center gap-1">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $message }}
              </p> 
            @enderror
          </div>

          <!-- Precio y Stock -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label for="price" class="label">Precio *</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground">$</span>
                <input 
                  id="price"
                  name="price" 
                  type="number" 
                  step="0.01" 
                  min="0"
                  class="input pl-8" 
                  placeholder="0.00"
                  value="{{ old('price') }}"
                  required
                >
              </div>
              @error('price') 
                <p class="text-destructive text-sm flex items-center gap-1">
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  {{ $message }}
                </p> 
              @enderror
            </div>
            
            <div class="space-y-2">
              <label for="stock" class="label">Stock disponible *</label>
              <input 
                id="stock"
                name="stock" 
                type="number" 
                min="0"
                class="input" 
                placeholder="0"
                value="{{ old('stock') }}"
                required
              >
              @error('stock') 
                <p class="text-destructive text-sm flex items-center gap-1">
                  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  {{ $message }}
                </p> 
              @enderror
            </div>
          </div>

          <!-- Categoría -->
          <div class="space-y-2">
            <label for="category_id" class="label">Categoría *</label>
            <select id="category_id" name="category_id" class="input" required>
              <option value="">Selecciona una categoría</option>
              @foreach($categories as $cat)
                <option value="{{ $cat['id'] }}" {{ old('category_id') == $cat['id'] ? 'selected' : '' }}>
                  {{ $cat['name'] }}
                </option>
              @endforeach
            </select>
            @error('category_id') 
              <p class="text-destructive text-sm flex items-center gap-1">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $message }}
              </p> 
            @enderror
          </div>

          <!-- Descripción -->
          <div class="space-y-2">
            <label for="description" class="label">Descripción</label>
            <textarea 
              id="description"
              name="description" 
              rows="4" 
              class="input resize-none" 
              placeholder="Describe las características principales de tu producto..."
            >{{ old('description') }}</textarea>
            @error('description') 
              <p class="text-destructive text-sm flex items-center gap-1">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $message }}
              </p> 
            @enderror
          </div>
        </div>
      </div>

      <!-- Botones de acción -->
      <div class="flex gap-4">
        <button type="submit" class="btn btn-primary flex-1">
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Guardar producto
        </button>
        <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline">
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancelar
        </a>
      </div>
    </div>
  </form>
</div>

<script>
function previewImage(input) {
  const preview = document.getElementById('imagePreview');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      preview.innerHTML = '<img src="' + e.target.result + '" class="h-full w-full object-cover rounded-lg" alt="Vista previa">';
    };
    
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.innerHTML = 
      '<div class="text-center">' +
        '<svg class="h-12 w-12 text-muted-foreground mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
          '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>' +
        '</svg>' +
        '<p class="text-sm text-muted-foreground">Haz clic para seleccionar una imagen</p>' +
        '<p class="text-xs text-muted-foreground mt-1">PNG, JPG, GIF hasta 10MB</p>' +
      '</div>';
  }
}
</script>
@endsection

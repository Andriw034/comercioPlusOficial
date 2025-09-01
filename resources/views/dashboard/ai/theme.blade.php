@extends('layouts.app')

@section('title', 'Branding con IA')

@section('content')
<div class="container py-8 max-w-3xl">
  <h1 class="text-2xl font-bold mb-2">Branding con IA</h1>
  <p class="text-muted-foreground mb-6">Genera una paleta de colores para tu tienda, basada en tu logo y portada (simulado).</p>

  <div class="rounded-lg border bg-card p-6 space-y-6" id="ai-theme">
    <div class="space-y-4">
      <div>
        <label class="text-sm font-medium">Nombre de la Tienda</label>
        <input id="shopName" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" required value="Moto Repuestos Pro" />
      </div>
      <div>
        <label class="text-sm font-medium">Logo (Data URI o URL)</label>
        <input id="logo" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" placeholder="data:image/png;base64,..." />
      </div>
      <div>
        <label class="text-sm font-medium">Portada (Data URI o URL)</label>
        <input id="cover" class="mt-1 w-full h-10 rounded-md border border-input bg-background px-3" placeholder="data:image/jpeg;base64,..." />
      </div>
      <button id="generateBtn" class="w-full h-11 rounded-md bg-primary text-primary-foreground font-semibold">Generar Tema con IA</button>
    </div>

    <div class="space-y-4 pt-4 border-t">
      <h4 class="font-semibold">Paleta de Colores</h4>
      <div class="grid grid-cols-2 gap-4" id="colorsGrid"></div>
    </div>

    <div class="space-y-4 pt-4 border-t">
      <h4 class="font-semibold">Vista previa</h4>
      <div id="previewBox" class="rounded-lg p-4 border">
        <h5 class="font-bold text-lg">Producto de Muestra</h5>
        <p class="text-sm opacity-80">Una descripción breve del producto.</p>
        <button id="previewBtn" class="mt-4 h-10 px-4 rounded-md font-semibold">Botón Principal</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const colorsDefault = {
    primaryColor: '#FF6A2E',
    secondaryColor: '#FF9156',
    backgroundColor: '#FFF7F2',
    textColor: '#0F172A',
  };

  const grid = document.getElementById('colorsGrid');
  const previewBox = document.getElementById('previewBox');
  const previewBtn = document.getElementById('previewBtn');
  const generateBtn = document.getElementById('generateBtn');

  let colors = {...colorsDefault};
  renderColorInputs();

  function labelize(key){ 
    return key.replace(/([A-Z])/g, ' $1').replace(/^./, function(s) { return s.toUpperCase(); }); 
  }
  
  function renderColorInputs(){
    grid.innerHTML = '';
    Object.entries(colors).forEach(function(entry) {
      var key = entry[0];
      var value = entry[1];
      var wrap = document.createElement('div');
      wrap.className = 'space-y-2';
      wrap.innerHTML = 
        '<label class="text-sm font-medium">' + labelize(key) + '</label>' +
        '<div class="flex items-center gap-2">' +
          '<input type="color" value="' + value + '" data-key="' + key + '" class="p-1 h-10 w-12 rounded-md border"/>' +
          '<input value="' + value + '" data-key="' + key + '" class="flex-1 h-10 rounded-md border border-input bg-background px-3"/>' +
        '</div>';
      grid.appendChild(wrap);
    });
    applyPreview();
    grid.querySelectorAll('input').forEach(function(inp) {
      inp.addEventListener('input', function(e) {
        var k = e.target.getAttribute('data-key');
        colors[k] = e.target.value;
        // Sincronizar ambos inputs del mismo color
        grid.querySelectorAll('[data-key="' + k + '"]').forEach(function(el) { 
          if(el !== e.target) el.value = e.target.value; 
        });
        applyPreview();
      });
    });
  }

  function applyPreview(){
    previewBox.style.backgroundColor = colors.backgroundColor;
    previewBox.style.color = colors.textColor;
    previewBtn.style.backgroundColor = colors.primaryColor;
    previewBtn.style.color = colors.textColor;
  }

  generateBtn.addEventListener('click', function() {
    var shopName = document.getElementById('shopName').value.trim();
    var logo = document.getElementById('logo').value.trim();
    var cover = document.getElementById('cover').value.trim();
    if(!shopName || !logo || !cover){
      alert('Por favor, completa nombre, logo y portada.');
      return;
    }
    generateBtn.disabled = true;
    generateBtn.textContent = 'Generando...';
    
    fetch('{{ route("dashboard.ai.generate") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ shopName: shopName, logo: logo, cover: cover })
    })
    .then(function(res) {
      return res.json();
    })
    .then(function(json) {
      colors = json;
      renderColorInputs();
      alert('¡Tema generado!');
    })
    .catch(function(e) {
      console.error(e);
      alert('Error al generar el tema.');
    })
    .finally(function() {
      generateBtn.disabled = false;
      generateBtn.textContent = 'Generar Tema con IA';
    });
  });
})();
</script>
@endsection

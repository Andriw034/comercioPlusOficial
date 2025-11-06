document.addEventListener("DOMContentLoaded",function(){i()});async function i(){const t=document.querySelector(".grid.grid-cols-2.md\\:grid-cols-3.gap-4");if(!t){console.warn("Contenedor de imágenes del demo no encontrado");return}try{t.innerHTML='<div class="col-span-full text-center py-8"><div class="text-white/70">Cargando imágenes...</div></div>';const e=await fetch("/api/demo/supabase-images?limit=5");if(!e.ok)throw new Error(`Error HTTP: ${e.status}`);const a=await e.json();if(a.error)throw new Error(a.message||"Error al cargar imágenes");if(!a.data||a.data.length===0){t.innerHTML='<div class="col-span-full text-center py-8"><div class="text-white/70">No hay imágenes disponibles</div></div>';return}const o=a.data.map(s=>{const r=s.title||"Imagen sin título";return`
                <figure class="group relative aspect-[4/3] rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5">
                    <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                         src="${s.url||"/images/placeholder.jpg"}"
                         alt="${r}"
                         onerror="this.src='/images/placeholder.jpg'">
                    <figcaption class="absolute inset-x-0 bottom-0 p-2 text-[11px] font-medium text-white/95 bg-gradient-to-t from-black/60 to-transparent">
                        ${r}
                    </figcaption>
                </figure>
            `}).join("");t.innerHTML=o}catch(e){console.error("Error cargando imágenes de Supabase:",e),t.innerHTML=`
            <div class="col-span-full text-center py-8">
                <div class="text-white/70">Error al cargar imágenes</div>
                <div class="text-xs text-white/50 mt-1">${e.message}</div>
            </div>
        `,setTimeout(()=>{n(t)},3e3)}}function n(t){const a=[{alt:"Cascos y protección",src:"https://images.unsplash.com/photo-1542362567-b07e54358753?q=80&w=900&auto=format&fit=crop"},{alt:"Llantas y rines",src:"https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=900&auto=format&fit=crop"},{alt:"Frenos y discos",src:"https://images.unsplash.com/photo-1526045478516-99145907023c?q=80&w=900&auto=format&fit=crop"},{alt:"Transmisión y cadenas",src:"https://images.unsplash.com/photo-1602320734573-1b57f5813996?q=80&w=900&auto=format&fit=crop"},{alt:"Aceites y lubricantes",src:"https://images.unsplash.com/photo-1589578527966-1e9b2ae05a0e?q=80&w=900&auto=format&fit=crop"},{alt:"Iluminación/eléctricos",src:"https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?q=80&w=900&auto=format&fit=crop"}].map(o=>`
        <figure class="group relative aspect-[4/3] rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5">
            <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                 src="${o.src}" alt="${o.alt}">
            <figcaption class="absolute inset-x-0 bottom-0 p-2 text-[11px] font-medium text-white/95 bg-gradient-to-t from-black/60 to-transparent">
                ${o.alt}
            </figcaption>
        </figure>
    `).join("");t.innerHTML=a}

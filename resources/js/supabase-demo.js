/**
 * Script para cargar imágenes dinámicas desde Supabase en el demo de welcome
 * Reemplaza las imágenes estáticas de Unsplash con imágenes de Supabase
 */

document.addEventListener('DOMContentLoaded', function() {
    loadSupabaseImages();
});

async function loadSupabaseImages() {
    const container = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-3.gap-4');

    if (!container) {
        console.warn('Contenedor de imágenes del demo no encontrado');
        return;
    }

    try {
        // Mostrar indicador de carga
        container.innerHTML = '<div class="col-span-full text-center py-8"><div class="text-white/70">Cargando imágenes...</div></div>';

        // Hacer petición a la API de Supabase
        const response = await fetch('/api/demo/supabase-images?limit=5');

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();

        if (data.error) {
            throw new Error(data.message || 'Error al cargar imágenes');
        }

        // Si no hay imágenes, mostrar mensaje
        if (!data.data || data.data.length === 0) {
            container.innerHTML = '<div class="col-span-full text-center py-8"><div class="text-white/70">No hay imágenes disponibles</div></div>';
            return;
        }

        // Generar HTML con las imágenes de Supabase
        const imagesHtml = data.data.map(image => {
            const title = image.title || 'Imagen sin título';
            const url = image.url || '/images/placeholder.jpg'; // fallback si no hay URL

            return `
                <figure class="group relative aspect-[4/3] rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5">
                    <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                         src="${url}"
                         alt="${title}"
                         onerror="this.src='/images/placeholder.jpg'">
                    <figcaption class="absolute inset-x-0 bottom-0 p-2 text-[11px] font-medium text-white/95 bg-gradient-to-t from-black/60 to-transparent">
                        ${title}
                    </figcaption>
                </figure>
            `;
        }).join('');

        // Reemplazar contenido del contenedor
        container.innerHTML = imagesHtml;

    } catch (error) {
        console.error('Error cargando imágenes de Supabase:', error);

        // Mostrar mensaje de error
        container.innerHTML = `
            <div class="col-span-full text-center py-8">
                <div class="text-white/70">Error al cargar imágenes</div>
                <div class="text-xs text-white/50 mt-1">${error.message}</div>
            </div>
        `;

        // Fallback: volver a las imágenes estáticas de Unsplash
        setTimeout(() => {
            loadFallbackImages(container);
        }, 3000);
    }
}

function loadFallbackImages(container) {
    // Imágenes de fallback (las originales de Unsplash)
    const fallbackImages = [
        { alt: 'Cascos y protección', src: 'https://images.unsplash.com/photo-1542362567-b07e54358753?q=80&w=900&auto=format&fit=crop' },
        { alt: 'Llantas y rines', src: 'https://images.unsplash.com/photo-1517940310602-75f39d4ac6fb?q=80&w=900&auto=format&fit=crop' },
        { alt: 'Frenos y discos', src: 'https://images.unsplash.com/photo-1526045478516-99145907023c?q=80&w=900&auto=format&fit=crop' },
        { alt: 'Transmisión y cadenas', src: 'https://images.unsplash.com/photo-1602320734573-1b57f5813996?q=80&w=900&auto=format&fit=crop' },
        { alt: 'Aceites y lubricantes', src: 'https://images.unsplash.com/photo-1589578527966-1e9b2ae05a0e?q=80&w=900&auto=format&fit=crop' },
        { alt: 'Iluminación/eléctricos', src: 'https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?q=80&w=900&auto=format&fit=crop' },
    ];

    const imagesHtml = fallbackImages.map(image => `
        <figure class="group relative aspect-[4/3] rounded-xl overflow-hidden ring-1 ring-white/10 bg-white/5">
            <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                 src="${image.src}" alt="${image.alt}">
            <figcaption class="absolute inset-x-0 bottom-0 p-2 text-[11px] font-medium text-white/95 bg-gradient-to-t from-black/60 to-transparent">
                ${image.alt}
            </figcaption>
        </figure>
    `).join('');

    container.innerHTML = imagesHtml;
}

import { useEffect, useState } from 'react'
import { Outlet } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import AppShell from './AppShell'

type HeroImage = {
  id: string
  urls: { regular: string }
  alt_description: string
}

const HERO_IMAGES: HeroImage[] = [
  {
    id: '1',
    urls: { regular: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=1200&q=80' },
    alt_description: 'Motociclista en carretera',
  },
  {
    id: '2',
    urls: { regular: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=1200&q=80' },
    alt_description: 'Moto deportiva',
  },
  {
    id: '3',
    urls: { regular: 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=1200&q=80' },
    alt_description: 'Motociclista en ruta',
  },
  {
    id: '4',
    urls: { regular: 'https://images.unsplash.com/photo-1580310614729-ccd69652491d?w=1200&q=80' },
    alt_description: 'Moto custom',
  },
  {
    id: '5',
    urls: { regular: 'https://images.unsplash.com/photo-1449426468159-d96dbf08f19f?w=1200&q=80' },
    alt_description: 'Moto clasica',
  },
]

export default function AuthLayout() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0)

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % HERO_IMAGES.length)
    }, 5000)

    return () => clearInterval(interval)
  }, [])

  return (
    <AppShell variant="auth" containerClassName="max-w-5xl" mainClassName="py-2 sm:py-3">
      <div className="max-h-[90vh] overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-[0_4px_24px_rgba(0,0,0,0.08)]">
        <div className="grid min-h-[530px] grid-cols-1 lg:grid-cols-2">
          <div className="relative hidden overflow-hidden bg-slate-900 lg:block">
            <AnimatePresence mode="sync" initial={false}>
              <motion.div
                key={currentImageIndex}
                initial={{ opacity: 0, scale: 1.1 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.95 }}
                transition={{ duration: 1.5, ease: 'easeInOut' }}
                className="absolute inset-0"
              >
                <img
                  src={HERO_IMAGES[currentImageIndex].urls.regular}
                  alt={HERO_IMAGES[currentImageIndex].alt_description}
                  className="h-full w-full object-cover"
                  loading="eager"
                  decoding="async"
                />
                <div className="absolute inset-0 bg-gradient-to-br from-slate-900/90 via-slate-900/75 to-comercioplus-900/80" />
              </motion.div>
            </AnimatePresence>

            <div className="relative z-10 flex h-full flex-col justify-center p-8 xl:p-10">
              <div className="max-w-md">
                <h2 className="text-[44px] font-bold leading-tight text-white">Bienvenido a ComercioPlus</h2>
                <p className="mt-3 text-[16px] leading-[1.55] text-white/90">
                  Conecta tu negocio con miles de clientes. Gestiona tu tienda online de manera simple y efectiva.
                </p>

                <div className="mt-6 flex gap-2">
                  {HERO_IMAGES.map((_, index) => (
                    <button
                      key={index}
                      onClick={() => setCurrentImageIndex(index)}
                      className={`h-2 rounded-full transition-all duration-300 ${
                        index === currentImageIndex ? 'w-8 bg-comercioplus-500' : 'w-2 bg-white/30 hover:bg-white/50'
                      }`}
                      aria-label={`Ver imagen ${index + 1}`}
                    />
                  ))}
                </div>
              </div>
            </div>
          </div>

          <div className="flex items-center p-4 sm:p-5 lg:p-6">
            <div className="w-full">
              <Outlet />
            </div>
          </div>
        </div>
      </div>
    </AppShell>
  )
}

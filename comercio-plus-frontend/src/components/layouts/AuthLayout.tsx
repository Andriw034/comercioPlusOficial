import { useEffect, useState } from 'react'
import { Outlet } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import AppShell from './AppShell'
import { MOTO_HERO_IMAGES } from '@/constants/motoHeroImages'

export default function AuthLayout() {
  const [currentImageIndex, setCurrentImageIndex] = useState(0)

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prev) => (prev + 1) % MOTO_HERO_IMAGES.length)
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
                  src={MOTO_HERO_IMAGES[currentImageIndex].url}
                  alt={MOTO_HERO_IMAGES[currentImageIndex].alt}
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
                  {MOTO_HERO_IMAGES.map((_, index) => (
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

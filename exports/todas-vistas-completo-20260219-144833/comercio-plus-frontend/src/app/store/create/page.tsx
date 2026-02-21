import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import AppShell from '@/components/layouts/AppShell'
import GlassCard from '@/components/ui/GlassCard'

export default function CreateStore() {
  const navigate = useNavigate()

  useEffect(() => {
    navigate('/dashboard/store', { replace: true })
  }, [navigate])

  return (
    <AppShell variant="dashboard">
      <GlassCard className="text-center text-white/70">Redirigiendo al gestor de tienda...</GlassCard>
    </AppShell>
  )
}

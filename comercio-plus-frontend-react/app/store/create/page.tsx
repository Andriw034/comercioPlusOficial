import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'

export default function CreateStore() {
  const navigate = useNavigate()

  useEffect(() => {
    navigate('/dashboard/store', { replace: true })
  }, [navigate])

  return (
    <div className="min-h-screen p-8 text-white">
      <p>Redirigiendo al gestor de tienda...</p>
    </div>
  )
}


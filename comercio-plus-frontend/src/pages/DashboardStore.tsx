import { useState } from 'react'
import Button from '@/components/Button'
import Card from '@/components/Card'
import Header from '@/components/Header'
import Input from '@/components/Input'
import Sidebar from '@/components/Sidebar'
import Badge from '@/components/Badge'

export default function DashboardStore() {
  const [isEditing, setIsEditing] = useState(false)
  const [storeData, setStoreData] = useState({
    name: 'Artesanías del Valle',
    description: 'Productos artesanales hechos a mano con materiales naturales.',
    email: 'contacto@artesanias.com',
    phone: '+56 9 9999 9999',
    address: 'Santiago, Chile',
    isVisible: true,
    status: 'active',
  })

  const stats = {
    customers: 284,
    avgRating: 4.8,
    totalProducts: 156,
    activeProducts: 142,
  }

  return (
    <div className="min-h-screen bg-dark-50">
      <Header showAuth={false} />

      <main className="grid grid-cols-1 lg:grid-cols-[256px_1fr]">
        <Sidebar
          items={[
            { icon: '📦', label: 'Productos', href: '/dashboard/products' },
            { icon: '👥', label: 'Clientes', href: '/dashboard/customers' },
            { icon: '⚙️', label: 'Configuración', href: '/dashboard/store' },
          ]}
        />

        <div className="p-6 lg:p-10 space-y-6">
          <div className="flex flex-wrap justify-between items-center gap-3">
            <h1 className="text-h1">Panel de Tienda</h1>
            <Button
              variant={isEditing ? 'secondary' : 'primary'}
              onClick={() => setIsEditing((current) => !current)}
            >
              {isEditing ? 'Guardar Cambios' : 'Editar Tienda'}
            </Button>
          </div>

          <div className="grid grid-cols-1 xl:grid-cols-[1fr_360px] gap-6">
            <Card>
              <h2 className="text-h2 mb-6">Información General</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input
                  label="Nombre de la Tienda"
                  value={storeData.name}
                  onChange={(event) => setStoreData({ ...storeData, name: event.target.value })}
                  disabled={!isEditing}
                  fullWidth
                />
                <Input
                  label="Email"
                  value={storeData.email}
                  onChange={(event) => setStoreData({ ...storeData, email: event.target.value })}
                  disabled={!isEditing}
                  fullWidth
                />
                <Input
                  label="Teléfono"
                  value={storeData.phone}
                  onChange={(event) => setStoreData({ ...storeData, phone: event.target.value })}
                  disabled={!isEditing}
                  fullWidth
                />
                <Input
                  label="Dirección"
                  value={storeData.address}
                  onChange={(event) => setStoreData({ ...storeData, address: event.target.value })}
                  disabled={!isEditing}
                  fullWidth
                />
              </div>
              <div className="mt-4">
                <label className="block text-body-sm font-medium text-dark-800 mb-2">Descripción</label>
                <textarea
                  className="textarea-dark w-full"
                  rows={4}
                  value={storeData.description}
                  onChange={(event) => setStoreData({ ...storeData, description: event.target.value })}
                  disabled={!isEditing}
                />
              </div>
            </Card>

            <div className="space-y-6">
              <Card>
                <h3 className="text-h3 mb-4">Estado de la Tienda</h3>
                <div className="space-y-3">
                  <div className="flex items-center justify-between">
                    <span className="text-body text-dark-700">Visibilidad</span>
                    <Badge variant={storeData.isVisible ? 'success' : 'warning'}>
                      {storeData.isVisible ? 'Visible' : 'Oculta'}
                    </Badge>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="text-body text-dark-700">Estado</span>
                    <Badge variant={storeData.status === 'active' ? 'success' : 'danger'}>
                      {storeData.status === 'active' ? 'Activa' : 'Inactiva'}
                    </Badge>
                  </div>
                </div>
              </Card>

              <Card>
                <h3 className="text-h3 mb-4">Estadísticas</h3>
                <div className="space-y-2 text-body">
                  <p>Clientes: <strong>{stats.customers}</strong></p>
                  <p>Valoración: <strong>{stats.avgRating}</strong></p>
                  <p>Productos activos: <strong>{stats.activeProducts}/{stats.totalProducts}</strong></p>
                </div>
              </Card>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}

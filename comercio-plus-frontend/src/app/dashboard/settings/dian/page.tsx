import { useState, useEffect } from 'react'
import API from '@/lib/api'
import { getApiPayload } from '@/lib/apiPayload'
import { ErpPageHeader, ErpBtn } from '@/components/erp'
import Input from '@/components/ui/Input'
import Select from '@/components/ui/Select'
import Switch from '@/components/ui/Switch'

interface DianConfig {
  dian_enabled: boolean
  dian_nit: string | null
  dian_business_name: string | null
  dian_provider: string | null
  dian_enabled_at: string | null
}

const PROVIDERS = [
  { value: '', label: 'Seleccionar proveedor...' },
  { value: 'saphety', label: 'Saphety' },
  { value: 'carvajal', label: 'Carvajal' },
  { value: 'factory', label: 'The Factory HKA' },
  { value: 'alegra', label: 'Alegra' },
  { value: 'other', label: 'Otro' },
]

const DEFAULT_CONFIG: DianConfig = {
  dian_enabled: false,
  dian_nit: null,
  dian_business_name: null,
  dian_provider: null,
  dian_enabled_at: null,
}

export default function DianSettingsPage() {
  const [config, setConfig] = useState<DianConfig>(DEFAULT_CONFIG)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null)

  // Form fields for enabling
  const [nit, setNit] = useState('')
  const [businessName, setBusinessName] = useState('')
  const [provider, setProvider] = useState('')

  useEffect(() => {
    const fetchConfig = async () => {
      try {
        const res = await API.get('/api/merchant/dian-config')
        const data = getApiPayload<DianConfig>(res, DEFAULT_CONFIG)
        setConfig(data)
        setNit(data.dian_nit ?? '')
        setBusinessName(data.dian_business_name ?? '')
        setProvider(data.dian_provider ?? '')
      } catch {
        setMessage({ type: 'error', text: 'Error al cargar configuración DIAN' })
      } finally {
        setLoading(false)
      }
    }
    fetchConfig()
  }, [])

  const handleEnable = async () => {
    if (!nit || !businessName || !provider) {
      setMessage({ type: 'error', text: 'Completa todos los campos obligatorios' })
      return
    }
    setSaving(true)
    setMessage(null)
    try {
      const res = await API.post('/api/merchant/dian-config/enable', {
        nit,
        business_name: businessName,
        provider,
      })
      const msg = res.data?.message ?? 'DIAN habilitado'
      setConfig(prev => ({ ...prev, dian_enabled: true, dian_nit: nit, dian_business_name: businessName, dian_provider: provider, dian_enabled_at: new Date().toISOString() }))
      setMessage({ type: 'success', text: msg })
    } catch {
      setMessage({ type: 'error', text: 'Error al habilitar DIAN' })
    } finally {
      setSaving(false)
    }
  }

  const handleDisable = async () => {
    setSaving(true)
    setMessage(null)
    try {
      await API.post('/api/merchant/dian-config/disable')
      setConfig(prev => ({ ...prev, dian_enabled: false }))
      setMessage({ type: 'success', text: 'Facturación DIAN deshabilitada' })
    } catch {
      setMessage({ type: 'error', text: 'Error al deshabilitar DIAN' })
    } finally {
      setSaving(false)
    }
  }

  if (loading) {
    return (
      <div className="p-6 flex items-center justify-center min-h-[300px]">
        <div className="animate-spin h-8 w-8 border-4 border-comercioplus-600 border-t-transparent rounded-full" />
      </div>
    )
  }

  return (
    <div className="p-6 max-w-2xl">
      <ErpPageHeader
        breadcrumb="Dashboard > Configuración > DIAN"
        title="Facturación Electrónica DIAN"
        subtitle="Configura si tu tienda emite facturas electrónicas ante la DIAN"
      />

      {message && (
        <div className={`mt-4 p-3 rounded-lg text-sm ${message.type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {/* Toggle principal */}
      <div className="mt-6 bg-white rounded-xl border border-slate-200 p-6">
        <div className="flex items-center justify-between">
          <div>
            <h3 className="text-lg font-semibold text-slate-900">Facturación DIAN</h3>
            <p className="text-sm text-slate-500 mt-1">
              {config.dian_enabled
                ? 'Tu tienda emite facturas electrónicas validadas por la DIAN'
                : 'Tu tienda usa comprobantes internos (sin validación DIAN)'}
            </p>
          </div>
          <Switch
            checked={config.dian_enabled}
            onCheckedChange={(next) => {
              if (!next) handleDisable()
            }}
            disabled={saving}
          />
        </div>

        {config.dian_enabled && config.dian_enabled_at && (
          <p className="text-xs text-slate-400 mt-2">
            Habilitado desde: {new Date(config.dian_enabled_at).toLocaleDateString('es-CO')}
          </p>
        )}
      </div>

      {/* Info para comerciantes sin DIAN */}
      {!config.dian_enabled && (
        <div className="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-5">
          <h4 className="font-semibold text-amber-900">No necesitas DIAN para vender</h4>
          <p className="text-sm text-amber-800 mt-2">
            Si tu negocio es informal o no estás obligado a facturar electrónicamente,
            puedes seguir vendiendo normalmente con comprobantes de venta internos.
          </p>
          <p className="text-sm text-amber-800 mt-2">
            Solo activa la facturación DIAN si tu negocio está formalizado ante la DIAN
            y tienes un proveedor tecnológico autorizado.
          </p>
        </div>
      )}

      {/* Formulario para activar DIAN */}
      {!config.dian_enabled && (
        <div className="mt-6 bg-white rounded-xl border border-slate-200 p-6">
          <h3 className="text-base font-semibold text-slate-900 mb-4">Activar facturación DIAN</h3>

          <div className="space-y-4">
            <Input
              label="NIT de la empresa"
              placeholder="901234567-8"
              value={nit}
              onChange={e => setNit(e.target.value)}
            />

            <Input
              label="Razón social"
              placeholder="Mi Negocio de Repuestos S.A.S."
              value={businessName}
              onChange={e => setBusinessName(e.target.value)}
            />

            <Select
              label="Proveedor tecnológico"
              value={provider}
              onChange={e => setProvider(e.target.value)}
            >
              {PROVIDERS.map(p => (
                <option key={p.value} value={p.value}>{p.label}</option>
              ))}
            </Select>
          </div>

          <div className="mt-6">
            <ErpBtn
              variant="primary"
              onClick={handleEnable}
              disabled={saving || !nit || !businessName || !provider}
            >
              {saving ? 'Guardando...' : 'Activar facturación DIAN'}
            </ErpBtn>
          </div>
        </div>
      )}

      {/* Resumen cuando DIAN está activo */}
      {config.dian_enabled && (
        <div className="mt-6 bg-white rounded-xl border border-slate-200 p-6">
          <h3 className="text-base font-semibold text-slate-900 mb-4">Configuración activa</h3>
          <dl className="space-y-3">
            <div className="flex justify-between">
              <dt className="text-sm text-slate-500">NIT</dt>
              <dd className="text-sm font-medium text-slate-900">{config.dian_nit}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-sm text-slate-500">Razón social</dt>
              <dd className="text-sm font-medium text-slate-900">{config.dian_business_name}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-sm text-slate-500">Proveedor</dt>
              <dd className="text-sm font-medium text-slate-900 capitalize">{config.dian_provider}</dd>
            </div>
          </dl>
        </div>
      )}
    </div>
  )
}

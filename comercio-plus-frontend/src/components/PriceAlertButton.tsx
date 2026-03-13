import { useEffect, useMemo, useState } from 'react'
import API from '@/lib/api'

interface PriceAlertButtonProps {
  productId: number
  currentPrice: number
}

type MineResponse = {
  following?: boolean
}

const readToken = () => localStorage.getItem('token') || sessionStorage.getItem('token')

export default function PriceAlertButton({ productId, currentPrice }: PriceAlertButtonProps) {
  const [following, setFollowing] = useState(false)
  const [loading, setLoading] = useState(true)
  const [targetPrice, setTargetPrice] = useState<string>(String(Math.max(1, Math.floor(currentPrice * 0.9))))
  const [showForm, setShowForm] = useState(false)
  const [saving, setSaving] = useState(false)
  const token = useMemo(() => readToken(), [])

  useEffect(() => {
    const check = async () => {
      if (!token) {
        setLoading(false)
        return
      }

      try {
        const { data } = await API.get<MineResponse>(`/products/${productId}/alerts/mine`, {
          params: { _t: Date.now() },
        })
        setFollowing(Boolean(data?.following))
      } catch {
        setFollowing(false)
      } finally {
        setLoading(false)
      }
    }

    void check()
  }, [productId, token])

  const handleCreate = async () => {
    const parsed = Number(targetPrice)
    if (!Number.isFinite(parsed) || parsed <= 0) return

    setSaving(true)
    try {
      await API.post(`/products/${productId}/alerts`, { target_price: parsed })
      setFollowing(true)
      setShowForm(false)
    } catch {
      // Puede fallar si el usuario no esta autenticado.
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async () => {
    setSaving(true)
    try {
      await API.delete(`/products/${productId}/alerts`)
      setFollowing(false)
    } catch {
      // No-op
    } finally {
      setSaving(false)
    }
  }

  if (loading || !token) return null

  if (following) {
    return (
      <button
        type="button"
        onClick={handleDelete}
        disabled={saving}
        className="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[12px] font-semibold text-amber-700 transition hover:bg-amber-100 disabled:opacity-50 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300"
      >
        🔔 Siguiendo precio
      </button>
    )
  }

  return (
    <div className="flex flex-col gap-2">
      {showForm ? (
        <div className="flex flex-wrap items-center gap-2">
          <input
            type="number"
            value={targetPrice}
            onChange={(event) => setTargetPrice(event.target.value)}
            min={1}
            placeholder="Precio objetivo COP"
            className="w-36 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[13px] text-slate-900 focus:border-orange-400 focus:outline-none dark:border-white/20 dark:bg-white/5 dark:text-white"
          />
          <button
            type="button"
            onClick={handleCreate}
            disabled={saving}
            className="rounded-lg bg-orange-500 px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-orange-600 disabled:opacity-50"
          >
            Guardar
          </button>
          <button
            type="button"
            onClick={() => setShowForm(false)}
            className="text-[12px] text-slate-500 hover:text-slate-700 dark:text-white/60 dark:hover:text-white/80"
          >
            Cancelar
          </button>
        </div>
      ) : (
        <button
          type="button"
          onClick={() => setShowForm(true)}
          className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-[12px] font-semibold text-slate-600 transition hover:border-orange-300 hover:text-orange-600 dark:border-white/20 dark:text-white/60"
        >
          🔔 Seguir precio
        </button>
      )}
    </div>
  )
}

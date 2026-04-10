interface LowStockAlertProps {
  productName: string
  stock: number
  threshold?: number
}

export function LowStockAlert({ productName, stock, threshold = 5 }: LowStockAlertProps) {
  if (stock >= threshold) return null

  const isCritical = stock === 0

  return (
    <div
      className={`flex items-center gap-3 rounded-lg border px-4 py-3 text-sm ${
        isCritical
          ? 'border-red-200 bg-red-50 text-red-800'
          : 'border-amber-200 bg-amber-50 text-amber-800'
      }`}
    >
      <span className="text-lg">{isCritical ? '!' : '!'}</span>
      <div>
        <p className="font-semibold">
          {isCritical ? 'Sin stock' : 'Stock bajo'}
        </p>
        <p className="text-xs opacity-75">
          {productName} — {stock} {stock === 1 ? 'unidad disponible' : 'unidades disponibles'}
        </p>
      </div>
    </div>
  )
}

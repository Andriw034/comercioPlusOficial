import type { FC } from 'react'
import Card from '@/components/Card'
import type { Store } from '@/types'

interface StoreCardProps extends Store {
  onClick?: () => void
}

const coverGradients = [
  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
]

const StoreCard: FC<StoreCardProps> = ({
  name,
  description,
  logo,
  cover,
  rating = 4.8,
  productCount = 0,
  onClick,
}) => {
  const randomCoverGradient = coverGradients[Math.floor(Math.random() * coverGradients.length)]

  return (
    <Card hoverable padding="none" onClick={onClick} className="overflow-hidden">
      <div className="relative">
        <div
          className="h-40"
          style={{
            background: cover ? `url(${cover})` : randomCoverGradient,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
        <div
          className="absolute bottom-0 left-6 transform translate-y-1/2 w-20 h-20 bg-white rounded-md border-4 border-white shadow-lg"
          style={{
            background: logo ? `url(${logo})` : '#999',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
      </div>

      <div className="pt-12 px-6 pb-6">
        <h3 className="text-lg font-bold text-dark-900 mb-2">
          {name}
        </h3>
        <p className="text-body-sm text-dark-600 mb-4 line-clamp-2">
          {description}
        </p>
        <div className="flex items-center gap-4 text-caption text-dark-600">
          <span>⭐ {rating}</span>
          <span>•</span>
          <span>{productCount} productos</span>
        </div>
      </div>
    </Card>
  )
}

export default StoreCard

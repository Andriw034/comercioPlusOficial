import type { FC } from 'react'
import Button from '@/components/Button'
import Card from '@/components/Card'
import type { Product } from '@/types'

interface ProductCardProps extends Product {
  onAddToCart?: () => void
  onClick?: () => void
}

const gradients = [
  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
  'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
]

const ProductCard: FC<ProductCardProps> = ({
  name,
  price,
  image,
  stock,
  onAddToCart,
  onClick,
}) => {
  const randomGradient = gradients[Math.floor(Math.random() * gradients.length)]

  return (
    <Card hoverable padding="none" onClick={onClick} className="overflow-hidden">
      <div
        className="h-48"
        style={{
          background: image ? `url(${image})` : randomGradient,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
        }}
      />
      <div className="p-5">
        <h3 className="text-lg font-semibold text-dark-900 mb-2 truncate">
          {name}
        </h3>
        <p className="text-xl font-bold text-primary mb-3">
          ${price.toLocaleString('es-CL')}
        </p>
        {stock !== undefined && (
          <p className="text-body-sm text-dark-600 mb-4">
            Stock: {stock} unidades
          </p>
        )}
        {onAddToCart && (
          <Button
            variant="primary"
            fullWidth
            size="sm"
            onClick={(event) => {
              event.stopPropagation()
              onAddToCart()
            }}
          >
            Agregar al Carrito
          </Button>
        )}
      </div>
    </Card>
  )
}

export default ProductCard

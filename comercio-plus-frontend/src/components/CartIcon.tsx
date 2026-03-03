import { Link } from 'react-router-dom'
import { Icon } from '@/components/Icon'
import { useCart } from '@/context/CartContext'

export default function CartIcon() {
  const { totalItems } = useCart()
  const count = Math.max(0, totalItems)

  return (
    <Link
      to="/cart"
      style={{
        position: 'relative',
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '32px',
        height: '32px',
        borderRadius: '8px',
        color: '#374151',
        textDecoration: 'none',
        border: '1px solid #e5e7eb',
        background: '#fff',
      }}
      aria-label="Ver carrito"
      title="Ver carrito"
    >
      <Icon name="cart" size={16} />
      {count > 0 ? (
        <span
          style={{
            position: 'absolute',
            top: '-7px',
            right: '-7px',
            minWidth: '18px',
            height: '18px',
            padding: '0 4px',
            borderRadius: '999px',
            background: '#EA580C',
            color: '#fff',
            fontSize: '10px',
            fontWeight: 700,
            lineHeight: '18px',
            textAlign: 'center',
            border: '2px solid #fff',
          }}
        >
          {count > 99 ? '99+' : count}
        </span>
      ) : null}
    </Link>
  )
}

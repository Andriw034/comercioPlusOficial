export interface Product {
  id: string
  name: string
  price: number
  image?: string
  stock?: number
  category?: string
  description?: string
}

export interface Store {
  id: string
  name: string
  description: string
  logo?: string
  cover?: string
  rating?: number
  productCount?: number
  slug?: string
}

export interface NavLink {
  label: string
  href: string
  active?: boolean
}

export interface SidebarItem {
  icon: string
  label: string
  href: string
}

export type ButtonVariant = 'primary' | 'secondary' | 'outline' | 'danger'
export type ButtonSize = 'sm' | 'md' | 'lg'
export type BadgeVariant = 'success' | 'warning' | 'danger' | 'info'
export type CardPadding = 'none' | 'sm' | 'md' | 'lg'

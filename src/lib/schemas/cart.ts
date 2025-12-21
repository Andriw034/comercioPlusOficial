import { z } from 'zod'

export const CartProductSchema = z.object({
  id: z.string(),
  productId: z.string(),
  quantity: z.number().positive(),
  unitPrice: z.number().positive(),
})

export type CartProduct = z.infer<typeof CartProductSchema>;

export const CartSchema = z.object({
  id: z.string(),
  userId: z.string(),
  status: z.enum(['active', 'completed']),
  createdAt: z.date(),
  updatedAt: z.date(),
  products: z.array(CartProductSchema).default([]),
})

export type Cart = z.infer<typeof CartSchema>;

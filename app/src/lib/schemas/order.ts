import { z } from 'zod';

export const OrderProductSchema = z.object({
  id: z.string(),
  productId: z.string(),
  quantity: z.number().positive(),
  unitPrice: z.number().positive(),
});

export type OrderProduct = z.infer<typeof OrderProductSchema>;

export const OrderSchema = z.object({
  id: z.string(),
  userId: z.string(),
  total: z.number(),
  date: z.date(),
  paymentMethod: z.string(),
  products: z.array(OrderProductSchema),
  createdAt: z.date(),
  updatedAt: z.date(),
});

export type Order = z.infer<typeof OrderSchema>;

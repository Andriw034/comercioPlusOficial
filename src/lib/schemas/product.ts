import { z } from 'zod';

export const RatingSchema = z.object({
  id: z.string(),
  userId: z.string(),
  rating: z.number().min(1).max(5),
  comment: z.string().optional().nullable(),
  createdAt: z.date(),
});

export type Rating = z.infer<typeof RatingSchema>;

export const ProductSchema = z.object({
  id: z.string(),
  name: z.string(),
  description: z.string().optional().nullable(),
  price: z.number().positive(),
  stock: z.number().int().nonnegative(),
  image: z.string().url().optional().nullable(),
  categoryId: z.string(),
  storeId: z.string(),
  userId: z.string(),
  offer: z.boolean().default(false),
  averageRating: z.number().min(0).max(5).default(0),
  ratings: z.array(RatingSchema).default([]),
  createdAt: z.date().optional(),
  updatedAt: z.date().optional(),
});

export type Product = z.infer<typeof ProductSchema>;

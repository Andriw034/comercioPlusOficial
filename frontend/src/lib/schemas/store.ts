import { z } from 'zod';

export const StoreSchema = z.object({
  id: z.string(),
  userId: z.string(),
  name: z.string().min(3, "El nombre debe tener al menos 3 caracteres."),
  slug: z.string().min(3, "El slug debe tener al menos 3 caracteres.").regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, "Slug inválido. Solo letras minúsculas, números y guiones."),
  logo: z.string().url().optional().nullable(),
  cover: z.string().url().optional().nullable(),
  description: z.string().optional().nullable(),
  address: z.string().min(5, "La dirección es requerida."),
  phone: z.string().optional().nullable(),
  status: z.enum(['active', 'inactive']).default('active'),
  openingHours: z.string().optional().nullable(),
  mainCategory: z.string(),
  averageRating: z.number().min(0).max(5).default(0),
  theme: z.object({
    primaryColor: z.string().default('#FFA14F'),
    backgroundColor: z.string().optional().nullable(),
  }).optional(),
  createdAt: z.date(),
  updatedAt: z.date(),
});

export type Store = z.infer<typeof StoreSchema>;

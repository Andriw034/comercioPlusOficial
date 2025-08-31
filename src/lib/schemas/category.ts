
import { z } from 'zod';

export const CategorySchema = z.object({
  id: z.string(),
  name: z.string(),
  slug: z.string(),
  description: z.string().optional().nullable(),
});

export type Category = z.infer<typeof CategorySchema>;

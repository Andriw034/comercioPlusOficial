import { z } from 'zod';

export const CategorySchema = z.object({
  id: z.string(),
  name: z.string(),
  parentId: z.string().optional().nullable(),
});

export type Category = z.infer<typeof CategorySchema>;

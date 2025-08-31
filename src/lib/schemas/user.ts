import { z } from 'zod';

export const UserRoleSchema = z.enum([
  'Administrador', 
  'Comerciante', 
  'Cliente'
]);

export const UserProfileSchema = z.object({
  username: z.string(),
  image: z.string().url().optional().nullable(),
  birthdate: z.date().optional().nullable(),
  otherInfo: z.string().optional().nullable(),
});

export const UserSchema = z.object({
  id: z.string(),
  name: z.string(),
  email: z.string().email(),
  role: UserRoleSchema.default('Cliente'),
  phone: z.string().optional().nullable(),
  avatar: z.string().url().optional().nullable(),
  status: z.boolean().default(true),
  address: z.string().optional().nullable(),
  profile: UserProfileSchema.optional(),
  createdAt: z.date(),
  updatedAt: z.date(),
});

export type User = z.infer<typeof UserSchema>;
export type UserRole = z.infer<typeof UserRoleSchema>;
export type UserProfile = z.infer<typeof UserProfileSchema>;

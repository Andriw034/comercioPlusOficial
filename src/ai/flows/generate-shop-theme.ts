'use server'

import { ai } from '@/ai/genkit'
import { z } from 'genkit'

const GenerateShopThemeInputSchema = z.object({
  shopName: z.string().describe('The name of the shop.'),
  // Acepta URL o data URI
  logoDataUri: z.string().describe('URL o Data URI del logo.'),
  coverImageDataUri: z.string().describe('URL o Data URI de la portada.'),
})
export type GenerateShopThemeInput = z.infer<typeof GenerateShopThemeInputSchema>;

const GenerateShopThemeOutputSchema = z.object({
  primaryColor: z.string().describe('Primary color (hex).'),
  secondaryColor: z.string().describe('Secondary color (hex).'),
  backgroundColor: z.string().describe('Background color (hex).'),
  textColor: z.string().describe('Text color (hex).'),
})
export type GenerateShopThemeOutput = z.infer<typeof GenerateShopThemeOutputSchema>;

export async function generateShopTheme(input: GenerateShopThemeInput): Promise<GenerateShopThemeOutput> {
  return generateShopThemeFlow(input)
}

const prompt = ai.definePrompt({
  name: 'generateShopThemePrompt',
  input: { schema: GenerateShopThemeInputSchema },
  output: { schema: GenerateShopThemeOutputSchema },
  prompt: `You are an expert in branding and color palettes. Given the shop name, logo and cover image, generate a color palette (hex).
Return: primaryColor, secondaryColor, backgroundColor, textColor.

Shop Name: {{{shopName}}}
Logo: {{media url=logoDataUri}}
Cover: {{media url=coverImageDataUri}}`,
})

const generateShopThemeFlow = ai.defineFlow(
  {
    name: 'generateShopThemeFlow',
    inputSchema: GenerateShopThemeInputSchema,
    outputSchema: GenerateShopThemeOutputSchema,
  },
  async (input) => {
    const { output } = await prompt(input)
    return output!
  }
)

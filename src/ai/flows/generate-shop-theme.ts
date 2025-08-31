'use server';

/**
 * @fileOverview Generates a color palette for a shop based on its logo, cover image, and name.
 *
 * - generateShopTheme - A function that handles the theme generation process.
 * - GenerateShopThemeInput - The input type for the generateShopTheme function.
 * - GenerateShopThemeOutput - The return type for the generateShopTheme function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const GenerateShopThemeInputSchema = z.object({
  shopName: z.string().describe('The name of the shop.'),
  logoDataUri: z
    .string()
    .describe(
      "A photo of the shop logo, as a data URI that must include a MIME type and use Base64 encoding. Expected format: 'data:<mimetype>;base64,<encoded_data>'."
    ),
  coverImageDataUri: z
    .string()
    .describe(
      "A photo of the shop cover image, as a data URI that must include a MIME type and use Base64 encoding. Expected format: 'data:<mimetype>;base64,<encoded_data>'."
    ),
});
export type GenerateShopThemeInput = z.infer<typeof GenerateShopThemeInputSchema>;

const GenerateShopThemeOutputSchema = z.object({
  primaryColor: z.string().describe('The primary color for the shop theme (hex code).'),
  secondaryColor: z.string().describe('The secondary color for the shop theme (hex code).'),
  backgroundColor: z.string().describe('The background color for the shop theme (hex code).'),
  textColor: z.string().describe('The text color for the shop theme (hex code).'),
});
export type GenerateShopThemeOutput = z.infer<typeof GenerateShopThemeOutputSchema>;

export async function generateShopTheme(input: GenerateShopThemeInput): Promise<GenerateShopThemeOutput> {
  return generateShopThemeFlow(input);
}

const prompt = ai.definePrompt({
  name: 'generateShopThemePrompt',
  input: {schema: GenerateShopThemeInputSchema},
  output: {schema: GenerateShopThemeOutputSchema},
  prompt: `You are an expert in branding and color palettes. Given the following shop name, logo and cover image, generate a color palette consisting of a primary color, secondary color, background color and text color. Return the colors as hex codes.

Shop Name: {{{shopName}}}
Logo: {{media url=logoDataUri}}
Cover Image: {{media url=coverImageDataUri}}`,
});

const generateShopThemeFlow = ai.defineFlow(
  {
    name: 'generateShopThemeFlow',
    inputSchema: GenerateShopThemeInputSchema,
    outputSchema: GenerateShopThemeOutputSchema,
  },
  async input => {
    const {output} = await prompt(input);
    return output!;
  }
);

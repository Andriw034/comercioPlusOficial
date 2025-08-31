import { genkit } from 'genkit';
import { googleAI } from '@genkit-ai/googleai';

export const ai = genkit({
  // Usamos tu API key desde .env para evitar el 401
  plugins: [googleAI({ apiKey: process.env.GEMINI_API_KEY! })],
  // Puedes usar pro o flash. Te dejo pro porque es el que estabas llamando.
  model: 'googleai/gemini-2.5-pro',
});

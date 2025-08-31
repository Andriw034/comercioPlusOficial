"use client";

import { useState } from "react";
import { generateShopTheme, GenerateShopThemeOutput } from "@/ai/flows/generate-shop-theme";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Wand2 } from "lucide-react";
import { useToast } from "@/hooks/use-toast";

const initialState: GenerateShopThemeOutput = {
  primaryColor: "#FF6A2E",
  secondaryColor: "#FF9156",
  backgroundColor: "#FFF7F2",
  textColor: "#0F172A",
};

export function AIThemeGenerator() {
  const [colors, setColors] = useState<GenerateShopThemeOutput>(initialState);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [coverPreview, setCoverPreview] = useState<string | null>(null);
  const [shopName, setShopName] = useState("Moto Repuestos Pro");
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const handleFileChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    setPreview: (url: string | null) => void
  ) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    } else {
      setPreview(null);
    }
  };

  const handleGenerateTheme = async () => {
    if (!logoPreview || !coverPreview || !shopName) {
      toast({
        title: "Faltan datos",
        description: "Por favor, proporciona nombre de la tienda, logo y portada.",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);

    try {
      const result = await generateShopTheme({
        shopName,
        logoDataUri: logoPreview,
        coverImageDataUri: coverPreview,
      });
      setColors(result);
      toast({
        title: "¡Tema generado!",
        description: "La paleta de colores ha sido actualizada.",
      });
    } catch (error) {
      console.error("Error generating theme:", error);
      toast({
        title: "Error al generar el tema",
        description: "Hubo un problema con la IA. Inténtalo de nuevo.",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Branding con IA</CardTitle>
        <CardDescription>Genera una paleta de colores para tu tienda usando IA, basada en tu logo y portada.</CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <div className="space-y-4">
          <div>
            <Label htmlFor="shopNameForAI">Nombre de la Tienda</Label>
            <Input id="shopNameForAI" name="shopName" value={shopName} onChange={(e) => setShopName(e.target.value)} required />
          </div>
          <div>
            <Label htmlFor="logoForAI">Logo</Label>
            <Input id="logoForAI" name="logo" type="file" accept="image/*" required onChange={(e) => handleFileChange(e, setLogoPreview)} />
          </div>
          <div>
            <Label htmlFor="coverForAI">Imagen de Portada</Label>
            <Input id="coverForAI" name="cover" type="file" accept="image/*" required onChange={(e) => handleFileChange(e, setCoverPreview)} />
          </div>
          <Button onClick={handleGenerateTheme} disabled={loading} className="w-full">
            <Wand2 className="mr-2 h-4 w-4" />
            {loading ? "Generando..." : "Generar Tema con IA"}
          </Button>
        </div>

        <div className="space-y-4 pt-4 border-t">
            <h4 className="font-semibold">Paleta de Colores</h4>
            <div className="grid grid-cols-2 gap-4">
                {Object.entries(colors).map(([key, value]) => (
                    <div key={key} className="space-y-2">
                        <Label>{key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}</Label>
                        <div className="flex items-center gap-2">
                            <Input type="color" value={value} onChange={(e) => setColors(prev => ({...prev, [key]: e.target.value}))} className="p-1 h-10 w-12"/>
                            <Input value={value} onChange={(e) => setColors(prev => ({...prev, [key]: e.target.value}))} />
                        </div>
                    </div>
                ))}
            </div>
        </div>

        <div className="space-y-4 pt-4 border-t">
          <h4 className="font-semibold">Vista Previa</h4>
          <div 
            className="rounded-lg p-4 border"
            style={{ backgroundColor: colors.backgroundColor, color: colors.textColor }}
          >
            <h5 className="font-bold text-lg">Producto de Muestra</h5>
            <p className="text-sm opacity-80">Una descripción breve del producto.</p>
            <Button 
              className="mt-4"
              style={{ backgroundColor: colors.primaryColor, color: colors.textColor }}
            >
              Botón Principal
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

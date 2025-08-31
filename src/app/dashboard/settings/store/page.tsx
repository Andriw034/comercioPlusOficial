import { AIThemeGenerator } from "@/components/dashboard/ai-theme-generator";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";

export default function StoreSettingsPage() {
  return (
    <div className="flex-1 space-y-4 p-4 md:p-8 pt-6">
      <div className="flex items-center justify-between space-y-2">
        <h2 className="text-3xl font-bold tracking-tight">Ajustes de la Tienda</h2>
      </div>
      <div className="grid gap-8 md:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Información de la Tienda</CardTitle>
                <CardDescription>Actualiza los datos de tu tienda.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="store-name">Nombre de la tienda</Label>
                    <Input id="store-name" defaultValue="Moto Repuestos Pro" />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="store-slug">Slug (URL)</Label>
                    <Input id="store-slug" defaultValue="moto-repuestos-pro" />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="store-description">Descripción</Label>
                    <Textarea id="store-description" defaultValue="Los mejores repuestos para tu moto. Calidad y servicio garantizado." />
                </div>
                 <div className="space-y-2">
                    <Label>Logo</Label>
                    <Input type="file" />
                    <p className="text-sm text-muted-foreground">Sube el logo de tu tienda (PNG, JPG).</p>
                </div>
                 <div className="space-y-2">
                    <Label>Imagen de Portada</Label>
                    <Input type="file" />
                    <p className="text-sm text-muted-foreground">Sube una imagen de portada para tu tienda.</p>
                </div>
                <Button>Guardar Cambios</Button>
            </CardContent>
        </Card>
        
        <AIThemeGenerator />

      </div>
    </div>
  );
}

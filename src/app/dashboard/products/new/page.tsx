"use client";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useToast } from "@/hooks/use-toast";
import { ProductSchema } from "@/lib/schemas/product";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { useRouter } from "next/navigation";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";


const ProductFormSchema = ProductSchema.omit({
  id: true,
  storeId: true,
  userId: true,
  averageRating: true,
  ratings: true,
  createdAt: true,
  updatedAt: true,
}).transform((data) => ({
  ...data,
  description: data.description ?? "",
  image: data.image ?? "",
}));

type ProductFormValues = z.infer<typeof ProductFormSchema>;

// TODO: Load categories from Firestore
const categories = [
    { id: "cascos", name: "Cascos" },
    { id: "llantas", name: "Llantas" },
    { id: "aceites", name: "Aceites y lubricantes" },
    { id: "frenos", name: "Frenos" },
    { id: "baterias", name: "Baterías" },
    { id: "accesorios", name: "Accesorios" },
];

export default function NewProductPage() {
  const { toast } = useToast();
  const router = useRouter();

  const form = useForm<ProductFormValues>({
    resolver: zodResolver(ProductFormSchema),
    defaultValues: {
      name: "",
      description: "",
      price: 0,
      stock: 0,
      image: "",
      categoryId: "",
      offer: false,
    },
    mode: "onChange",
  });

  const onSubmit = async (data: ProductFormValues) => {
    toast({
        title: "¡Producto creado! (Simulado)",
        description: "Tu nuevo producto ha sido guardado correctamente.",
      });
      
    router.push("/dashboard/products");
  };

  return (
    <Card>
        <CardHeader>
            <CardTitle>Crear Nuevo Producto</CardTitle>
            <CardDescription>Completa el formulario para añadir un nuevo artículo a tu tienda.</CardDescription>
        </CardHeader>
        <CardContent>
            <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="grid md:grid-cols-2 gap-6">
                    <div className="space-y-4">
                        <FormField
                            control={form.control}
                            name="name"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Nombre del Producto</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Ej: Casco Integral Pro-X" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="description"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Descripción</FormLabel>
                                    <FormControl>
                                        <Textarea placeholder="Describe las características principales del producto..." {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="price"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Precio</FormLabel>
                                    <FormControl>
                                        <Input type="number" placeholder="Ej: 350000" {...field} onChange={e => field.onChange(parseFloat(e.target.value))}/>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="stock"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Stock (Unidades disponibles)</FormLabel>
                                    <FormControl>
                                        <Input type="number" placeholder="Ej: 15" {...field} onChange={e => field.onChange(parseInt(e.target.value, 10))}/>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    </div>
                    <div className="space-y-4">
                        <FormField
                            control={form.control}
                            name="categoryId"
                            render={({ field }) => (
                                <FormItem>
                                <FormLabel>Categoría</FormLabel>
                                <Select onValueChange={field.onChange} defaultValue={field.value}>
                                    <FormControl>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona una categoría" />
                                    </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                    {categories.map(cat => (
                                        <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
                                    ))}
                                    </SelectContent>
                                </Select>
                                <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="image"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>URL de la Imagen</FormLabel>
                                    <FormControl>
                                        <Input placeholder="https://ejemplo.com/imagen.jpg" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                         <div className="space-y-2">
                            <FormLabel>Subir Imagen</FormLabel>
                            <Input type="file" disabled/>
                            <p className="text-sm text-muted-foreground">La subida de archivos se habilitará pronto.</p>
                        </div>
                    </div>
                    <div className="md:col-span-2">
                        <Button type="submit" disabled={form.formState.isSubmitting}>
                            {form.formState.isSubmitting ? "Guardando..." : "Guardar Producto"}
                        </Button>
                    </div>
                </form>
            </Form>
        </CardContent>
    </Card>
  );
}

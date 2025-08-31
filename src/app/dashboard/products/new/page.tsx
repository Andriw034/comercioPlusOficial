
"use client";

import { useState } from "react";
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
import Image from "next/image";
import { Image as ImageIcon } from "lucide-react";


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
  const [imagePreview, setImagePreview] = useState<string | null>(null);

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
  
  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
        const reader = new FileReader();
        reader.onloadend = () => {
            setImagePreview(reader.result as string);
            form.setValue("image", reader.result as string, { shouldValidate: true });
        };
        reader.readAsDataURL(file);
    } else {
        setImagePreview(null);
        form.setValue("image", "", { shouldValidate: true });
    }
  };


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
                <form onSubmit={form.handleSubmit(onSubmit)} className="grid md:grid-cols-2 gap-x-8 gap-y-6">
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

                        <div className="space-y-2">
                          <FormLabel>Imagen del Producto</FormLabel>
                           <div className="flex items-center gap-4">
                              <div className="h-24 w-24 rounded-lg bg-muted flex items-center justify-center border">
                                  {imagePreview ? (
                                      <Image src={imagePreview} width={96} height={96} alt="Vista previa del producto" className="rounded-md object-cover h-24 w-24"/>
                                  ) : (
                                      <ImageIcon className="h-10 w-10 text-muted-foreground" />
                                  )}
                              </div>
                              <Input 
                                  type="file" 
                                  accept="image/*"
                                  onChange={handleImageChange}
                              />
                           </div>
                           <p className="text-sm text-muted-foreground">La subida se simula, pero la previsualización funciona.</p>
                       </div>

                       <FormField
                            control={form.control}
                            name="image"
                            render={({ field }) => (
                                <FormItem className="hidden">
                                    <FormLabel>URL de la Imagen</FormLabel>
                                    <FormControl>
                                        <Input {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
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

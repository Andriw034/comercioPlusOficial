"use client";

import { useEffect, useState } from "react";
import { AIThemeGenerator } from "@/components/dashboard/ai-theme-generator";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useToast } from "@/hooks/use-toast";
import { StoreSchema, type Store } from "@/lib/schemas/store";
import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { z } from "zod";
import Image from "next/image";
import { Bike, Loader2 } from "lucide-react";
import { ThemeToggle } from "@/components/dashboard/theme-toggle";
import { useAuth } from "@/lib/contexts/auth-context";
import { getStoreByUserId, createOrUpdateStore } from "@/lib/services/store";
import { uploadFile } from "@/lib/services/storage";
import Link from "next/link";

const StoreFormSchema = StoreSchema.omit({
  id: true,
  userId: true,
  status: true,
  averageRating: true,
  theme: true,
  createdAt: true,
  updatedAt: true,
});

type StoreFormValues = z.infer<typeof StoreFormSchema>;

export default function StoreSettingsPage() {
  const { toast } = useToast();
  const { user, loading: authLoading } = useAuth();
  
  const [store, setStore] = useState<Store | null>(null);
  const [loading, setLoading] = useState(true);
  
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [coverFile, setCoverFile] = useState<File | null>(null);

  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [coverPreview, setCoverPreview] = useState<string | null>(null);
  
  const form = useForm<StoreFormValues>({
    resolver: zodResolver(StoreFormSchema),
    defaultValues: {
      name: "",
      slug: "",
      description: "",
      address: "",
      phone: "",
      openingHours: "",
      mainCategory: "Repuestos",
      logo: "",
      cover: "",
    },
  });

  useEffect(() => {
    if (user) {
      const fetchStoreData = async () => {
        setLoading(true);
        const storeData = await getStoreByUserId(user.uid);
        if (storeData) {
          setStore(storeData as Store);
          form.reset({
            name: storeData.name,
            slug: storeData.slug,
            description: storeData.description ?? "",
            address: storeData.address,
            phone: storeData.phone ?? "",
            openingHours: storeData.openingHours ?? "",
            mainCategory: storeData.mainCategory,
            logo: storeData.logo ?? "",
            cover: storeData.cover ?? "",
          });
          if (storeData.logo) setLogoPreview(storeData.logo);
          if (storeData.cover) setCoverPreview(storeData.cover);
        }
        setLoading(false);
      };
      fetchStoreData();
    } else if (!authLoading) {
      setLoading(false);
    }
  }, [user, authLoading, form]);


  const handleFileChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    setFile: React.Dispatch<React.SetStateAction<File | null>>,
    setPreview: React.Dispatch<React.SetStateAction<string | null>>
  ) => {
    const file = e.target.files?.[0];
    if (file) {
      setFile(file);
      const reader = new FileReader();
      reader.onloadend = () => {
        setPreview(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const onSubmit = async (data: StoreFormValues) => {
    if (!user) {
      toast({ title: "Error", description: "Debes iniciar sesión para guardar.", variant: "destructive" });
      return;
    }

    try {
      let logoUrl = store?.logo ?? null;
      if (logoFile) {
        logoUrl = await uploadFile(logoFile, `stores/${user.uid}/logo`);
      }

      let coverUrl = store?.cover ?? null;
      if (coverFile) {
        coverUrl = await uploadFile(coverFile, `stores/${user.uid}/cover`);
      }
      
      const storeDataToSave = {
        ...data,
        logo: logoUrl,
        cover: coverUrl,
      };

      await createOrUpdateStore(user.uid, storeDataToSave);

      toast({
        title: "¡Tienda actualizada!",
        description: "Los datos de tu tienda se han guardado correctamente.",
      });

    } catch (error) {
        console.error("Error saving store:", error);
        toast({ title: "Error", description: "No se pudo guardar la información de la tienda.", variant: "destructive" });
    }
  };

  if (loading || authLoading) {
    return (
        <div className="flex items-center justify-center h-full p-8">
            <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
    )
  }

  if (!user) {
      return (
            <div className="flex flex-col items-center justify-center h-full gap-4 text-center p-8">
                <CardTitle>Acceso denegado</CardTitle>
                <CardDescription>Debes iniciar sesión para administrar tu tienda.</CardDescription>
                <Button asChild>
                    <Link href="/login">Ir a Iniciar Sesión</Link>
                </Button>
            </div>
      )
  }


  return (
    <div className="flex-1 space-y-4 p-4 md:p-8 pt-6">
      <div className="flex items-center justify-between space-y-2">
        <h2 className="text-3xl font-bold tracking-tight">Ajustes de la Tienda</h2>
        <ThemeToggle />
      </div>
      <div className="grid gap-8 md:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Información de la Tienda</CardTitle>
                <CardDescription>Actualiza los datos de tu tienda. Este será tu perfil público.</CardDescription>
            </CardHeader>
            <CardContent>
                <Form {...form}>
                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                        <FormField
                            control={form.control}
                            name="name"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Nombre de la tienda</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Ej: Moto Repuestos Pro" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="slug"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Slug (URL)</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Ej: moto-repuestos-pro" {...field} />
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
                                        <Textarea placeholder="Describe tu tienda..." {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                         <FormField
                            control={form.control}
                            name="address"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Dirección</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Ej: Calle 123 #45-67" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                          <FormField
                            control={form.control}
                            name="phone"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Teléfono</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Ej: 3001234567" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        <FormField
                            control={form.control}
                            name="openingHours"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Horario de Atención</FormLabel>
                                    <FormControl>
                                        <Input placeholder="Ej: L-V 8am-6pm, S 9am-2pm" {...field} />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                        
                        <div className="space-y-2">
                            <FormLabel>Logo</FormLabel>
                            <div className="flex items-center gap-4">
                                <div className="h-16 w-16 rounded-lg bg-muted flex items-center justify-center border">
                                    {logoPreview ? (
                                        <Image src={logoPreview} width={64} height={64} alt="Vista previa del logo" className="rounded-md object-cover h-16 w-16"/>
                                    ) : (
                                        <Bike className="h-10 w-10 text-muted-foreground" />
                                    )}
                                </div>
                                <Input 
                                    type="file" 
                                    accept="image/*"
                                    onChange={(e) => handleFileChange(e, setLogoFile, setLogoPreview)}
                                />
                            </div>
                        </div>

                        <div className="space-y-2">
                            <FormLabel>Imagen de Portada</FormLabel>
                             <div className="flex items-center gap-4">
                                 {coverPreview ? (
                                    <Image src={coverPreview} width={200} height={100} alt="Vista previa de la portada" className="rounded-md object-cover h-24 w-48"/>
                                ) : (
                                    <div className="h-24 w-48 rounded-md bg-muted flex items-center justify-center text-muted-foreground text-sm border">
                                        Vista Previa
                                    </div>
                                )}
                                <Input 
                                    type="file" 
                                    accept="image/*"
                                    onChange={(e) => handleFileChange(e, setCoverFile, setCoverPreview)}
                                />
                             </div>
                        </div>

                        <Button type="submit" disabled={form.formState.isSubmitting}>
                            {form.formState.isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            {form.formState.isSubmitting ? "Guardando..." : "Guardar Cambios"}
                        </Button>
                    </form>
                </Form>
            </CardContent>
        </Card>
        
        <AIThemeGenerator />

      </div>
    </div>
  );
}

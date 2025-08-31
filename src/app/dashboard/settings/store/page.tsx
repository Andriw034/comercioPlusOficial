
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
import { Bike } from "lucide-react";
import { ThemeToggle } from "@/components/dashboard/theme-toggle";
import { useAuthState } from "react-firebase-hooks/auth";
import { auth, db } from "@/lib/firebase";
import { doc, getDoc, setDoc, serverTimestamp } from "firebase/firestore";

// We omit fields that are not in the form or are handled separately
const StoreFormSchema = StoreSchema.omit({
  id: true,
  userId: true,
  logo: true,
  cover: true,
  status: true,
  averageRating: true,
  theme: true,
  createdAt: true,
  updatedAt: true,
});

type StoreFormValues = z.infer<typeof StoreFormSchema>;

export default function StoreSettingsPage() {
  const [user] = useAuthState(auth);
  const { toast } = useToast();
  const [logoFile, setLogoFile] = useState<File | null>(null);
  const [coverFile, setCoverFile] = useState<File | null>(null);
  const [logoPreview, setLogoPreview] = useState<string | null>(null);
  const [coverPreview, setCoverPreview] = useState<string | null>(null);
  const [existingLogo, setExistingLogo] = useState<string | null>(null);
  const [existingCover, setExistingCover] = useState<string | null>(null);

  const form = useForm<StoreFormValues>({
    resolver: zodResolver(StoreFormSchema),
    defaultValues: {
      name: "",
      slug: "",
      description: "",
      address: "",
      phone: "",
      openingHours: "",
      mainCategory: "Repuestos", // Default category
    },
  });

  useEffect(() => {
    const fetchStoreData = async () => {
      if (user) {
        const storeRef = doc(db, "stores", user.uid);
        const storeSnap = await getDoc(storeRef);
        if (storeSnap.exists()) {
          const storeData = storeSnap.data() as Store;
          form.reset(storeData);
          if (storeData.logo) setExistingLogo(storeData.logo);
          if (storeData.cover) setExistingCover(storeData.cover);
        }
      }
    };
    fetchStoreData();
  }, [user, form]);

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
      toast({ title: "Error", description: "Debes iniciar sesión.", variant: "destructive" });
      return;
    }

    try {
      const storeRef = doc(db, "stores", user.uid);
      const storeData = {
        userId: user.uid,
        ...data,
        // In a real app, you would upload logoFile and coverFile to storage
        // and get the URLs to save here. For now, we'll just save the form data.
        logo: existingLogo, // placeholder
        cover: existingCover, // placeholder
        updatedAt: serverTimestamp(),
      };
      
      await setDoc(storeRef, storeData, { merge: true });

      toast({
        title: "¡Tienda actualizada!",
        description: "Los datos de tu tienda se han guardado correctamente.",
      });
    } catch (error) {
      console.error("Error updating store:", error);
      toast({ title: "Error", description: "No se pudo actualizar la tienda.", variant: "destructive" });
    }
  };


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
                                <div className="h-16 w-16 rounded-lg bg-muted flex items-center justify-center">
                                    {logoPreview ? (
                                        <Image src={logoPreview} width={64} height={64} alt="Vista previa del logo" className="rounded-md object-cover h-16 w-16"/>
                                    ) : existingLogo ? (
                                        <Image src={existingLogo} width={64} height={64} alt="Logo actual" className="rounded-md object-cover h-16 w-16"/>
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
                                ) : existingCover ? (
                                    <Image src={existingCover} width={200} height={100} alt="Portada actual" className="rounded-md object-cover h-24 w-48"/>
                                ) : (
                                    <div className="h-24 w-48 rounded-md bg-muted flex items-center justify-center text-muted-foreground text-sm">
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

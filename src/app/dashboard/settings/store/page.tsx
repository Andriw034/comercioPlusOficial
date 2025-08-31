
"use client";

import { useEffect } from "react";
import { AIThemeGenerator } from "@/components/dashboard/ai-theme-generator";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useToast } from "@/hooks/use-toast";
import { auth, db } from "@/lib/firebase";
import { StoreSchema, type Store } from "@/lib/schemas/store";
import { zodResolver } from "@hookform/resolvers/zod";
import { doc, getDoc, serverTimestamp, setDoc } from "firebase/firestore";
import { useAuthState } from "react-firebase-hooks/auth";
import { useForm } from "react-hook-form";
import { z } from "zod";

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
    if (user) {
      const fetchStoreData = async () => {
        const storeRef = doc(db, "stores", user.uid);
        const storeSnap = await getDoc(storeRef);
        if (storeSnap.exists()) {
          const storeData = storeSnap.data() as Store;
          // Use form.reset to populate the form with existing data
          form.reset(storeData);
        }
      };
      fetchStoreData();
    }
  }, [user, form]);

  const onSubmit = async (data: StoreFormValues) => {
    if (!user) {
      toast({
        title: "No autenticado",
        description: "Debes iniciar sesión para guardar los ajustes de la tienda.",
        variant: "destructive",
      });
      return;
    }

    try {
      // Use user's UID as the store's ID for a 1-to-1 relationship
      const storeRef = doc(db, "stores", user.uid);
      
      await setDoc(storeRef, {
        ...data,
        id: user.uid,
        userId: user.uid,
        updatedAt: serverTimestamp(),
      }, { merge: true }); // Use merge to avoid overwriting fields

      toast({
        title: "¡Tienda actualizada!",
        description: "Los datos de tu tienda se han guardado correctamente.",
      });

    } catch (error) {
      console.error("Error saving store settings:", error);
      toast({
        title: "Error",
        description: "No se pudieron guardar los ajustes de la tienda.",
        variant: "destructive",
      });
    }
  };


  return (
    <div className="flex-1 space-y-4 p-4 md:p-8 pt-6">
      <div className="flex items-center justify-between space-y-2">
        <h2 className="text-3xl font-bold tracking-tight">Ajustes de la Tienda</h2>
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
                        {/* File inputs are handled separately */}
                        <div className="space-y-2">
                            <FormLabel>Logo</FormLabel>
                            <Input type="file" disabled />
                            <p className="text-sm text-muted-foreground">La subida de archivos se habilitará pronto.</p>
                        </div>
                        <div className="space-y-2">
                            <FormLabel>Imagen de Portada</FormLabel>
                            <Input type="file" disabled />
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

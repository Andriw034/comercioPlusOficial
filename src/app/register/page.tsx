
"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { createUserWithEmailAndPassword, updateProfile } from "firebase/auth";
import { auth, db } from "@/lib/firebase";
import { doc, setDoc } from "firebase/firestore";

import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";
import { UserRoleSchema } from "@/lib/schemas/user";

const formSchema = z.object({
  fullName: z.string().min(3, "El nombre debe tener al menos 3 caracteres."),
  email: z.string().email("Por favor, ingresa un correo electrónico válido."),
  password: z.string().min(6, "La contraseña debe tener al menos 6 caracteres."),
  role: UserRoleSchema,
});

export default function RegisterPage() {
  const router = useRouter();
  const { toast } = useToast();
  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      fullName: "",
      email: "",
      password: "",
      role: "Cliente",
    },
  });

  const onSubmit = async (values: z.infer<typeof formSchema>) => {
    try {
      const userCredential = await createUserWithEmailAndPassword(
        auth,
        values.email,
        values.password
      );
      const user = userCredential.user;
      
      await updateProfile(user, {
        displayName: values.fullName,
      });

      // Save user role and other info in Firestore
      await setDoc(doc(db, "users", user.uid), {
        id: user.uid,
        name: values.fullName,
        email: values.email,
        role: values.role,
        createdAt: new Date(),
        updatedAt: new Date(),
        status: true,
      });
      
      toast({
        title: "¡Cuenta creada!",
        description: "Tu cuenta ha sido creada exitosamente.",
      });

      router.push("/dashboard");
    } catch (error: any) {
      console.error("Error creating account:", error);
      let description = "Ocurrió un error inesperado. Por favor, intenta de nuevo.";
      if (error.code === 'auth/email-already-in-use') {
        description = "Este correo electrónico ya está en uso. Por favor, intenta con otro.";
      } else if (error.code === 'auth/api-key-not-valid') {
        description = "La clave de API de Firebase no es válida. Por favor, revisa tu configuración.";
      }
      toast({
        title: "Error al crear la cuenta",
        description: description,
        variant: "destructive",
      });
    }
  };

  return (
    <div className="w-full lg:grid lg:min-h-[calc(100vh-8rem)] lg:grid-cols-2">
      <div className="flex items-center justify-center py-12">
        <Card className="mx-auto max-w-sm border-0 shadow-none sm:border sm:shadow-sm">
          <CardHeader>
            <CardTitle className="text-2xl">Crea tu cuenta</CardTitle>
            <CardDescription>Ingresa tus datos para registrarte.</CardDescription>
          </CardHeader>
          <CardContent>
            <Form {...form}>
              <form onSubmit={form.handleSubmit(onSubmit)} className="grid gap-4">
                <FormField
                  control={form.control}
                  name="fullName"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Nombre completo</FormLabel>
                      <FormControl>
                        <Input placeholder="Juan Pérez" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="email"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Correo electrónico</FormLabel>
                      <FormControl>
                        <Input placeholder="m@ejemplo.com" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="password"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Contraseña</FormLabel>
                      <FormControl>
                        <Input type="password" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="role"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Rol</FormLabel>
                      <Select onValueChange={field.onChange} defaultValue={field.value}>
                        <FormControl>
                          <SelectTrigger>
                            <SelectValue placeholder="Selecciona tu rol" />
                          </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                          <SelectItem value="Comerciante">Comerciante</SelectItem>
                          <SelectItem value="Cliente">Cliente</SelectItem>
                        </SelectContent>
                      </Select>
                      <FormMessage />
                    </FormItem>
                  )}
                />
                <Button type="submit" className="w-full bg-gradient-to-r from-primary to-accent text-primary-foreground" disabled={form.formState.isSubmitting}>
                  {form.formState.isSubmitting ? "Creando cuenta..." : "Crear cuenta"}
                </Button>
              </form>
            </Form>
            <div className="mt-4 text-center text-sm">
              ¿Ya tienes una cuenta?{" "}
              <Link href="/login" className="underline">
                Iniciar sesión
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>
      <div className="hidden bg-muted lg:flex items-center justify-center rounded-l-3xl">
        <div className="w-full h-full bg-gradient-to-br from-primary to-accent rounded-l-3xl flex items-center justify-center text-center p-12">
          <div className="space-y-4">
            <h2 className="text-4xl font-extrabold text-primary-foreground">
              Tu tienda de motos, a un clic de distancia.
            </h2>
            <p className="text-primary-foreground/80 text-lg">
              Únete a la comunidad de ComercioPlus y lleva tu negocio al siguiente nivel.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

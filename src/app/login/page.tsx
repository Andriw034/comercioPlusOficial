
"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { signInWithEmailAndPassword } from "firebase/auth";
import { auth, db } from "@/lib/firebase";
import { doc, getDoc } from "firebase/firestore";

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
import { useToast } from "@/hooks/use-toast";
import type { User } from "@/lib/schemas/user";


const formSchema = z.object({
  email: z.string().email("Por favor, ingresa un correo electrónico válido."),
  password: z.string().min(1, "Por favor, ingresa tu contraseña."),
});

export default function LoginPage() {
  const router = useRouter();
  const { toast } = useToast();
  const form = useForm<z.infer<typeof formSchema>>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      email: "comerciante@example.com",
      password: "password",
    },
  });

  const onSubmit = async (values: z.infer<typeof formSchema>) => {
    try {
      const userCredential = await signInWithEmailAndPassword(auth, values.email, values.password);
      const user = userCredential.user;

      // Fetch user role from Firestore
      const userDocRef = doc(db, "users", user.uid);
      const userDocSnap = await getDoc(userDocRef);

      toast({
        title: "¡Bienvenido de vuelta!",
        description: "Has iniciado sesión correctamente.",
      });

      if (userDocSnap.exists()) {
        const userData = userDocSnap.data() as User;
        if (userData.role === 'Comerciante') {
          router.push("/dashboard/products");
        } else {
          router.push("/dashboard");
        }
      } else {
        // Fallback if user document doesn't exist for some reason
        router.push("/dashboard");
      }

    } catch (error: any) {
      console.error("Error signing in:", error);
      let description = "Ocurrió un error inesperado. Por favor, intenta de nuevo.";
      if (error.code === 'auth/invalid-credential' || error.code === 'auth/user-not-found' || error.code === 'auth/wrong-password') {
        description = "Las credenciales son incorrectas. Verifica tu correo y contraseña.";
      }
      toast({
        title: "Error al iniciar sesión",
        description,
        variant: "destructive",
      });
    }
  };

  return (
    <div className="w-full h-full lg:grid lg:min-h-[calc(100vh-8rem)] lg:grid-cols-2">
      <div className="hidden bg-muted lg:block">
           <div className="w-full h-full bg-primary flex items-center justify-center text-center p-12">
            <h2 className="text-4xl font-extrabold text-primary-foreground">
                Empieza gratis y comparte tu catálogo hoy.
            </h2>
        </div>
      </div>
      <div className="flex items-center justify-center py-12">
        <div className="mx-auto grid w-[350px] gap-6">
          <div className="grid gap-2 text-center">
            <h1 className="text-3xl font-bold">Iniciar sesión</h1>
            <p className="text-balance text-muted-foreground">
              Ingresa tu correo para acceder a tu cuenta
            </p>
          </div>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="grid gap-4">
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
                    <div className="flex items-center">
                      <FormLabel>Contraseña</FormLabel>
                    </div>
                    <FormControl>
                      <Input type="password" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
              <Button type="submit" className="w-full" disabled={form.formState.isSubmitting}>
                {form.formState.isSubmitting ? "Iniciando sesión..." : "Iniciar sesión"}
              </Button>
            </form>
          </Form>
          <div className="mt-4 text-center text-sm">
            ¿Aún no tienes cuenta?{" "}
            <Link href="/register" className="underline">
              Crear cuenta
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}

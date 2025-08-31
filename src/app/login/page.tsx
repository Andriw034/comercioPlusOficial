
"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { signInWithEmailAndPassword } from "firebase/auth";

import { Button } from "@/components/ui/button";
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
import Image from "next/image";
import { auth } from "@/lib/firebase";
import { Loader2 } from "lucide-react";


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
      email: "",
      password: "",
    },
  });

  const onSubmit = async (values: z.infer<typeof formSchema>) => {
    try {
      await signInWithEmailAndPassword(auth, values.email, values.password);
      toast({
          title: "¡Bienvenido de vuelta!",
          description: "Has iniciado sesión correctamente.",
      });
      router.push("/dashboard");
    } catch (error: any) {
        console.error("Login Error:", error);
        let description = "Ocurrió un error inesperado.";
        if (error.code === 'auth/user-not-found' || error.code === 'auth/wrong-password' || error.code === 'auth/invalid-credential') {
            description = "El correo electrónico o la contraseña son incorrectos.";
        }
        toast({
            title: "Error al iniciar sesión",
            description,
            variant: "destructive",
        });
    }
  };

  return (
    <div className="w-full h-full lg:grid lg:min-h-[calc(100vh-4rem)] lg:grid-cols-2">
      <div className="hidden bg-muted lg:block relative">
           <Image
            src="https://picsum.photos/1200/800"
            alt="Motorcycle parts"
            fill
            className="object-cover opacity-20"
            data-ai-hint="motorcycle workshop"
           />
      </div>
      <div className="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div className="mx-auto w-full max-w-md space-y-8">
          <div className="space-y-2 text-center">
            <h1 className="text-3xl font-bold">Iniciar sesión</h1>
            <p className="text-balance text-muted-foreground">
              Ingresa tu correo para acceder a tu panel de control.
            </p>
          </div>
          <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
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
                {form.formState.isSubmitting && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                {form.formState.isSubmitting ? "Iniciando sesión..." : "Iniciar sesión"}
              </Button>
            </form>
          </Form>
          <div className="mt-6 text-center text-sm text-muted-foreground">
            ¿Aún no tienes cuenta?{" "}
            <Link href="/register" className="font-medium text-primary hover:text-primary/90 underline-offset-4 hover:underline">
              Crear cuenta
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}

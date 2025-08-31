import Link from "next/link"
import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export default function RegisterPage() {
  return (
    <div className="w-full lg:grid lg:min-h-[calc(100vh-8rem)] lg:grid-cols-2">
         <div className="flex items-center justify-center py-12">
            <Card className="mx-auto max-w-sm border-0 shadow-none sm:border sm:shadow-sm">
                <CardHeader>
                    <CardTitle className="text-2xl">Crea tu cuenta</CardTitle>
                    <CardDescription>
                    Ingresa tus datos para registrarte.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid gap-4">
                    <div className="grid gap-2">
                        <Label htmlFor="full-name">Nombre completo</Label>
                        <Input id="full-name" placeholder="Juan Pérez" required />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="email">Correo electrónico</Label>
                        <Input
                        id="email"
                        type="email"
                        placeholder="m@ejemplo.com"
                        required
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="password">Contraseña</Label>
                        <Input id="password" type="password" />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="role">Rol</Label>
                        <Select defaultValue="cliente">
                            <SelectTrigger id="role">
                                <SelectValue placeholder="Selecciona tu rol" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="comerciante">Comerciante</SelectItem>
                                <SelectItem value="cliente">Cliente</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button type="submit" className="w-full bg-gradient-to-r from-primary to-accent text-primary-foreground">
                        Crear cuenta
                    </Button>
                    </div>
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
  )
}

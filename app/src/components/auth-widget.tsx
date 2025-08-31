"use client";

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { type User as FirebaseUser } from 'firebase/auth';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Skeleton } from './ui/skeleton';
import { useToast } from '@/hooks/use-toast';
import type { User as AppUser } from '@/lib/schemas/user';
import type { Store } from '@/lib/schemas/store';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip';

type UserState = {
  data: Partial<FirebaseUser>;
  appUser: AppUser | null;
  store: Store | null;
} | null;

// Mocked user for UI development without real authentication
const mockUser: Partial<FirebaseUser> = {
  uid: 'mock-user-id',
  displayName: 'Comerciante de Prueba',
  email: 'comerciante@example.com',
  photoURL: 'https://i.pravatar.cc/150?u=a042581f4e29026704d',
};

const mockStore: Store = {
    id: "mock-store-id",
    userId: "mock-user-id",
    name: "Tienda de Prueba",
    slug: "tienda-de-prueba",
    description: "Esta es una tienda de prueba.",
    address: "Calle Falsa 123",
    mainCategory: "Repuestos",
    createdAt: new Date(),
    updatedAt: new Date(),
};


export function AuthWidget() {
  const [loading, setLoading] = useState(true);
  const [userState, setUserState] = useState<UserState>(null);
  const { toast } = useToast();

  useEffect(() => {
    // This now runs only on the client, after the initial render.
    // This avoids the hydration mismatch error.
    const timer = setTimeout(() => {
      setUserState({
        data: mockUser,
        appUser: { role: 'Comerciante' } as AppUser,
        store: mockStore,
      });
      setLoading(false);
    }, 500);

    return () => clearTimeout(timer);
  }, []);

  const handleLogout = async () => {
    toast({
      title: 'Has cerrado sesión (simulado)',
      description: 'En una app real, esto cerraría tu sesión.',
    });
    setLoading(true);
    setUserState(null);
    setTimeout(() => {
        setLoading(false)
    }, 500);
  };
  
  const MyStoreLink = () => {
    if (userState?.appUser?.role === 'Comerciante') {
        if (userState.store?.slug) {
            return (
                <Link href={`/store/${userState.store.slug}`} className="text-sm font-medium text-muted-foreground hover:text-primary transition-colors">Mi Tienda</Link>
            );
        }
        return (
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <span className="text-sm font-medium text-muted-foreground/50 cursor-not-allowed">Mi Tienda</span>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Completa el registro de tu tienda para verla.</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        )
    }
    return null;
  }

  // During initial render and loading state, show skeletons
  if (loading) {
    return (
        <div className='flex items-center gap-4'>
            <Skeleton className="h-8 w-24" />
            <Skeleton className="h-10 w-10 rounded-full" />
        </div>
    );
  }

  // After loading, if user is "logged in" (mocked)
  if (userState?.data) {
    const user = userState.data;
    const userInitial = user.displayName ? user.displayName.charAt(0).toUpperCase() : (user.email?.charAt(0).toUpperCase() ?? 'U');
    return (
        <div className='flex items-center gap-4'>
            <nav className="hidden md:flex items-center gap-6">
                <MyStoreLink />
            </nav>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="relative h-10 w-10 rounded-full">
                    <Avatar className="h-10 w-10">
                    <AvatarImage src={user.photoURL ?? ''} alt={user.displayName ?? ''} />
                    <AvatarFallback>{userInitial}</AvatarFallback>
                    </Avatar>
                </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="w-56" align="end" forceMount>
                <DropdownMenuLabel className="font-normal">
                    <div className="flex flex-col space-y-1">
                    <p className="text-sm font-medium leading-none">{user.displayName}</p>
                    <p className="text-xs leading-none text-muted-foreground">
                        {user.email}
                    </p>
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href="/dashboard">Dashboard</Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link href="/dashboard/settings/store">Ajustes</Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={handleLogout}>
                    Cerrar sesión
                </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
      </div>
    );
  }

  // After loading, if no user is logged in
  return (
    <div className="flex items-center gap-2">
      <Button asChild variant="ghost">
        <Link href="/login">Entrar</Link>
      </Button>
      <Button asChild>
        <Link href="/register">Crear cuenta</Link>
      </Button>
    </div>
  );
}

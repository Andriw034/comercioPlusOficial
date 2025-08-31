
"use client";

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { type User, onAuthStateChanged, signOut } from 'firebase/auth';
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
import { auth } from '@/lib/firebase';

type UserState = {
  data: Partial<User> | null;
  appUser: AppUser | null;
  store: Store | null;
} | null;

export function AuthWidget() {
  const [loading, setLoading] = useState(true);
  const [userState, setUserState] = useState<UserState>(null);
  const router = useRouter();
  const { toast } = useToast();

  useEffect(() => {
    // We start in a loading state
    setLoading(true);

    const mockUser: Partial<User> = {
      uid: 'mock-user-id',
      displayName: 'Comerciante Mock',
      email: 'comerciante@example.com',
      photoURL: `https://i.pravatar.cc/150?u=mock-user`,
    };
    const mockAppUser: AppUser = {
      id: 'mock-user-id',
      name: 'Comerciante Mock',
      email: 'comerciante@example.com',
      role: 'Comerciante',
      createdAt: new Date(),
      updatedAt: new Date(),
      status: true,
    };
    const mockStore: Store = {
      id: 'mock-user-id',
      userId: 'mock-user-id',
      name: 'Mi Tienda Mock',
      slug: 'mi-tienda-mock',
      averageRating: 4.5,
      createdAt: new Date(),
      updatedAt: new Date(),
      mainCategory: "Repuestos",
      status: 'active',
      address: "Calle Falsa 123"
    };

    // This timeout simulates the async nature of onAuthStateChanged
    const timeout = setTimeout(() => {
        // To simulate being logged in:
        setUserState({ data: mockUser, appUser: mockAppUser, store: mockStore });
        // To simulate being logged out:
        // setUserState(null);

        // We are no longer loading
        setLoading(false);
    }, 500);

    return () => clearTimeout(timeout);
  }, []);

  const handleLogout = async () => {
    setLoading(true);
    // MOCK LOGOUT
    setTimeout(() => {
        toast({
            title: 'Has cerrado sesión (simulado)',
            description: 'Vuelve pronto.',
        });
        setUserState(null);
        router.push('/');
        setLoading(false);
    }, 300);
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

  if (loading) {
    return (
        <div className='flex items-center gap-4'>
            <Skeleton className="h-8 w-24" />
            <Skeleton className="h-10 w-10 rounded-full" />
        </div>
    );
  }

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

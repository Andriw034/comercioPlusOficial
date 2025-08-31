
"use client";

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { onAuthStateChanged, signOut, type User } from 'firebase/auth';
import { auth, db } from '@/lib/firebase';
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
import { doc, getDoc } from 'firebase/firestore';
import type { User as AppUser } from '@/lib/schemas/user';
import type { Store } from '@/lib/schemas/store';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip';

type UserState = {
  data: Partial<User> | null;
  appUser: AppUser | null;
  store: Store | null;
}

export function AuthWidget() {
  const [userState, setUserState] = useState<UserState | null>(null);
  const [loading, setLoading] = useState(false); // Set to false to use mock data
  const router = useRouter();
  const { toast } = useToast();

  useEffect(() => {
    // --- START MOCK DATA ---
    // This simulates a logged-in merchant to bypass the network error
    const mockUser: Partial<User> = {
        uid: 'mock-user-id',
        displayName: 'Comerciante de Prueba',
        email: 'test@example.com',
        photoURL: '',
    };
    const mockAppUser: AppUser = {
        id: 'mock-user-id',
        name: 'Comerciante de Prueba',
        email: 'test@example.com',
        role: 'Comerciante',
        createdAt: new Date(),
        updatedAt: new Date(),
        status: true,
    };
     const mockStore: Store = {
        id: 'mock-user-id',
        userId: 'mock-user-id',
        name: 'Tienda de Prueba',
        slug: 'tienda-de-prueba',
        mainCategory: 'Repuestos',
        address: 'Calle Falsa 123',
        createdAt: new Date(),
        updatedAt: new Date(),
    };
    setUserState({ data: mockUser, appUser: mockAppUser, store: mockStore });
    setLoading(false);
    // --- END MOCK DATA ---

    /*
    // --- ORIGINAL FIREBASE CODE ---
    setLoading(true);
    const unsubscribe = onAuthStateChanged(auth, async (user) => {
      if (user) {
        const userDocRef = doc(db, "users", user.uid);
        const userDocSnap = await getDoc(userDocRef);
        const appUser = userDocSnap.exists() ? userDocSnap.data() as AppUser : null;

        let store: Store | null = null;
        if (appUser?.role === 'Comerciante') {
          const storeDocRef = doc(db, "stores", user.uid);
          const storeDocSnap = await getDoc(storeDocRef);
          store = storeDocSnap.exists() ? storeDocSnap.data() as Store : null;
        }

        setUserState({ data: user, appUser, store });
      } else {
        setUserState(null);
      }
      setLoading(false);
    });
    return () => unsubscribe();
    // --- END ORIGINAL FIREBASE CODE ---
    */
  }, []);

  const handleLogout = async () => {
    // In a real scenario, we would sign out from Firebase.
    // For now, we'll just redirect.
    // await signOut(auth); 
    toast({
        title: 'Cierre de sesión simulado',
        description: 'Has cerrado sesión (simulación).',
    });
    setUserState(null); // Simulate logout
    router.push('/');
  };
  
  const MyStoreLink = () => {
    if (userState?.appUser?.role === 'Comerciante') {
        if (userState.store?.slug) {
            return (
                <Link href={`/store/${userState.store.slug}`} className="font-medium text-muted-foreground hover:text-primary transition-colors">Mi Tienda</Link>
            );
        }
        return (
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger>
                        <span className="font-medium text-muted-foreground/50 cursor-not-allowed">Mi Tienda</span>
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
            <Skeleton className="h-6 w-20" />
            <Skeleton className="h-10 w-28" />
        </div>
    );
  }

  if (userState?.data) {
    const user = userState.data;
    const userInitial = user.displayName ? user.displayName.charAt(0).toUpperCase() : (user.email?.charAt(0).toUpperCase() ?? 'U');
    return (
        <div className='flex items-center gap-6'>
            <nav className="hidden md:flex items-center gap-6 text-sm">
                <MyStoreLink />
                <Link href="#" className="font-medium text-muted-foreground hover:text-primary transition-colors">Ayuda</Link>
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
    <div className="flex items-center gap-3">
        <nav className="hidden md:flex items-center gap-6 text-sm mr-3">
            <Link href="#" className="font-medium text-muted-foreground hover:text-primary transition-colors">Ayuda</Link>
        </nav>
      <Button asChild variant="ghost">
        <Link href="/login">Entrar</Link>
      </Button>
      <Button asChild className="bg-gradient-to-r from-primary to-accent text-primary-foreground">
        <Link href="/register">Crear cuenta</Link>
      </Button>
    </div>
  );
}

    
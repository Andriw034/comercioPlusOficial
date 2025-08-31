
"use client";

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { onAuthStateChanged, type User } from 'firebase/auth';
import { auth } from '@/lib/firebase';
import { Skeleton } from '@/components/ui/skeleton';

export function PrivateRoute({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    const unsubscribe = onAuthStateChanged(auth, (user) => {
      setUser(user);
      setLoading(false);
      if (!user) {
        router.push('/login');
      }
    });

    return () => unsubscribe();
  }, [router]);

  if (loading) {
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <div className="grid gap-4 md:grid-cols-2 md:gap-8 lg:grid-cols-4">
                <Skeleton className="h-[125px] w-full" />
                <Skeleton className="h-[125px] w-full" />
                <Skeleton className="h-[125px] w-full" />
                <Skeleton className="h-[125px] w-full" />
            </div>
            <div className="flex items-center gap-4">
                <Skeleton className="h-10 w-48" />
                <Skeleton className="h-10 w-28" />
            </div>
            <Skeleton className="h-[300px] w-full" />
        </div>
    );
  }

  if (user) {
    return <>{children}</>;
  }

  return null;
}

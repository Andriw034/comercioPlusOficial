"use client";

import Link from 'next/link';
import { Button } from '@/components/ui/button';

export function AuthWidget() {
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

import Link from "next/link";
import { Button } from "./ui/button";
import { Logo } from "./logo";
import { AuthWidget } from "./auth-widget";

export function Header() {
  return (
    <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container h-16 flex items-center justify-between">
        <div className="flex items-center gap-6">
          <Link href="/" className="flex items-center gap-2">
            <Logo />
          </Link>
          <nav className="hidden md:flex items-center gap-6 text-sm">
            {/* The AuthWidget will now handle the logic for this link */}
          </nav>
        </div>

        <div className="flex items-center gap-3">
          <AuthWidget />
        </div>
      </div>
    </header>
  );
}

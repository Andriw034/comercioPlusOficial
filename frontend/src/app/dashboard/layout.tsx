import { DashboardSidebar } from "@/components/dashboard/sidebar";
import { ReactNode } from "react";

export default function DashboardLayout({ children }: { children: ReactNode }) {
  return (
    <div className="grid min-h-[calc(100vh-4rem)] md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]">
      <DashboardSidebar />
      <main className="flex w-full flex-col bg-background">
        {children}
      </main>
    </div>
  );
}

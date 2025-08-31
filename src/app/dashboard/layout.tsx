
import { DashboardSidebar } from "@/components/dashboard/sidebar";
import { PrivateRoute } from "@/components/private-route";
import { ReactNode } from "react";

export default function DashboardLayout({ children }: { children: ReactNode }) {
  return (
    <PrivateRoute>
      <div className="grid min-h-[calc(100vh-4rem)] md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]">
        <DashboardSidebar />
        <main className="flex w-full flex-col">
          {children}
        </main>
      </div>
    </PrivateRoute>
  );
}

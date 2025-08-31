import { Bike } from "lucide-react";

export function Logo() {
  return (
    <div className="flex items-center gap-2">
      <div className="h-8 w-8 rounded-lg flex items-center justify-center bg-gradient-to-br from-primary to-accent">
        <Bike className="h-5 w-5 text-primary-foreground" />
      </div>
      <span className="text-lg font-extrabold text-foreground">
        Comercio<span className="text-primary">Plus</span>
      </span>
    </div>
  );
}

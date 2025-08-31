import { Bike } from "lucide-react";

export function Logo() {
  return (
    <div className="flex items-center gap-2">
      <div className="h-8 w-8 rounded-lg flex items-center justify-center bg-primary">
        <Bike className="h-5 w-5 text-background" />
      </div>
      <span className="text-lg font-bold text-foreground">
        ComercioPlus
      </span>
    </div>
  );
}

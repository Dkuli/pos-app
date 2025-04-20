import { Button } from "@/components/ui/button"
import { Label } from "@/components/ui/label"

export function AppearanceSettingsForm({ theme, onChange }) {
  return (
    <div className="space-y-4">
      <Label>Theme</Label>
      <div className="flex gap-2">
        <Button variant={theme === "light" ? "default" : "outline"} onClick={() => onChange("light")}>Light</Button>
        <Button variant={theme === "dark" ? "default" : "outline"} onClick={() => onChange("dark")}>Dark</Button>
        <Button variant={theme === "system" ? "default" : "outline"} onClick={() => onChange("system")}>System</Button>
      </div>
    </div>
  )
}
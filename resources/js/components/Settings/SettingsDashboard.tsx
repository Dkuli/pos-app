import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"

const categories = [
  { key: "general", label: "General" },
  { key: "pos", label: "POS" },
  { key: "tax", label: "Tax" },
  { key: "roles", label: "User Roles" },
  { key: "email", label: "Email" },
  { key: "notifications", label: "Notifications" },
  { key: "payment", label: "Payment Methods" },
  { key: "backup", label: "Backup" },
  { key: "appearance", label: "Appearance" },
  { key: "stores", label: "Stores" },
]

export function SettingsDashboard({ selected, onSelect }) {
  return (
    <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
      {categories.map(cat => (
        <Card
          key={cat.key}
          className={`p-4 cursor-pointer ${selected === cat.key ? "ring-2 ring-primary" : ""}`}
          onClick={() => onSelect(cat.key)}
        >
          <div className="font-semibold">{cat.label}</div>
        </Card>
      ))}
    </div>
  )
}
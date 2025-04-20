import { Button } from "@/components/ui/button"

export function CustomerImportExport({ onImport, onExport }) {
  return (
    <div className="flex gap-2">
      <Button onClick={onImport} variant="outline">Import Customers</Button>
      <Button onClick={onExport} variant="outline">Export Customers</Button>
    </div>
  )
}
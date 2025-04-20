import { Button } from "@/components/ui/button"

export function BackupTools({ onBackup, onRestore }) {
  return (
    <div className="space-y-4">
      <Button onClick={onBackup}>Backup Now</Button>
      <Button variant="outline" onClick={onRestore}>Restore Backup</Button>
    </div>
  )
}
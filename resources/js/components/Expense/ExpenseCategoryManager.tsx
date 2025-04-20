import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Switch } from "@/components/ui/switch"

export function ExpenseCategoryManager({ categories, onEdit, onDelete, onToggle }) {
  const [search, setSearch] = useState("")
  const filtered = categories.filter(c => c.name.toLowerCase().includes(search.toLowerCase()))

  return (
    <div>
      <Input placeholder="Search category..." value={search} onChange={e => setSearch(e.target.value)} className="max-w-xs mb-2" />
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Description</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {filtered.map(cat => (
            <TableRow key={cat.id}>
              <TableCell>{cat.name}</TableCell>
              <TableCell>{cat.description}</TableCell>
              <TableCell>
                <Switch checked={cat.is_active} onCheckedChange={() => onToggle(cat)} />
              </TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onEdit(cat)}>Edit</Button>
                <Button size="sm" variant="destructive" onClick={() => onDelete(cat.id)}>Delete</Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
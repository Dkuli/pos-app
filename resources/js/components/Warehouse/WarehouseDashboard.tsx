import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Switch } from "@/components/ui/switch"
import { Input } from "@/components/ui/input"
import { useState } from "react"

export function WarehouseDashboard({ warehouses, onEdit, onToggleActive, onCreate }) {
  const [search, setSearch] = useState("")
  const filtered = warehouses.filter(w =>
    w.name.toLowerCase().includes(search.toLowerCase()) ||
    w.code.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div>
      <div className="flex flex-wrap gap-2 mb-4">
        <Input placeholder="Search warehouse..." value={search} onChange={e => setSearch(e.target.value)} className="max-w-xs" />
        <Button onClick={onCreate}>Add Warehouse</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Code</TableHead>
            <TableHead>Store</TableHead>
            <TableHead>Phone</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {filtered.map(wh => (
            <TableRow key={wh.id}>
              <TableCell>{wh.name}</TableCell>
              <TableCell>{wh.code}</TableCell>
              <TableCell>{wh.store?.name}</TableCell>
              <TableCell>{wh.phone}</TableCell>
              <TableCell>{wh.email}</TableCell>
              <TableCell>
                <Switch checked={wh.is_active} onCheckedChange={val => onToggleActive(wh, val)} />
              </TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onEdit(wh)}>Edit</Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}

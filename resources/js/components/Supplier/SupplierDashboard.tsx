import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function SupplierDashboard({ suppliers, onEdit, onDelete, onCreate }) {
  const [search, setSearch] = useState("")
  const filtered = suppliers.filter(s =>
    s.name.toLowerCase().includes(search.toLowerCase()) ||
    s.company_name?.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div>
      <div className="flex flex-wrap gap-2 mb-4">
        <Input placeholder="Search supplier..." value={search} onChange={e => setSearch(e.target.value)} className="max-w-xs" />
        <Button onClick={onCreate}>Add Supplier</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Company</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Phone</TableHead>
            <TableHead>Payment Terms</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {filtered.map(s => (
            <TableRow key={s.id}>
              <TableCell>{s.name}</TableCell>
              <TableCell>{s.company_name}</TableCell>
              <TableCell>{s.email}</TableCell>
              <TableCell>{s.phone}</TableCell>
              <TableCell>{s.payment_terms} days</TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onEdit(s)}>Edit</Button>
                <Button size="sm" variant="destructive" onClick={() => onDelete(s.id)}>Delete</Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}

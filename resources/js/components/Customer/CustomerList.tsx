// CustomerList.tsx
import { useState } from "react"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Switch } from "@/components/ui/switch"
import { Pagination } from "@/components/ui/pagination"

export function CustomerList({ customers, onEdit, onView, onToggleActive, onImport, onExport }) {
  const [search, setSearch] = useState("")
  const filtered = customers.data.filter(c =>
    c.name.toLowerCase().includes(search.toLowerCase()) ||
    (c.email && c.email.toLowerCase().includes(search.toLowerCase())) ||
    (c.phone && c.phone.includes(search))
  )

  return (
    <div>
      <div className="flex flex-wrap gap-2 mb-4">
        <Input placeholder="Search customer..." value={search} onChange={e => setSearch(e.target.value)} className="max-w-xs" />
        <Button onClick={onImport} variant="outline">Import</Button>
        <Button onClick={onExport} variant="outline">Export</Button>
        <Button onClick={() => onEdit(null)}>Add Customer</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Email</TableHead>
            <TableHead>Phone</TableHead>
            <TableHead>Membership</TableHead>
            <TableHead>Points</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {filtered.map(c => (
            <TableRow key={c.id}>
              <TableCell>{c.name}</TableCell>
              <TableCell>{c.email}</TableCell>
              <TableCell>{c.phone}</TableCell>
              <TableCell>{c.membership_level}</TableCell>
              <TableCell>{c.points}</TableCell>
              <TableCell>
                <Switch checked={c.is_active} onCheckedChange={val => onToggleActive(c, val)} />
              </TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onView(c)}>View</Button>
                <Button size="sm" variant="outline" onClick={() => onEdit(c)}>Edit</Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <Pagination
        currentPage={customers.current_page}
        totalPages={customers.last_page}
        onPageChange={page => console.log(`Go to page ${page}`)}
      />
    </div>
  )
}
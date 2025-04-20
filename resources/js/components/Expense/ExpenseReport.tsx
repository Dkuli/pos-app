import { useState } from "react"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function ExpenseReport({ expenses, categories, stores, onExport, onFilter }) {
  const [filters, setFilters] = useState({ date_from: "", date_to: "", category: "", store: "" })

  return (
    <div>
      <div className="flex flex-wrap gap-2 mb-4">
        <Input type="date" value={filters.date_from} onChange={e => setFilters(f => ({ ...f, date_from: e.target.value }))} />
        <Input type="date" value={filters.date_to} onChange={e => setFilters(f => ({ ...f, date_to: e.target.value }))} />
        <Select value={filters.category} onValueChange={val => setFilters(f => ({ ...f, category: val }))}>
          <SelectTrigger className="w-40"><SelectValue placeholder="All Categories" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Categories</SelectItem>
            {categories.map(c => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
          </SelectContent>
        </Select>
        <Select value={filters.store} onValueChange={val => setFilters(f => ({ ...f, store: val }))}>
          <SelectTrigger className="w-40"><SelectValue placeholder="All Stores" /></SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Stores</SelectItem>
            {stores.map(s => <SelectItem key={s.id} value={s.id}>{s.name}</SelectItem>)}
          </SelectContent>
        </Select>
        <Button onClick={() => onFilter(filters)}>Apply</Button>
        <Button variant="outline" onClick={() => onExport(filters)}>Export CSV</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Reference</TableHead>
            <TableHead>Category</TableHead>
            <TableHead>Store</TableHead>
            <TableHead>Amount</TableHead>
            <TableHead>Notes</TableHead>
            <TableHead>Receipt</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {expenses.map(e => (
            <TableRow key={e.id}>
              <TableCell>{e.date}</TableCell>
              <TableCell>{e.reference}</TableCell>
              <TableCell>{e.category?.name}</TableCell>
              <TableCell>{e.store?.name}</TableCell>
              <TableCell>{e.amount}</TableCell>
              <TableCell>{e.notes}</TableCell>
              <TableCell>
                {e.attachment && (
                  <Button size="sm" variant="outline" asChild>
                    <a href={e.attachment} target="_blank" rel="noopener">View</a>
                  </Button>
                )}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
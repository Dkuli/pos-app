import { useState } from "react"
import { Input } from "@/components/ui/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function TransactionHistory({ transactions, onRefund, onRecall }) {
  const [search, setSearch] = useState("")
  const filtered = transactions.filter(t =>
    t.transaction_number.includes(search) ||
    t.customer?.name?.toLowerCase().includes(search.toLowerCase())
  )

  return (
    <div>
      <Input placeholder="Search transactions..." value={search} onChange={e => setSearch(e.target.value)} className="mb-2" />
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>No.</TableHead>
            <TableHead>Date</TableHead>
            <TableHead>Customer</TableHead>
            <TableHead>Total</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {filtered.map(t => (
            <TableRow key={t.id}>
              <TableCell>{t.transaction_number}</TableCell>
              <TableCell>{t.transaction_date}</TableCell>
              <TableCell>{t.customer?.name ?? "Walk-in"}</TableCell>
              <TableCell>{t.total}</TableCell>
              <TableCell>{t.payment_status}</TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onRecall(t)}>Recall</Button>
                <Button size="sm" variant="destructive" onClick={() => onRefund(t)}>Refund</Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}

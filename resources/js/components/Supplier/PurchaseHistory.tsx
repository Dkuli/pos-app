import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function PurchaseHistory({ purchases, onView, onReceive, onReturn, onPrint }) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Reference</TableHead>
          <TableHead>Supplier</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Total</TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {purchases.map(p => (
          <TableRow key={p.id}>
            <TableCell>{p.reference}</TableCell>
            <TableCell>{p.supplier?.name}</TableCell>
            <TableCell>{p.date}</TableCell>
            <TableCell>
              <span className={`px-2 py-1 rounded text-xs ${p.status === "received" ? "bg-green-100 text-green-700" : "bg-yellow-100 text-yellow-700"}`}>
                {p.status}
              </span>
            </TableCell>
            <TableCell>{p.grand_total}</TableCell>
            <TableCell>
              <Button size="sm" variant="outline" onClick={() => onView(p)}>View</Button>
              <Button size="sm" variant="outline" onClick={() => onReceive(p)} disabled={p.status === "received"}>Receive</Button>
              <Button size="sm" variant="outline" onClick={() => onReturn(p)}>Return</Button>
              <Button size="sm" variant="outline" onClick={() => onPrint(p)}>Print</Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

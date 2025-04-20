import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function SupplierPaymentHistory({ payments }) {
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Date</TableHead>
          <TableHead>Amount</TableHead>
          <TableHead>Reference</TableHead>
          <TableHead>Notes</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {payments.map(p => (
          <TableRow key={p.id}>
            <TableCell>{p.payment_date}</TableCell>
            <TableCell>{p.amount}</TableCell>
            <TableCell>{p.reference}</TableCell>
            <TableCell>{p.notes}</TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}
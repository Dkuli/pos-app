import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function InventoryHistory({ logs }) {
  // logs: [{ date, warehouse: {name}, product: {name}, type, quantity_change, reference_type, notes }]
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Date</TableHead>
          <TableHead>Warehouse</TableHead>
          <TableHead>Product</TableHead>
          <TableHead>Type</TableHead>
          <TableHead>Change</TableHead>
          <TableHead>Reference</TableHead>
          <TableHead>Notes</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {logs.map((log, idx) => (
          <TableRow key={idx}>
            <TableCell>{log.date}</TableCell>
            <TableCell>{log.warehouse.name}</TableCell>
            <TableCell>{log.product.name}</TableCell>
            <TableCell>{log.type}</TableCell>
            <TableCell className={log.quantity_change < 0 ? "text-red-500" : "text-green-600"}>
              {log.quantity_change}
            </TableCell>
            <TableCell>{log.reference_type}</TableCell>
            <TableCell>{log.notes}</TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

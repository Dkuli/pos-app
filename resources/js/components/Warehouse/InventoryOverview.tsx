// InventoryOverview.tsx
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function InventoryOverview({ inventories }) {
  // inventories: [{ warehouse: {name}, product: {name, sku}, quantity }]
  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Warehouse</TableHead>
          <TableHead>Product</TableHead>
          <TableHead>SKU</TableHead>
          <TableHead>Quantity</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {inventories.map((inv, idx) => (
          <TableRow key={idx}>
            <TableCell>{inv.warehouse.name}</TableCell>
            <TableCell>{inv.product.name}</TableCell>
            <TableCell>{inv.product.sku}</TableCell>
            <TableCell className={inv.quantity <= inv.product.stock_alert_quantity ? "text-red-500 font-bold" : ""}>
              {inv.quantity}
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  )
}

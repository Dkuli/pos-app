import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function LowStockDashboard({ products }) {
  // products: [{ name, sku, total_quantity, stock_alert_quantity }]
  return (
    <div>
      <h2 className="text-lg font-bold mb-2">Low Stock Products</h2>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product</TableHead>
            <TableHead>SKU</TableHead>
            <TableHead>Stock</TableHead>
            <TableHead>Alert Level</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {products.map((p, idx) => (
            <TableRow key={idx}>
              <TableCell>{p.name}</TableCell>
              <TableCell>{p.sku}</TableCell>
              <TableCell className="text-red-500 font-bold">{p.total_quantity}</TableCell>
              <TableCell>{p.stock_alert_quantity}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}

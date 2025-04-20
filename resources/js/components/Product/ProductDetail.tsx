import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function ProductDetail({ product, inventories }) {
  return (
    <div className="space-y-4">
      <Card>
        <div className="p-4">
          <h2 className="text-xl font-bold">{product.name}</h2>
          <p>{product.description}</p>
          <div className="flex gap-4 mt-2">
            <span>SKU: {product.sku}</span>
            <span>Barcode: {product.barcode}</span>
            <span>Price: {product.selling_price}</span>
          </div>
        </div>
      </Card>
      <Card>
        <div className="p-4">
          <h3 className="font-semibold mb-2">Inventory Levels</h3>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Warehouse</TableHead>
                <TableHead>Stock</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {inventories.map(inv => (
                <TableRow key={inv.warehouse_id}>
                  <TableCell>{inv.warehouse_name}</TableCell>
                  <TableCell className={inv.quantity <= product.stock_alert_quantity ? "text-red-500 font-bold" : ""}>
                    {inv.quantity}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      </Card>
    </div>
  )
}

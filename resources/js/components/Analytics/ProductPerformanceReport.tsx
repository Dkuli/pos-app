import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function ProductPerformanceReport({ products, onExport }) {
  return (
    <Card className="p-4">
      <div className="flex justify-between items-center mb-4">
        <div className="font-bold text-lg">Product Performance</div>
        <Button onClick={onExport}>Export CSV</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product</TableHead>
            <TableHead>Category</TableHead>
            <TableHead>Quantity Sold</TableHead>
            <TableHead>Total Sales</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {products.map(p => (
            <TableRow key={p.id}>
              <TableCell>{p.name}</TableCell>
              <TableCell>{p.category}</TableCell>
              <TableCell>{p.quantity_sold}</TableCell>
              <TableCell>{p.total_sales}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </Card>
  )
}
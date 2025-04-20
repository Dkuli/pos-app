import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function ProfitMarginReport({ data, onExport }) {
  return (
    <Card className="p-4">
      <div className="flex justify-between items-center mb-4">
        <div className="font-bold text-lg">Profit Margin Analysis</div>
        <Button onClick={onExport}>Export CSV</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product/Category</TableHead>
            <TableHead>Sales</TableHead>
            <TableHead>Cost</TableHead>
            <TableHead>Profit</TableHead>
            <TableHead>Margin (%)</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {data.map(row => (
            <TableRow key={row.id}>
              <TableCell>{row.name}</TableCell>
              <TableCell>{row.sales}</TableCell>
              <TableCell>{row.cost}</TableCell>
              <TableCell>{row.profit}</TableCell>
              <TableCell>{row.margin}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </Card>
  )
}
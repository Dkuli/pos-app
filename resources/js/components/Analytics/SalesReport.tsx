import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"
// You can use chart libraries like recharts or chart.js for the chart below
import { Line } from "react-chartjs-2"

export function SalesReport({ salesData, chartData, onExport }) {
  return (
    <Card className="p-4">
      <div className="flex justify-between items-center mb-4">
        <div className="font-bold text-lg">Sales Report</div>
        <Button onClick={onExport}>Export CSV</Button>
      </div>
      <div className="mb-4">
        <Line data={chartData} />
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Transactions</TableHead>
            <TableHead>Total Sales</TableHead>
            <TableHead>Tax</TableHead>
            <TableHead>Discount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {salesData.map(row => (
            <TableRow key={row.date}>
              <TableCell>{row.date}</TableCell>
              <TableCell>{row.transaction_count}</TableCell>
              <TableCell>{row.total_sales}</TableCell>
              <TableCell>{row.tax_amount}</TableCell>
              <TableCell>{row.discount_amount}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </Card>
  )
}
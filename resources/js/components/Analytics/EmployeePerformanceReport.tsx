import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function EmployeePerformanceReport({ employees, onExport }) {
  return (
    <Card className="p-4">
      <div className="flex justify-between items-center mb-4">
        <div className="font-bold text-lg">Employee Performance</div>
        <Button onClick={onExport}>Export CSV</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Employee</TableHead>
            <TableHead>Transactions</TableHead>
            <TableHead>Total Sales</TableHead>
            <TableHead>Average Sale</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {employees.map(emp => (
            <TableRow key={emp.id}>
              <TableCell>{emp.name}</TableCell>
              <TableCell>{emp.transaction_count}</TableCell>
              <TableCell>{emp.total_sales}</TableCell>
              <TableCell>{emp.average_sale}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </Card>
  )
}
import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function ExpenseReport({ expenses, onExport }) {
  return (
    <Card className="p-4">
      <div className="flex justify-between items-center mb-4">
        <div className="font-bold text-lg">Expense Report</div>
        <Button onClick={onExport}>Export CSV</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Category</TableHead>
            <TableHead>Amount</TableHead>
            <TableHead>Notes</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {expenses.map(e => (
            <TableRow key={e.id}>
              <TableCell>{e.date}</TableCell>
              <TableCell>{e.category}</TableCell>
              <TableCell>{e.amount}</TableCell>
              <TableCell>{e.notes}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </Card>
  )
}
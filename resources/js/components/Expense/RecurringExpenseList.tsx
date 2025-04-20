import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function RecurringExpenseList({ recurringExpenses, onEdit, onStop }) {
  return (
    <div>
      <div className="font-bold mb-2">Recurring Expenses</div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Frequency</TableHead>
            <TableHead>Next Date</TableHead>
            <TableHead>Amount</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {recurringExpenses.map(e => (
            <TableRow key={e.id}>
              <TableCell>{e.notes}</TableCell>
              <TableCell>{e.recurring_frequency}</TableCell>
              <TableCell>{e.next_recurring_date}</TableCell>
              <TableCell>{e.amount}</TableCell>
              <TableCell>{e.is_active ? "Active" : "Stopped"}</TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onEdit(e)}>Edit</Button>
                <Button size="sm" variant="destructive" onClick={() => onStop(e.id)}>Stop</Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
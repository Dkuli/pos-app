import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function ExpenseDashboard({ summary, recentExpenses }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <Card className="p-4">
        <div className="text-xs text-gray-500">Total Expenses</div>
        <div className="text-2xl font-bold">{summary.total}</div>
      </Card>
      <Card className="p-4">
        <div className="text-xs text-gray-500">This Month</div>
        <div className="text-2xl font-bold">{summary.thisMonth}</div>
      </Card>
      <Card className="p-4">
        <div className="text-xs text-gray-500">Top Category</div>
        <div className="text-2xl font-bold">{summary.topCategory}</div>
      </Card>
      <Card className="p-4">
        <div className="text-xs text-gray-500">Pending Approval</div>
        <div className="text-2xl font-bold">{summary.pending}</div>
      </Card>
      <div className="col-span-full">
        <div className="font-bold mb-2">Recent Expenses</div>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Date</TableHead>
              <TableHead>Category</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Store</TableHead>
              <TableHead>Notes</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {recentExpenses.map(e => (
              <TableRow key={e.id}>
                <TableCell>{e.date}</TableCell>
                <TableCell>{e.category?.name}</TableCell>
                <TableCell>{e.amount}</TableCell>
                <TableCell>{e.store?.name}</TableCell>
                <TableCell>{e.notes}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </div>
  )
}
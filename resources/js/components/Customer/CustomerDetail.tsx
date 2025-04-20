import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function CustomerDetail({ customer, history }) {
  return (
    <div className="space-y-4">
      <Card>
        <div className="p-4">
          <h2 className="text-xl font-bold">{customer.name}</h2>
          <p>Email: {customer.email}</p>
          <p>Phone: {customer.phone}</p>
          <p>Address: {customer.address}, {customer.city}, {customer.postal_code}</p>
          <p>Membership Level: {customer.membership_level}</p>
          <p>Points: {customer.points}</p>
          <p>Credit Limit: {customer.credit_limit}</p>
        </div>
      </Card>
      <Card>
        <div className="p-4">
          <h3 className="font-semibold mb-2">Purchase History</h3>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Date</TableHead>
                <TableHead>Transaction #</TableHead>
                <TableHead>Total</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {history.map(h => (
                <TableRow key={h.id}>
                  <TableCell>{h.date}</TableCell>
                  <TableCell>{h.transaction_number}</TableCell>
                  <TableCell>{h.total}</TableCell>
                  <TableCell>{h.status}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      </Card>
    </div>
  )
}
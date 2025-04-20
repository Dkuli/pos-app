import { Card } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function EndOfDayReport({ session, summary, onPrint }) {
  return (
    <Card className="p-4">
      <div className="font-bold text-lg mb-2">End of Day Report</div>
      <div className="mb-2">Register: {session.cashRegister?.name}</div>
      <div className="mb-2">Opened: {session.opened_at}</div>
      <div className="mb-2">Closed: {session.closed_at}</div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Type</TableHead>
            <TableHead>Amount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow>
            <TableCell>Opening Amount</TableCell>
            <TableCell>{summary.opening_amount}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Sales</TableCell>
            <TableCell>{summary.total_sales}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Refunds</TableCell>
            <TableCell>{summary.total_refunds}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Cash In</TableCell>
            <TableCell>{summary.total_added}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Cash Out</TableCell>
            <TableCell>{summary.total_subtracted}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Expected Closing</TableCell>
            <TableCell>{summary.expected_amount}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Actual Closing</TableCell>
            <TableCell>{summary.closing_amount}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell>Discrepancy</TableCell>
            <TableCell className={summary.difference !== 0 ? "text-red-500 font-bold" : ""}>{summary.difference}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
      <Button className="mt-4" onClick={onPrint}>Print Z-Report</Button>
    </Card>
  )
}
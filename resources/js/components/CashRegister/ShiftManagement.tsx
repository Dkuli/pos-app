import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"

export function ShiftManagement({ sessions, onView, onClose }) {
  return (
    <div>
      <div className="font-bold mb-2">Register Shifts</div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Register</TableHead>
            <TableHead>User</TableHead>
            <TableHead>Opened</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {sessions.map(s => (
            <TableRow key={s.id}>
              <TableCell>{s.cashRegister?.name}</TableCell>
              <TableCell>{s.user?.name}</TableCell>
              <TableCell>{s.opened_at}</TableCell>
              <TableCell>
                <span className={s.is_active ? "text-green-600 font-bold" : "text-gray-500"}>
                  {s.is_active ? "Open" : "Closed"}
                </span>
              </TableCell>
              <TableCell>
                <Button size="sm" variant="outline" onClick={() => onView(s)}>View</Button>
                {s.is_active && (
                  <Button size="sm" variant="destructive" onClick={() => onClose(s)}>Force Close</Button>
                )}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  )
}
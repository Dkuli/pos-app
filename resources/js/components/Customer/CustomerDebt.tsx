import { Card } from "@/components/ui/card"

export function CustomerDebt({ customer }) {
  const remainingCredit = customer.credit_limit - customer.debt

  return (
    <Card className="p-4">
      <h3 className="font-semibold mb-2">Credit Management</h3>
      <p>Total Credit Limit: {customer.credit_limit}</p>
      <p>Remaining Credit: {remainingCredit}</p>
      <p>Current Debt: {customer.debt}</p>
    </Card>
  )
}
import { Card } from "@/components/ui/card"
import { Progress } from "@/components/ui/progress"

export function BudgetTracking({ budgets }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
      {budgets.map(budget => (
        <Card key={budget.category_id} className="p-4">
          <div className="font-semibold">{budget.category_name}</div>
          <div className="text-xs text-gray-500 mb-2">Budget: {budget.amount}</div>
          <Progress value={budget.used / budget.amount * 100} className="mb-2" />
          <div className="flex justify-between text-xs">
            <span>Used: {budget.used}</span>
            <span>Remaining: {budget.amount - budget.used}</span>
          </div>
        </Card>
      ))}
    </div>
  )
}
import { Progress } from "@/components/ui/progress"
import { Card } from "@/components/ui/card"

export function CustomerPoints({ customer }) {
  const progress = (customer.points / 1000) * 100 // Example: 1000 points = next reward

  return (
    <Card className="p-4">
      <h3 className="font-semibold mb-2">Loyalty Points</h3>
      <Progress value={progress} />
      <p className="text-sm mt-2">{customer.points} / 1000 points</p>
    </Card>
  )
}
import { Card } from "@/components/ui/card"
// Use a chart library like recharts or chart.js for the chart below
import { Line } from "react-chartjs-2"

export function CashFlowChart({ chartData }) {
  return (
    <Card className="p-4">
      <div className="font-bold mb-2">Cash Flow Throughout the Day</div>
      <Line data={chartData} />
    </Card>
  )
}
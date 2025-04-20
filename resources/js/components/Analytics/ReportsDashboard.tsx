// ReportsDashboard.tsx
import { Card } from "@/components/ui/card"

export function ReportsDashboard({ summary }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card className="p-4">
        <div className="text-xs text-gray-500">Total Sales</div>
        <div className="text-2xl font-bold">{summary.total_sales}</div>
      </Card>
      <Card className="p-4">
        <div className="text-xs text-gray-500">Transactions</div>
        <div className="text-2xl font-bold">{summary.transaction_count}</div>
      </Card>
      <Card className="p-4">
        <div className="text-xs text-gray-500">Profit Margin</div>
        <div className="text-2xl font-bold">{summary.profit_margin}%</div>
      </Card>
      <Card className="p-4">
        <div className="text-xs text-gray-500">Low Stock Products</div>
        <div className="text-2xl font-bold">{summary.low_stock_count}</div>
      </Card>
    </div>
  )
}
export function SupplierMetrics({ supplier, history }) {
    return (
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 my-4">
        <div className="bg-white rounded shadow p-4">
          <div className="text-xs text-gray-500">Total Purchases</div>
          <div className="text-xl font-bold">{history.purchase_count}</div>
        </div>
        <div className="bg-white rounded shadow p-4">
          <div className="text-xs text-gray-500">Total Amount</div>
          <div className="text-xl font-bold">{history.total_amount}</div>
        </div>
        <div className="bg-white rounded shadow p-4">
          <div className="text-xs text-gray-500">Average Purchase</div>
          <div className="text-xl font-bold">{history.average_purchase}</div>
        </div>
        <div className="bg-white rounded shadow p-4">
          <div className="text-xs text-gray-500">Outstanding</div>
          <div className="text-xl font-bold">{supplier.getOutstandingBalance()}</div>
        </div>
      </div>
    )
  }

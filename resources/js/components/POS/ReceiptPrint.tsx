import { Button } from "@/components/ui/button"

export function ReceiptPrint({ transaction }) {
  return (
    <div className="max-w-xs mx-auto p-4 bg-white rounded shadow">
      <div className="text-center font-bold text-lg mb-2">Receipt</div>
      <div>No: {transaction.transaction_number}</div>
      <div>Date: {transaction.transaction_date}</div>
      <div>Customer: {transaction.customer?.name ?? "Walk-in"}</div>
      <hr className="my-2" />
      <div>
        {transaction.items.map(item => (
          <div key={item.id} className="flex justify-between">
            <span>{item.product?.name}</span>
            <span>{item.quantity} x {item.price}</span>
          </div>
        ))}
      </div>
      <hr className="my-2" />
      <div className="flex justify-between font-bold">
        <span>Total</span>
        <span>{transaction.total}</span>
      </div>
      <Button className="w-full mt-2" onClick={() => window.print()}>Print</Button>
    </div>
  )
}

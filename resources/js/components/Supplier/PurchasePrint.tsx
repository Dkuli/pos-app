import { Button } from "@/components/ui/button"

export function PurchasePrint({ purchase }) {
  return (
    <div className="max-w-lg mx-auto p-4 bg-white rounded shadow">
      <div className="text-center font-bold text-lg mb-2">Purchase Order</div>
      <div>Reference: {purchase.reference}</div>
      <div>Date: {purchase.date}</div>
      <div>Supplier: {purchase.supplier?.name}</div>
      <hr className="my-2" />
      <div>
        {purchase.items.map(item => (
          <div key={item.id} className="flex justify-between">
            <span>{item.product?.name}</span>
            <span>{item.quantity} x {item.unit_price}</span>
          </div>
        ))}
      </div>
      <hr className="my-2" />
      <div className="flex justify-between font-bold">
        <span>Grand Total</span>
        <span>{purchase.grand_total}</span>
      </div>
      <Button className="w-full mt-2" onClick={() => window.print()}>Print</Button>
      <Button className="w-full mt-2" onClick={() => {/* export logic */}}>Export CSV</Button>
    </div>
  )
}

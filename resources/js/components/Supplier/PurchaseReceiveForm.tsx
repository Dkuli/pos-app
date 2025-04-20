import { useForm } from "@inertiajs/react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

export function PurchaseReceiveForm({ purchase, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    items: purchase.items.map(item => ({
      id: item.id,
      product_id: item.product_id,
      received_quantity: item.quantity, // default to full
    })),
    notes: "",
  })

  function updateReceived(idx, value) {
    setData("items", data.items.map((item, i) => i === idx ? { ...item, received_quantity: value } : item))
  }

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Items to Receive</Label>
        {purchase.items.map((item, idx) => (
          <div key={item.id} className="flex gap-2 items-center mb-2">
            <span className="w-48">{item.product?.name}</span>
            <Input
              type="number"
              min={0}
              max={item.quantity}
              value={data.items[idx].received_quantity}
              onChange={e => updateReceived(idx, e.target.value)}
              className="w-24"
            />
            <span>/ {item.quantity}</span>
          </div>
        ))}
      </div>
      <div>
        <Label>Notes</Label>
        <Input value={data.notes} onChange={e => setData("notes", e.target.value)} />
      </div>
      <Button type="submit" disabled={processing}>Submit Receiving</Button>
    </form>
  )
}

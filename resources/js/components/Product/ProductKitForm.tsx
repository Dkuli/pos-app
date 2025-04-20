import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export function ProductKitForm({ products, kit, onSubmit }) {
  const [items, setItems] = useState(kit?.items ?? [])

  function addItem() {
    setItems([...items, { product_id: "", quantity: 1 }])
  }
  function updateItem(idx, field, value) {
    setItems(items.map((item, i) => i === idx ? { ...item, [field]: value } : item))
  }
  function removeItem(idx) {
    setItems(items.filter((_, i) => i !== idx))
  }

  return (
    <form
      onSubmit={e => {
        e.preventDefault()
        onSubmit({ ...kit, items })
      }}
      className="space-y-4"
    >
      <h3 className="font-semibold">Kit Items</h3>
      {items.map((item, idx) => (
        <div key={idx} className="flex gap-2 items-center">
          <Select value={item.product_id} onValueChange={val => updateItem(idx, "product_id", val)}>
            <SelectTrigger className="w-48">
              <SelectValue placeholder="Select product" />
            </SelectTrigger>
            <SelectContent>
              {products.map(p => (
                <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>
              ))}
            </SelectContent>
          </Select>
          <Input
            type="number"
            min={1}
            value={item.quantity}
            onChange={e => updateItem(idx, "quantity", e.target.value)}
            className="w-24"
          />
          <Button size="xs" variant="destructive" onClick={() => removeItem(idx)}>Remove</Button>
        </div>
      ))}
      <Button type="button" onClick={addItem}>Add Item</Button>
      <Button type="submit">Save Kit</Button>
    </form>
  )
}

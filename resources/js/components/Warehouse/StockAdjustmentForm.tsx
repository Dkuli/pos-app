import { useForm } from "@inertiajs/react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export function StockAdjustmentForm({ warehouses, products, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    warehouse_id: "",
    reference: "",
    date: "",
    adjustment_type: "add",
    notes: "",
    items: [{ product_id: "", quantity: 1, type: "add", reason: "" }],
  })

  function addItem() {
    setData("items", [...data.items, { product_id: "", quantity: 1, type: "add", reason: "" }])
  }

  function updateItem(idx, field, value) {
    setData("items", data.items.map((item, i) => i === idx ? { ...item, [field]: value } : item))
  }

  function removeItem(idx) {
    setData("items", data.items.filter((_, i) => i !== idx))
  }

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Warehouse</Label>
        <Select value={data.warehouse_id} onValueChange={val => setData("warehouse_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select warehouse" /></SelectTrigger>
          <SelectContent>
            {warehouses.map(w => <SelectItem key={w.id} value={w.id}>{w.name}</SelectItem>)}
          </SelectContent>
        </Select>
        {errors.warehouse_id && <div className="text-red-500 text-xs">{errors.warehouse_id}</div>}
      </div>
      <div>
        <Label>Reference</Label>
        <Input value={data.reference} onChange={e => setData("reference", e.target.value)} />
      </div>
      <div>
        <Label>Date</Label>
        <Input type="date" value={data.date} onChange={e => setData("date", e.target.value)} />
      </div>
      <div>
        <Label>Notes</Label>
        <Input value={data.notes} onChange={e => setData("notes", e.target.value)} />
      </div>
      <div>
        <Label>Items</Label>
        {data.items.map((item, idx) => (
          <div key={idx} className="flex gap-2 items-center mb-2">
            <Select value={item.product_id} onValueChange={val => updateItem(idx, "product_id", val)}>
              <SelectTrigger className="w-48"><SelectValue placeholder="Product" /></SelectTrigger>
              <SelectContent>
                {products.map(p => <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>)}
              </SelectContent>
            </Select>
            <Input type="number" min={0.01} value={item.quantity} onChange={e => updateItem(idx, "quantity", e.target.value)} className="w-24" />
            <Select value={item.type} onValueChange={val => updateItem(idx, "type", val)}>
              <SelectTrigger className="w-24"><SelectValue /></SelectTrigger>
              <SelectContent>
                <SelectItem value="add">Add</SelectItem>
                <SelectItem value="subtract">Subtract</SelectItem>
              </SelectContent>
            </Select>
            <Input placeholder="Reason" value={item.reason} onChange={e => updateItem(idx, "reason", e.target.value)} className="w-32" />
            <Button type="button" size="xs" variant="destructive" onClick={() => removeItem(idx)}>Remove</Button>
          </div>
        ))}
        <Button type="button" onClick={addItem}>Add Item</Button>
      </div>
      <Button type="submit" disabled={processing}>Submit Adjustment</Button>
    </form>
  )
}

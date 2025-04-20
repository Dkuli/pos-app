import { useForm } from "@inertiajs/react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export function StockTransferForm({ warehouses, products, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    source_warehouse_id: "",
    destination_warehouse_id: "",
    product_id: "",
    quantity: 1,
    reference: "",
    notes: "",
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Source Warehouse</Label>
        <Select value={data.source_warehouse_id} onValueChange={val => setData("source_warehouse_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select source" /></SelectTrigger>
          <SelectContent>
            {warehouses.map(w => <SelectItem key={w.id} value={w.id}>{w.name}</SelectItem>)}
          </SelectContent>
        </Select>
      </div>
      <div>
        <Label>Destination Warehouse</Label>
        <Select value={data.destination_warehouse_id} onValueChange={val => setData("destination_warehouse_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select destination" /></SelectTrigger>
          <SelectContent>
            {warehouses.map(w => <SelectItem key={w.id} value={w.id}>{w.name}</SelectItem>)}
          </SelectContent>
        </Select>
      </div>
      <div>
        <Label>Product</Label>
        <Select value={data.product_id} onValueChange={val => setData("product_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select product" /></SelectTrigger>
          <SelectContent>
            {products.map(p => <SelectItem key={p.id} value={p.id}>{p.name}</SelectItem>)}
          </SelectContent>
        </Select>
      </div>
      <div>
        <Label>Quantity</Label>
        <Input type="number" min={0.01} value={data.quantity} onChange={e => setData("quantity", e.target.value)} />
      </div>
      <div>
        <Label>Reference</Label>
        <Input value={data.reference} onChange={e => setData("reference", e.target.value)} />
      </div>
      <div>
        <Label>Notes</Label>
        <Input value={data.notes} onChange={e => setData("notes", e.target.value)} />
      </div>
      <Button type="submit" disabled={processing}>Transfer Stock</Button>
    </form>
  )
}

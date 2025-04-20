import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"

export function StoreManagementForm({ stores, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    stores: stores.map(s => ({ ...s })),
  })

  function updateStore(idx, field, value) {
    setData("stores", data.stores.map((s, i) => i === idx ? { ...s, [field]: value } : s))
  }

  function addStore() {
    setData("stores", [...data.stores, { name: "", code: "", address: "" }])
  }

  function removeStore(idx) {
    setData("stores", data.stores.filter((_, i) => i !== idx))
  }

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <Label>Stores</Label>
      {data.stores.map((s, idx) => (
        <div key={idx} className="flex gap-2 items-center mb-2">
          <Input value={s.name} onChange={e => updateStore(idx, "name", e.target.value)} placeholder="Store Name" className="w-48" />
          <Input value={s.code} onChange={e => updateStore(idx, "code", e.target.value)} placeholder="Code" className="w-24" />
          <Input value={s.address} onChange={e => updateStore(idx, "address", e.target.value)} placeholder="Address" className="w-64" />
          <Button type="button" size="xs" variant="destructive" onClick={() => removeStore(idx)}>Remove</Button>
        </div>
      ))}
      <Button type="button" onClick={addStore}>Add Store</Button>
      <Button type="submit" disabled={processing}>Save</Button>
    </form>
  )
}
import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"

export function PaymentMethodsForm({ methods, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    methods: methods.map(m => ({ ...m })),
  })

  function updateMethod(idx, field, value) {
    setData("methods", data.methods.map((m, i) => i === idx ? { ...m, [field]: value } : m))
  }

  function addMethod() {
    setData("methods", [...data.methods, { name: "", type: "" }])
  }

  function removeMethod(idx) {
    setData("methods", data.methods.filter((_, i) => i !== idx))
  }

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <Label>Payment Methods</Label>
      {data.methods.map((m, idx) => (
        <div key={idx} className="flex gap-2 items-center mb-2">
          <Input value={m.name} onChange={e => updateMethod(idx, "name", e.target.value)} placeholder="Method Name" className="w-48" />
          <Input value={m.type} onChange={e => updateMethod(idx, "type", e.target.value)} placeholder="Type" className="w-32" />
          <Button type="button" size="xs" variant="destructive" onClick={() => removeMethod(idx)}>Remove</Button>
        </div>
      ))}
      <Button type="button" onClick={addMethod}>Add Method</Button>
      <Button type="submit" disabled={processing}>Save</Button>
    </form>
  )
}
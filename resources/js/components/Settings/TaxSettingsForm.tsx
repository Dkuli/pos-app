import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"

export function TaxSettingsForm({ taxes, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    taxes: taxes.map(tax => ({ ...tax })),
  })

  function updateTax(idx, field, value) {
    setData("taxes", data.taxes.map((tax, i) => i === idx ? { ...tax, [field]: value } : tax))
  }

  function addTax() {
    setData("taxes", [...data.taxes, { name: "", rate: 0 }])
  }

  function removeTax(idx) {
    setData("taxes", data.taxes.filter((_, i) => i !== idx))
  }

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <Label>Tax Rates</Label>
      {data.taxes.map((tax, idx) => (
        <div key={idx} className="flex gap-2 items-center mb-2">
          <Input value={tax.name} onChange={e => updateTax(idx, "name", e.target.value)} placeholder="Tax Name" className="w-48" />
          <Input type="number" min={0} max={100} value={tax.rate} onChange={e => updateTax(idx, "rate", e.target.value)} className="w-24" />
          <Button type="button" size="xs" variant="destructive" onClick={() => removeTax(idx)}>Remove</Button>
        </div>
      ))}
      <Button type="button" onClick={addTax}>Add Tax</Button>
      <Button type="submit" disabled={processing}>Save</Button>
    </form>
  )
}
import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"

export function CashRegisterOpenForm({ register, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    opening_amount: "",
    opening_note: "",
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Register</Label>
        <div className="font-bold">{register.name}</div>
      </div>
      <div>
        <Label>Opening Amount</Label>
        <Input
          type="number"
          min={0}
          step="0.01"
          value={data.opening_amount}
          onChange={e => setData("opening_amount", e.target.value)}
          required
        />
        {errors.opening_amount && <div className="text-red-500 text-xs">{errors.opening_amount}</div>}
      </div>
      <div>
        <Label>Note (optional)</Label>
        <Input
          value={data.opening_note}
          onChange={e => setData("opening_note", e.target.value)}
        />
      </div>
      <Button type="submit" disabled={processing}>Open Register</Button>
    </form>
  )
}
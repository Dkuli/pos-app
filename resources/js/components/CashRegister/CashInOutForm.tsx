import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export function CashInOutForm({ session, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    type: "in",
    amount: "",
    reason: "",
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Type</Label>
        <Select value={data.type} onValueChange={val => setData("type", val)}>
          <SelectTrigger className="w-32"><SelectValue /></SelectTrigger>
          <SelectContent>
            <SelectItem value="in">Cash In</SelectItem>
            <SelectItem value="out">Cash Out</SelectItem>
          </SelectContent>
        </Select>
      </div>
      <div>
        <Label>Amount</Label>
        <Input
          type="number"
          min={0.01}
          step="0.01"
          value={data.amount}
          onChange={e => setData("amount", e.target.value)}
          required
        />
        {errors.amount && <div className="text-red-500 text-xs">{errors.amount}</div>}
      </div>
      <div>
        <Label>Reason</Label>
        <Input
          value={data.reason}
          onChange={e => setData("reason", e.target.value)}
          required
        />
        {errors.reason && <div className="text-red-500 text-xs">{errors.reason}</div>}
      </div>
      <Button type="submit" disabled={processing}>Submit</Button>
    </form>
  )
}
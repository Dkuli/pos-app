import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

const paymentMethods = [
  { value: "cash", label: "Cash" },
  { value: "card", label: "Card" },
  { value: "bank_transfer", label: "Bank Transfer" },
  { value: "e-wallet", label: "E-Wallet" },
  { value: "other", label: "Other" },
]

export function PaymentForm({ total, onSubmit }) {
  const [payments, setPayments] = useState([{ method: "cash", amount: total }])

  function updatePayment(idx, field, value) {
    setPayments(payments.map((p, i) => i === idx ? { ...p, [field]: value } : p))
  }

  function addPayment() {
    setPayments([...payments, { method: "cash", amount: 0 }])
  }

  function removePayment(idx) {
    setPayments(payments.filter((_, i) => i !== idx))
  }

  const paid = payments.reduce((sum, p) => sum + Number(p.amount), 0)
  const change = paid - total

  return (
    <form
      onSubmit={e => {
        e.preventDefault()
        onSubmit({ payments })
      }}
      className="space-y-4"
    >
      {payments.map((p, idx) => (
        <div key={idx} className="flex gap-2 items-center">
          <Select value={p.method} onValueChange={val => updatePayment(idx, "method", val)}>
            <SelectTrigger className="w-32">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {paymentMethods.map(m => (
                <SelectItem key={m.value} value={m.value}>{m.label}</SelectItem>
              ))}
            </SelectContent>
          </Select>
          <Input
            type="number"
            min={0}
            value={p.amount}
            onChange={e => updatePayment(idx, "amount", e.target.value)}
            className="w-32"
          />
          {payments.length > 1 && (
            <Button type="button" size="xs" variant="destructive" onClick={() => removePayment(idx)}>Remove</Button>
          )}
        </div>
      ))}
      <Button type="button" variant="outline" onClick={addPayment}>+ Add Payment Method</Button>
      <div className="flex justify-between font-bold">
        <span>Paid:</span>
        <span>{paid}</span>
      </div>
      <div className="flex justify-between font-bold">
        <span>Change:</span>
        <span>{change >= 0 ? change : 0}</span>
      </div>
      <Button type="submit" className="w-full" disabled={paid < total}>Complete Sale</Button>
    </form>
  )
}

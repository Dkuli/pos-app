import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export function ExpenseForm({ categories, stores, users, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    store_id: "",
    expense_category_id: "",
    user_id: "",
    reference: "",
    date: "",
    amount: "",
    notes: "",
    receipt: null,
    is_recurring: false,
    recurring_frequency: "",
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4" encType="multipart/form-data">
      <div>
        <Label>Store</Label>
        <Select value={data.store_id} onValueChange={val => setData("store_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select store" /></SelectTrigger>
          <SelectContent>
            {stores.map(s => <SelectItem key={s.id} value={s.id}>{s.name}</SelectItem>)}
          </SelectContent>
        </Select>
        {errors.store_id && <div className="text-red-500 text-xs">{errors.store_id}</div>}
      </div>
      <div>
        <Label>Category</Label>
        <Select value={data.expense_category_id} onValueChange={val => setData("expense_category_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select category" /></SelectTrigger>
          <SelectContent>
            {categories.map(c => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
          </SelectContent>
        </Select>
        {errors.expense_category_id && <div className="text-red-500 text-xs">{errors.expense_category_id}</div>}
      </div>
      <div>
        <Label>User</Label>
        <Select value={data.user_id} onValueChange={val => setData("user_id", val)}>
          <SelectTrigger><SelectValue placeholder="Select user" /></SelectTrigger>
          <SelectContent>
            {users.map(u => <SelectItem key={u.id} value={u.id}>{u.name}</SelectItem>)}
          </SelectContent>
        </Select>
        {errors.user_id && <div className="text-red-500 text-xs">{errors.user_id}</div>}
      </div>
      <div>
        <Label>Reference</Label>
        <Input value={data.reference} onChange={e => setData("reference", e.target.value)} />
      </div>
      <div>
        <Label>Date</Label>
        <Input type="date" value={data.date} onChange={e => setData("date", e.target.value)} required />
      </div>
      <div>
        <Label>Amount</Label>
        <Input type="number" min={0.01} value={data.amount} onChange={e => setData("amount", e.target.value)} required />
        {errors.amount && <div className="text-red-500 text-xs">{errors.amount}</div>}
      </div>
      <div>
        <Label>Notes</Label>
        <Input value={data.notes} onChange={e => setData("notes", e.target.value)} />
      </div>
      <div>
        <Label>Receipt</Label>
        <Input type="file" accept="image/*,application/pdf" onChange={e => setData("receipt", e.target.files?.[0] ?? null)} />
      </div>
      <div>
        <Label>
          <input type="checkbox" checked={data.is_recurring} onChange={e => setData("is_recurring", e.target.checked)} />
          <span className="ml-2">Recurring Expense</span>
        </Label>
        {data.is_recurring && (
          <Select value={data.recurring_frequency} onValueChange={val => setData("recurring_frequency", val)}>
            <SelectTrigger><SelectValue placeholder="Frequency" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="daily">Daily</SelectItem>
              <SelectItem value="weekly">Weekly</SelectItem>
              <SelectItem value="monthly">Monthly</SelectItem>
              <SelectItem value="quarterly">Quarterly</SelectItem>
              <SelectItem value="yearly">Yearly</SelectItem>
            </SelectContent>
          </Select>
        )}
      </div>
      <Button type="submit" disabled={processing}>Save Expense</Button>
    </form>
  )
}
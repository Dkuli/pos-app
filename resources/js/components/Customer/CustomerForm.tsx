import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Switch } from "@/components/ui/switch"

export function CustomerForm({ customer, onSubmit }) {
  const isEdit = !!customer
  const { data, setData, processing, errors } = useForm({
    name: customer?.name ?? "",
    email: customer?.email ?? "",
    phone: customer?.phone ?? "",
    address: customer?.address ?? "",
    city: customer?.city ?? "",
    postal_code: customer?.postal_code ?? "",
    birthdate: customer?.birthdate ?? "",
    membership_level: customer?.membership_level ?? "",
    points: customer?.points ?? 0,
    credit_limit: customer?.credit_limit ?? 0,
    notes: customer?.notes ?? "",
    is_active: customer?.is_active ?? true,
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Name</Label>
        <Input value={data.name} onChange={e => setData("name", e.target.value)} required />
        {errors.name && <div className="text-red-500 text-xs">{errors.name}</div>}
      </div>
      <div>
        <Label>Email</Label>
        <Input type="email" value={data.email} onChange={e => setData("email", e.target.value)} />
        {errors.email && <div className="text-red-500 text-xs">{errors.email}</div>}
      </div>
      <div>
        <Label>Phone</Label>
        <Input value={data.phone} onChange={e => setData("phone", e.target.value)} />
        {errors.phone && <div className="text-red-500 text-xs">{errors.phone}</div>}
      </div>
      <div>
        <Label>Address</Label>
        <Input value={data.address} onChange={e => setData("address", e.target.value)} />
      </div>
      <div>
        <Label>City</Label>
        <Input value={data.city} onChange={e => setData("city", e.target.value)} />
      </div>
      <div>
        <Label>Postal Code</Label>
        <Input value={data.postal_code} onChange={e => setData("postal_code", e.target.value)} />
      </div>
      <div>
        <Label>Birthdate</Label>
        <Input type="date" value={data.birthdate} onChange={e => setData("birthdate", e.target.value)} />
      </div>
      <div>
        <Label>Membership Level</Label>
        <Input value={data.membership_level} onChange={e => setData("membership_level", e.target.value)} />
      </div>
      <div>
        <Label>Points</Label>
        <Input type="number" min={0} value={data.points} onChange={e => setData("points", e.target.value)} />
      </div>
      <div>
        <Label>Credit Limit</Label>
        <Input type="number" min={0} value={data.credit_limit} onChange={e => setData("credit_limit", e.target.value)} />
      </div>
      <div>
        <Label>Notes</Label>
        <Input value={data.notes} onChange={e => setData("notes", e.target.value)} />
      </div>
      <div className="flex items-center gap-2">
        <Switch checked={data.is_active} onCheckedChange={val => setData("is_active", val)} />
        <Label>Active</Label>
      </div>
      <Button type="submit" disabled={processing}>{isEdit ? "Update Customer" : "Create Customer"}</Button>
    </form>
  )
}
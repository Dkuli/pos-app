import { useForm } from "@inertiajs/react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"

export function SupplierForm({ supplier, onSubmit }) {
  const isEdit = !!supplier
  const { data, setData, processing, errors } = useForm({
    name: supplier?.name ?? "",
    company_name: supplier?.company_name ?? "",
    contact_person: supplier?.contact_person ?? "",
    email: supplier?.email ?? "",
    phone: supplier?.phone ?? "",
    address: supplier?.address ?? "",
    payment_terms: supplier?.payment_terms ?? 30,
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Name</Label>
        <Input value={data.name} onChange={e => setData("name", e.target.value)} required />
        {errors.name && <div className="text-red-500 text-xs">{errors.name}</div>}
      </div>
      <div>
        <Label>Company Name</Label>
        <Input value={data.company_name} onChange={e => setData("company_name", e.target.value)} />
      </div>
      <div>
        <Label>Contact Person</Label>
        <Input value={data.contact_person} onChange={e => setData("contact_person", e.target.value)} />
      </div>
      <div>
        <Label>Email</Label>
        <Input type="email" value={data.email} onChange={e => setData("email", e.target.value)} />
      </div>
      <div>
        <Label>Phone</Label>
        <Input value={data.phone} onChange={e => setData("phone", e.target.value)} />
      </div>
      <div>
        <Label>Address</Label>
        <Input value={data.address} onChange={e => setData("address", e.target.value)} />
      </div>
      <div>
        <Label>Payment Terms (days)</Label>
        <Input type="number" min={0} value={data.payment_terms} onChange={e => setData("payment_terms", e.target.value)} />
      </div>
      <Button type="submit" disabled={processing}>{isEdit ? "Update" : "Create"} Supplier</Button>
    </form>
  )
}

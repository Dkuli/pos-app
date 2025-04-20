import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar"

export function GeneralSettingsForm({ settings, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    company_name: settings.company_name ?? "",
    company_email: settings.company_email ?? "",
    company_phone: settings.company_phone ?? "",
    company_address: settings.company_address ?? "",
    currency: settings.currency ?? "IDR",
    default_tax_rate: settings.default_tax_rate ?? 0,
    logo: null,
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4" encType="multipart/form-data">
      <div>
        <Label>Company Name</Label>
        <Input value={data.company_name} onChange={e => setData("company_name", e.target.value)} required />
        {errors.company_name && <div className="text-red-500 text-xs">{errors.company_name}</div>}
      </div>
      <div>
        <Label>Email</Label>
        <Input type="email" value={data.company_email} onChange={e => setData("company_email", e.target.value)} required />
        {errors.company_email && <div className="text-red-500 text-xs">{errors.company_email}</div>}
      </div>
      <div>
        <Label>Phone</Label>
        <Input value={data.company_phone} onChange={e => setData("company_phone", e.target.value)} />
      </div>
      <div>
        <Label>Address</Label>
        <Input value={data.company_address} onChange={e => setData("company_address", e.target.value)} />
      </div>
      <div>
        <Label>Currency</Label>
        <Input value={data.currency} onChange={e => setData("currency", e.target.value)} required />
      </div>
      <div>
        <Label>Default Tax Rate (%)</Label>
        <Input type="number" min={0} max={100} value={data.default_tax_rate} onChange={e => setData("default_tax_rate", e.target.value)} required />
      </div>
      <div>
        <Label>Logo</Label>
        <Input type="file" accept="image/*" onChange={e => setData("logo", e.target.files?.[0] ?? null)} />
        {settings.logo && (
          <Avatar>
            <AvatarImage src={settings.logo} alt="Logo" />
            <AvatarFallback>Logo</AvatarFallback>
          </Avatar>
        )}
      </div>
      <Button type="submit" disabled={processing}>Save</Button>
    </form>
  )
}
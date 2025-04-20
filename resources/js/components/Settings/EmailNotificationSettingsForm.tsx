import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Switch } from "@/components/ui/switch"

export function EmailNotificationSettingsForm({ settings, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    mail_host: settings.mail_host ?? "",
    mail_port: settings.mail_port ?? "",
    mail_username: settings.mail_username ?? "",
    mail_password: "",
    mail_encryption: settings.mail_encryption ?? "",
    mail_from_address: settings.mail_from_address ?? "",
    notify_low_stock: settings.notify_low_stock ?? false,
    notify_out_of_stock: settings.notify_out_of_stock ?? false,
    notify_expiring_products: settings.notify_expiring_products ?? false,
    notify_sales_target: settings.notify_sales_target ?? false,
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Mail Host</Label>
        <Input value={data.mail_host} onChange={e => setData("mail_host", e.target.value)} />
      </div>
      <div>
        <Label>Mail Port</Label>
        <Input value={data.mail_port} onChange={e => setData("mail_port", e.target.value)} />
      </div>
      <div>
        <Label>Mail Username</Label>
        <Input value={data.mail_username} onChange={e => setData("mail_username", e.target.value)} />
      </div>
      <div>
        <Label>Mail Password</Label>
        <Input type="password" value={data.mail_password} onChange={e => setData("mail_password", e.target.value)} />
      </div>
      <div>
        <Label>Mail Encryption</Label>
        <Input value={data.mail_encryption} onChange={e => setData("mail_encryption", e.target.value)} />
      </div>
      <div>
        <Label>Mail From Address</Label>
        <Input value={data.mail_from_address} onChange={e => setData("mail_from_address", e.target.value)} />
      </div>
      <div className="flex flex-col gap-2">
        <div className="flex items-center gap-2">
          <Switch checked={data.notify_low_stock} onCheckedChange={val => setData("notify_low_stock", !!val)} />
          <Label>Notify Low Stock</Label>
        </div>
        <div className="flex items-center gap-2">
          <Switch checked={data.notify_out_of_stock} onCheckedChange={val => setData("notify_out_of_stock", !!val)} />
          <Label>Notify Out of Stock</Label>
        </div>
        <div className="flex items-center gap-2">
          <Switch checked={data.notify_expiring_products} onCheckedChange={val => setData("notify_expiring_products", !!val)} />
          <Label>Notify Expiring Products</Label>
        </div>
        <div className="flex items-center gap-2">
          <Switch checked={data.notify_sales_target} onCheckedChange={val => setData("notify_sales_target", !!val)} />
          <Label>Notify Sales Target</Label>
        </div>
      </div>
      <Button type="submit" disabled={processing}>Save</Button>
    </form>
  )
}
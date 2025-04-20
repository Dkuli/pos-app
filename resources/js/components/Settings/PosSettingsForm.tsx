import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Switch } from "@/components/ui/switch"

export function PosSettingsForm({ settings, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    pos_show_logo: settings.pos_show_logo ?? false,
    pos_receipt_header: settings.pos_receipt_header ?? "",
    pos_receipt_footer: settings.pos_receipt_footer ?? "",
    pos_default_discount: settings.pos_default_discount ?? 0,
    pos_default_tax: settings.pos_default_tax ?? 0,
  })

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div className="flex items-center gap-2">
        <Switch checked={data.pos_show_logo} onCheckedChange={val => setData("pos_show_logo", !!val)} />
        <Label>Show Logo on Receipt</Label>
      </div>
      <div>
        <Label>Receipt Header</Label>
        <Input value={data.pos_receipt_header} onChange={e => setData("pos_receipt_header", e.target.value)} />
      </div>
      <div>
        <Label>Receipt Footer</Label>
        <Input value={data.pos_receipt_footer} onChange={e => setData("pos_receipt_footer", e.target.value)} />
      </div>
      <div>
        <Label>Default Discount (%)</Label>
        <Input type="number" min={0} max={100} value={data.pos_default_discount} onChange={e => setData("pos_default_discount", e.target.value)} />
      </div>
      <div>
        <Label>Default Tax (%)</Label>
        <Input type="number" min={0} max={100} value={data.pos_default_tax} onChange={e => setData("pos_default_tax", e.target.value)} />
      </div>
      <Button type="submit" disabled={processing}>Save</Button>
    </form>
  )
}
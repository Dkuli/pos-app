import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Switch } from "@/components/ui/switch"

export function ProductForm({ product, categories, units, taxes, onSubmit }) {
  const isEdit = !!product
  const { data, setData, post, processing, errors } = useForm({
    name: product?.name ?? "",
    category_id: product?.category_id ?? "",
    unit_id: product?.unit_id ?? "",
    tax_id: product?.tax_id ?? "",
    sku: product?.sku ?? "",
    barcode: product?.barcode ?? "",
    description: product?.description ?? "",
    cost_price: product?.cost_price ?? "",
    selling_price: product?.selling_price ?? "",
    stock_alert_quantity: product?.stock_alert_quantity ?? "",
    is_active: product?.is_active ?? true,
    images: [],
  })

  return (
    <form
      onSubmit={e => {
        e.preventDefault()
        onSubmit(data)
      }}
      className="space-y-4"
      encType="multipart/form-data"
    >
      <div>
        <Label htmlFor="name">Name</Label>
        <Input id="name" value={data.name} onChange={e => setData("name", e.target.value)} required />
        {errors.name && <div className="text-red-500 text-xs">{errors.name}</div>}
      </div>
      <div>
        <Label htmlFor="category_id">Category</Label>
        <Select value={data.category_id} onValueChange={val => setData("category_id", val)}>
          <SelectTrigger>
            <SelectValue placeholder="Select category" />
          </SelectTrigger>
          <SelectContent>
            {categories.map(cat => (
              <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.category_id && <div className="text-red-500 text-xs">{errors.category_id}</div>}
      </div>
      <div>
        <Label htmlFor="unit_id">Unit</Label>
        <Select value={data.unit_id} onValueChange={val => setData("unit_id", val)}>
          <SelectTrigger>
            <SelectValue placeholder="Select unit" />
          </SelectTrigger>
          <SelectContent>
            {units.map(unit => (
              <SelectItem key={unit.id} value={unit.id}>{unit.name}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.unit_id && <div className="text-red-500 text-xs">{errors.unit_id}</div>}
      </div>
      <div>
        <Label htmlFor="tax_id">Tax</Label>
        <Select value={data.tax_id} onValueChange={val => setData("tax_id", val)}>
          <SelectTrigger>
            <SelectValue placeholder="Select tax" />
          </SelectTrigger>
          <SelectContent>
            {taxes.map(tax => (
              <SelectItem key={tax.id} value={tax.id}>{tax.name}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.tax_id && <div className="text-red-500 text-xs">{errors.tax_id}</div>}
      </div>
      <div>
        <Label htmlFor="sku">SKU</Label>
        <Input id="sku" value={data.sku} onChange={e => setData("sku", e.target.value)} />
        {errors.sku && <div className="text-red-500 text-xs">{errors.sku}</div>}
      </div>
      <div>
        <Label htmlFor="barcode">Barcode</Label>
        <Input id="barcode" value={data.barcode} onChange={e => setData("barcode", e.target.value)} />
        {errors.barcode && <div className="text-red-500 text-xs">{errors.barcode}</div>}
      </div>
      <div>
        <Label htmlFor="cost_price">Cost Price</Label>
        <Input id="cost_price" type="number" value={data.cost_price} onChange={e => setData("cost_price", e.target.value)} required />
        {errors.cost_price && <div className="text-red-500 text-xs">{errors.cost_price}</div>}
      </div>
      <div>
        <Label htmlFor="selling_price">Selling Price</Label>
        <Input id="selling_price" type="number" value={data.selling_price} onChange={e => setData("selling_price", e.target.value)} required />
        {errors.selling_price && <div className="text-red-500 text-xs">{errors.selling_price}</div>}
      </div>
      <div>
        <Label htmlFor="stock_alert_quantity">Stock Alert Quantity</Label>
        <Input id="stock_alert_quantity" type="number" value={data.stock_alert_quantity} onChange={e => setData("stock_alert_quantity", e.target.value)} />
        {errors.stock_alert_quantity && <div className="text-red-500 text-xs">{errors.stock_alert_quantity}</div>}
      </div>
      <div>
        <Label htmlFor="images">Images</Label>
        <Input id="images" type="file" multiple accept="image/*" onChange={e => setData("images", Array.from(e.target.files ?? []))} />
        {errors.images && <div className="text-red-500 text-xs">{errors.images}</div>}
      </div>
      <div className="flex items-center gap-2">
        <Switch checked={data.is_active} onCheckedChange={val => setData("is_active", !!val)} />
        <Label>Active</Label>
      </div>
      <Button type="submit" disabled={processing}>
        {isEdit ? "Update Product" : "Create Product"}
      </Button>
    </form>
  )
}

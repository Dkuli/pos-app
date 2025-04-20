import { useState } from "react"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/ui/sheet"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function POSPage({ products, customers, onSubmit, onQuickCustomer }) {
  const [cart, setCart] = useState([])
  const [search, setSearch] = useState("")
  const [selectedCustomer, setSelectedCustomer] = useState("")
  const [discount, setDiscount] = useState(0)
  const [showPayment, setShowPayment] = useState(false)

  function addToCart(product) {
    setCart(prev =>
      prev.some(item => item.id === product.id)
        ? prev.map(item => item.id === product.id ? { ...item, qty: item.qty + 1 } : item)
        : [...prev, { ...product, qty: 1 }]
    )
  }

  function updateQty(id, qty) {
    setCart(cart.map(item => item.id === id ? { ...item, qty } : item))
  }

  function removeItem(id) {
    setCart(cart.filter(item => item.id !== id))
  }

  const filteredProducts = products.filter(p =>
    p.name.toLowerCase().includes(search.toLowerCase()) ||
    p.barcode?.toLowerCase().includes(search.toLowerCase())
  )

  const subtotal = cart.reduce((sum, item) => sum + item.selling_price * item.qty, 0)
  const total = subtotal - discount

  return (
    <div className="flex flex-col md:flex-row gap-4">
      {/* Product Search & List */}
      <div className="flex-1">
        <Input
          placeholder="Search or scan barcode"
          value={search}
          onChange={e => setSearch(e.target.value)}
          className="mb-2"
        />
        <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
          {filteredProducts.map(product => (
            <Button key={product.id} onClick={() => addToCart(product)} className="h-20">
              <div>
                <div className="font-bold">{product.name}</div>
                <div className="text-xs">{product.sku}</div>
                <div className="text-lg">{product.selling_price}</div>
              </div>
            </Button>
          ))}
        </div>
      </div>
      {/* Cart & Checkout */}
      <div className="w-full md:w-96">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Item</TableHead>
              <TableHead>Qty</TableHead>
              <TableHead>Price</TableHead>
              <TableHead>Remove</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {cart.map(item => (
              <TableRow key={item.id}>
                <TableCell>{item.name}</TableCell>
                <TableCell>
                  <Input
                    type="number"
                    min={1}
                    value={item.qty}
                    onChange={e => updateQty(item.id, Number(e.target.value))}
                    className="w-16"
                  />
                </TableCell>
                <TableCell>{item.selling_price * item.qty}</TableCell>
                <TableCell>
                  <Button size="sm" variant="destructive" onClick={() => removeItem(item.id)}>X</Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
        <div className="mt-2 flex flex-col gap-2">
          <Select value={selectedCustomer} onValueChange={setSelectedCustomer}>
            <SelectTrigger>
              <SelectValue placeholder="Select customer" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">Walk-in</SelectItem>
              {customers.map(c => (
                <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>
              ))}
            </SelectContent>
          </Select>
          <Button variant="outline" onClick={onQuickCustomer}>+ Quick Add Customer</Button>
          <Input
            type="number"
            min={0}
            value={discount}
            onChange={e => setDiscount(Number(e.target.value))}
            placeholder="Discount"
          />
          <div className="flex justify-between font-bold">
            <span>Total:</span>
            <span>{total}</span>
          </div>
          <Button className="w-full" onClick={() => setShowPayment(true)} disabled={cart.length === 0}>
            Pay
          </Button>
        </div>
      </div>
      {/* Payment Modal */}
      <Sheet open={showPayment} onOpenChange={setShowPayment}>
        <SheetContent side="right" className="w-full max-w-md">
          <SheetHeader>
            <SheetTitle>Payment</SheetTitle>
          </SheetHeader>
          <PaymentForm total={total} onSubmit={data => { onSubmit({ cart, customer_id: selectedCustomer, discount, ...data }); setShowPayment(false); }} />
        </SheetContent>
      </Sheet>
    </div>
  )
}

// PaymentForm is shown below

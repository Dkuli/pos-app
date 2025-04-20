import { useForm } from "@inertiajs/react"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog"

export function CashRegisterCloseForm({ session, expectedAmount, onSubmit }) {
  const { data, setData, processing, errors } = useForm({
    closing_amount: "",
    closing_note: "",
  })
  const discrepancy = data.closing_amount !== "" ? (parseFloat(data.closing_amount) - expectedAmount) : 0

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(data) }} className="space-y-4">
      <div>
        <Label>Expected Amount</Label>
        <div className="font-bold">{expectedAmount.toFixed(2)}</div>
      </div>
      <div>
        <Label>Counted Closing Amount</Label>
        <Input
          type="number"
          min={0}
          step="0.01"
          value={data.closing_amount}
          onChange={e => setData("closing_amount", e.target.value)}
          required
        />
        {errors.closing_amount && <div className="text-red-500 text-xs">{errors.closing_amount}</div>}
      </div>
      <div>
        <Label>Discrepancy</Label>
        <div className={discrepancy !== 0 ? "text-red-500 font-bold" : ""}>
          {discrepancy.toFixed(2)}
        </div>
      </div>
      <div>
        <Label>Note (optional)</Label>
        <Input
          value={data.closing_note}
          onChange={e => setData("closing_note", e.target.value)}
        />
      </div>
      <Dialog>
        <DialogTrigger asChild>
          <Button type="button" variant="destructive" disabled={processing || discrepancy === 0}>
            Confirm Discrepancy
          </Button>
        </DialogTrigger>
        <DialogContent>
          <div className="mb-4">
            <div className="font-bold text-red-600">Discrepancy Detected</div>
            <div>
              Please confirm and provide a reason for the discrepancy before closing the register.
            </div>
          </div>
          <Button type="submit" disabled={processing}>Close Register</Button>
        </DialogContent>
      </Dialog>
      <Button type="submit" disabled={processing || discrepancy !== 0}>Close Register</Button>
    </form>
  )
}
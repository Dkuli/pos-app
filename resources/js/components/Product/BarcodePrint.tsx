import { Button } from "@/components/ui/button"

export function BarcodePrint({ barcodeValue }) {
  // Use a barcode library like JsBarcode or react-barcode for rendering
  return (
    <div className="flex flex-col items-center gap-2">
      <div id="barcode">
        {/* <Barcode value={barcodeValue} /> */}
        <svg width="120" height="40"><text x="10" y="25">{barcodeValue}</text></svg>
      </div>
      <Button onClick={() => window.print()}>Print Barcode</Button>
    </div>
  )
}

import { Dialog, DialogContent, DialogTrigger } from "@/components/ui/dialog"
import { Button } from "@/components/ui/button"

export function ReceiptViewer({ attachmentUrl }) {
  return (
    <Dialog>
      <DialogTrigger asChild>
        <Button size="sm" variant="outline">View Receipt</Button>
      </DialogTrigger>
      <DialogContent>
        {attachmentUrl.endsWith(".pdf") ? (
          <iframe src={attachmentUrl} className="w-full h-96" title="Receipt PDF" />
        ) : (
          <img src={attachmentUrl} alt="Receipt" className="max-w-full max-h-96 mx-auto" />
        )}
      </DialogContent>
    </Dialog>
  )
}
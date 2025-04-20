import { Button } from "@/components/ui/button"

export function CashDrawerStatus({ isOpen, onOpen, onClose }) {
  return (
    <div className="flex items-center gap-2">
      <span className={isOpen ? "text-green-600 font-bold" : "text-gray-500"}>{isOpen ? "Drawer Open" : "Drawer Closed"}</span>
      <Button size="sm" variant="outline" onClick={isOpen ? onClose : onOpen}>
        {isOpen ? "Close Drawer" : "Open Drawer"}
      </Button>
    </div>
  )
}
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Button } from "@/components/ui/button"

export function ReportFilterBar({ stores, categories, filters, setFilters, onApply }) {
  return (
    <div className="flex flex-wrap gap-2 items-end mb-4">
      <Input
        type="date"
        value={filters.date_from}
        onChange={e => setFilters(f => ({ ...f, date_from: e.target.value }))}
      />
      <Input
        type="date"
        value={filters.date_to}
        onChange={e => setFilters(f => ({ ...f, date_to: e.target.value }))}
      />
      <Select value={filters.store_id} onValueChange={val => setFilters(f => ({ ...f, store_id: val }))}>
        <SelectTrigger className="w-40"><SelectValue placeholder="All Stores" /></SelectTrigger>
        <SelectContent>
          <SelectItem value="">All Stores</SelectItem>
          {stores.map(s => <SelectItem key={s.id} value={s.id}>{s.name}</SelectItem>)}
        </SelectContent>
      </Select>
      <Select value={filters.category_id} onValueChange={val => setFilters(f => ({ ...f, category_id: val }))}>
        <SelectTrigger className="w-40"><SelectValue placeholder="All Categories" /></SelectTrigger>
        <SelectContent>
          <SelectItem value="">All Categories</SelectItem>
          {categories.map(c => <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>)}
        </SelectContent>
      </Select>
      <Button onClick={onApply}>Apply</Button>
    </div>
  )
}
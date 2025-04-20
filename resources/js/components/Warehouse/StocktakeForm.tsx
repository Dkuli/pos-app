import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

export function StocktakeForm({ warehouse, products, onSubmit }) {
  const [counts, setCounts] = useState(products.map(p => ({ product_id: p.id, expected: p.quantity, counted: p.quantity })))

  function updateCount(idx, value) {
    setCounts(counts.map((c, i) => i === idx ? { ...c, counted: value } : c))
  }

  return (
    <form onSubmit={e => { e.preventDefault(); onSubmit(counts) }}>
      <h2 className="font-bold mb-2">Stocktake for {warehouse.name}</h2>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product</TableHead>
            <TableHead>Expected</TableHead>
            <TableHead>Counted</TableHead>
            <TableHead>Difference</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {products.map((p, idx) => (
            <TableRow key={p.id}>
              <TableCell>{p.name}</TableCell>
              <TableCell>{counts[idx].expected}</TableCell>
              <TableCell>
                <Input
                  type="number"
                  value={counts[idx].counted}
                  onChange={e => updateCount(idx, e.target.value)}
                  className="w-24"
                />
              </TableCell>
              <TableCell className={counts[idx].counted != counts[idx].expected ? "text-red-500" : ""}>
                {counts[idx].counted - counts[idx].expected}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <Button type="submit" className="mt-4">Submit Stocktake</Button>
    </form>
  )
}

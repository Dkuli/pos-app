import { useState } from "react"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"
import { Pagination } from "@/components/ui/pagination"

export function ProductCatalog({ products, categories, onFilter }) {
  const [search, setSearch] = useState("")
  const [category, setCategory] = useState("")

  return (
    <div>
      <div className="flex flex-wrap gap-2 mb-4">
        <Input
          placeholder="Search products..."
          value={search}
          onChange={e => setSearch(e.target.value)}
          className="max-w-xs"
        />
        <Select value={category} onValueChange={setCategory}>
          <SelectTrigger className="w-48">
            <SelectValue placeholder="All Categories" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All Categories</SelectItem>
            {categories.map(cat => (
              <SelectItem key={cat.id} value={cat.id}>{cat.name}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        <Button onClick={() => onFilter({ search, category })}>Filter</Button>
      </div>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Category</TableHead>
            <TableHead>SKU</TableHead>
            <TableHead>Barcode</TableHead>
            <TableHead>Stock</TableHead>
            <TableHead>Price</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {products.data.map(product => (
            <TableRow key={product.id}>
              <TableCell>{product.name}</TableCell>
              <TableCell>{product.category?.name}</TableCell>
              <TableCell>{product.sku}</TableCell>
              <TableCell>{product.barcode}</TableCell>
              <TableCell>
                <span className={product.stock_alert_quantity > 0 && product.stock <= product.stock_alert_quantity ? "text-red-500 font-bold" : ""}>
                  {product.stock}
                </span>
              </TableCell>
              <TableCell>{product.selling_price}</TableCell>
              <TableCell>{product.is_active ? "Active" : "Inactive"}</TableCell>
              <TableCell>
                <Button size="sm" variant="outline" asChild>
                  <a href={route("products.show", product.id)}>View</a>
                </Button>
                <Button size="sm" variant="outline" asChild>
                  <a href={route("products.edit", product.id)}>Edit</a>
                </Button>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <Pagination
        currentPage={products.current_page}
        totalPages={products.last_page}
        onPageChange={page => onFilter({ search, category, page })}
      />
    </div>
  )
}

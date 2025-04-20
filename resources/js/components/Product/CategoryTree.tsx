import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"

export function CategoryTree({ categories, onMove, onEdit, onDelete }) {
  // categories: array of { id, name, children: [...] }
  // onMove: (id, newParentId) => void
  // onEdit: (category) => void
  // onDelete: (id) => void

  // For brevity, this is a static tree. Use a drag-and-drop lib for production.
  function renderTree(nodes, parentId = null) {
    return (
      <ul className="ml-4">
        {nodes.filter(n => n.parent_id === parentId).map(cat => (
          <li key={cat.id} className="flex items-center gap-2">
            <span>{cat.name}</span>
            <Button size="xs" variant="outline" onClick={() => onEdit(cat)}>Edit</Button>
            <Button size="xs" variant="destructive" onClick={() => onDelete(cat.id)}>Delete</Button>
            {renderTree(categories, cat.id)}
          </li>
        ))}
      </ul>
    )
  }

  return (
    <div>
      <h3 className="font-semibold mb-2">Category Hierarchy</h3>
      {renderTree(categories)}
    </div>
  )
}

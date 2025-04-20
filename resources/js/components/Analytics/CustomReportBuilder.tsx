import { useState } from "react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

export function CustomReportBuilder({ templates, onSaveTemplate, onLoadTemplate, onBuild }) {
  const [templateName, setTemplateName] = useState("")
  const [selectedTemplate, setSelectedTemplate] = useState("")

  return (
    <Card className="p-4 mb-4">
      <div className="flex flex-wrap gap-2 items-end">
        <Input
          placeholder="Template Name"
          value={templateName}
          onChange={e => setTemplateName(e.target.value)}
        />
        <Button onClick={() => onSaveTemplate(templateName)}>Save Template</Button>
        <Select value={selectedTemplate} onValueChange={val => setSelectedTemplate(val)}>
          <SelectTrigger className="w-48"><SelectValue placeholder="Load Template" /></SelectTrigger>
          <SelectContent>
            {templates.map(t => <SelectItem key={t.id} value={t.id}>{t.name}</SelectItem>)}
          </SelectContent>
        </Select>
        <Button onClick={() => onLoadTemplate(selectedTemplate)}>Load</Button>
        <Button onClick={onBuild}>Build Report</Button>
      </div>
    </Card>
  )
}
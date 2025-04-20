import { useForm } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'

const roles = [
  { value: 'admin', label: 'Admin' },
  { value: 'manager', label: 'Manager' },
  { value: 'cashier', label: 'Cashier' },
  { value: 'inventory', label: 'Inventory' },
]

export function UserForm({ user, onSubmit }) {
  const isEdit = !!user
  const { data, setData, post, processing, errors } = useForm({
    name: user?.name ?? '',
    email: user?.email ?? '',
    phone: user?.phone ?? '',
    role: user?.role ?? '',
    is_active: user?.is_active ?? true,
    avatar: null,
    password: '',
    password_confirmation: '',
  })

  return (
    <form
      onSubmit={e => {
        e.preventDefault()
        onSubmit(data)
      }}
      className="space-y-4"
      encType="multipart/form-data"
    >
      <div>
        <Label htmlFor="name">Name</Label>
        <Input
          id="name"
          value={data.name}
          onChange={e => setData('name', e.target.value)}
          required
        />
        {errors.name && <div className="text-red-500 text-xs">{errors.name}</div>}
      </div>
      <div>
        <Label htmlFor="email">Email</Label>
        <Input
          id="email"
          type="email"
          value={data.email}
          onChange={e => setData('email', e.target.value)}
          required
        />
        {errors.email && <div className="text-red-500 text-xs">{errors.email}</div>}
      </div>
      <div>
        <Label htmlFor="phone">Phone</Label>
        <Input
          id="phone"
          value={data.phone}
          onChange={e => setData('phone', e.target.value)}
        />
        {errors.phone && <div className="text-red-500 text-xs">{errors.phone}</div>}
      </div>
      <div>
        <Label htmlFor="role">Role</Label>
        <Select
          value={data.role}
          onValueChange={val => setData('role', val)}
          required
        >
          <SelectTrigger>
            <SelectValue placeholder="Select role" />
          </SelectTrigger>
          <SelectContent>
            {roles.map(r => (
              <SelectItem key={r.value} value={r.value}>{r.label}</SelectItem>
            ))}
          </SelectContent>
        </Select>
        {errors.role && <div className="text-red-500 text-xs">{errors.role}</div>}
      </div>
      <div>
        <Label htmlFor="avatar">Avatar</Label>
        <Input
          id="avatar"
          type="file"
          accept="image/*"
          onChange={e => setData('avatar', e.target.files?.[0] ?? null)}
        />
        {user?.avatar && (
          <Avatar>
            <AvatarImage src={user.avatar} alt={user.name} />
            <AvatarFallback>{user.name[0]}</AvatarFallback>
          </Avatar>
        )}
        {errors.avatar && <div className="text-red-500 text-xs">{errors.avatar}</div>}
      </div>
      {!isEdit && (
        <>
          <div>
            <Label htmlFor="password">Password</Label>
            <Input
              id="password"
              type="password"
              value={data.password}
              onChange={e => setData('password', e.target.value)}
              required
            />
            {errors.password && <div className="text-red-500 text-xs">{errors.password}</div>}
          </div>
          <div>
            <Label htmlFor="password_confirmation">Confirm Password</Label>
            <Input
              id="password_confirmation"
              type="password"
              value={data.password_confirmation}
              onChange={e => setData('password_confirmation', e.target.value)}
              required
            />
          </div>
        </>
      )}
      <div className="flex items-center gap-2">
        <Switch
          checked={data.is_active}
          onCheckedChange={val => setData('is_active', !!val)}
        />
        <Label>Active</Label>
      </div>
      <Button type="submit" disabled={processing}>
        {isEdit ? 'Update User' : 'Create User'}
      </Button>
    </form>
  )
}

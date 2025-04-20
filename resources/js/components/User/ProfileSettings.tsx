import { useForm } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'

export function ProfileSettings({ user }) {
  const { data, setData, post, processing, errors } = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone,
    avatar: null,
    current_password: '',
    password: '',
    password_confirmation: '',
  })

  return (
    <form
      onSubmit={e => {
        e.preventDefault()
        post(route('profile.update'))
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
      </div>
      <div>
        <Label htmlFor="phone">Phone</Label>
        <Input
          id="phone"
          value={data.phone}
          onChange={e => setData('phone', e.target.value)}
        />
      </div>
      <div>
        <Label htmlFor="avatar">Avatar</Label>
        <Input
          id="avatar"
          type="file"
          accept="image/*"
          onChange={e => setData('avatar', e.target.files?.[0] ?? null)}
        />
        <Avatar>
          <AvatarImage src={user.avatar} alt={user.name} />
          <AvatarFallback>{user.name[0]}</AvatarFallback>
        </Avatar>
      </div>
      <div>
        <Label htmlFor="current_password">Current Password</Label>
        <Input
          id="current_password"
          type="password"
          value={data.current_password}
          onChange={e => setData('current_password', e.target.value)}
        />
      </div>
      <div>
        <Label htmlFor="password">New Password</Label>
        <Input
          id="password"
          type="password"
          value={data.password}
          onChange={e => setData('password', e.target.value)}
        />
      </div>
      <div>
        <Label htmlFor="password_confirmation">Confirm New Password</Label>
        <Input
          id="password_confirmation"
          type="password"
          value={data.password_confirmation}
          onChange={e => setData('password_confirmation', e.target.value)}
        />
      </div>
      <Button type="submit" disabled={processing}>
        Update Profile
      </Button>
    </form>
  )
}

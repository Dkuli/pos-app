// LoginPage.tsx
import { useForm, Head } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import AuthLayout from '@/layouts/auth-layout'

export default function LoginPage({ status, canResetPassword }) {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
  })

  return (
    <AuthLayout title="Sign in">
      <Head title="Login" />
      <form
        onSubmit={e => {
          e.preventDefault()
          post(route('login'))
        }}
        className="space-y-6"
      >
        <div>
          <Label htmlFor="email">Email</Label>
          <Input
            id="email"
            type="email"
            value={data.email}
            onChange={e => setData('email', e.target.value)}
            required
            autoFocus
          />
          {errors.email && <div className="text-red-500 text-xs">{errors.email}</div>}
        </div>
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
        <div className="flex items-center justify-between">
          <Checkbox
            id="remember"
            checked={data.remember}
            onCheckedChange={val => setData('remember', !!val)}
          />
          <Label htmlFor="remember" className="ml-2">Remember me</Label>
          {canResetPassword && (
            <a href={route('password.request')} className="ml-auto text-sm text-blue-600">Forgot?</a>
          )}
        </div>
        <Button type="submit" className="w-full" disabled={processing}>
          Login
        </Button>
      </form>
    </AuthLayout>
  )
}

import { Outlet } from 'react-router-dom'
import AppShell from './AppShell'

export default function AuthLayout() {
  return (
    <AppShell variant="auth">
      <Outlet />
    </AppShell>
  )
}

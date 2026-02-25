import { Outlet, useLocation } from 'react-router-dom'
import Navbar from '@/components/Navbar'
import Footer from '@/components/Footer'
import AppShell from './AppShell'

export default function PublicLayout() {
  const location = useLocation()
  const isHomeRoute = location.pathname === '/'

  return (
    <AppShell
      header={<Navbar />}
      footer={<Footer />}
      containerClassName={isHomeRoute ? 'max-w-none' : ''}
      mainClassName={isHomeRoute ? '!px-0 !py-0' : ''}
    >
      <Outlet />
    </AppShell>
  )
}

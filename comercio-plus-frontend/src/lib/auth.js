export function saveAuth(token, user) {
  if (token) localStorage.setItem('cplus_token', token)
  if (user) localStorage.setItem('cplus_user', JSON.stringify(user))
}

export function getToken() {
  return localStorage.getItem('cplus_token') || ''
}

export function getUser() {
  try { 
    return JSON.parse(localStorage.getItem('cplus_user') || 'null') 
  } catch { 
    return null 
  }
}

export function clearAuth() {
  localStorage.removeItem('cplus_token')
  localStorage.removeItem('cplus_user')
}

export function primaryRole(user){
  const list = Array.isArray(user?.roles) ? user.roles.map(r => (r?.name ?? r)?.toString().toLowerCase()) : []
  if (list.includes('superadmin')) return 'superadmin'
  if (list.includes('comerciante') || list.includes('merchant')) return 'comerciante'
  if (list.includes('cliente') || list.includes('customer') || list.includes('usuario')) return 'cliente'
  if (typeof user?.role === 'string') return user.role.toLowerCase()
  return 'cliente'
}

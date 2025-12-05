import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '../types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = computed(() => !!user.value)

  const login = async (email: string, password: string) => {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    })

    if (!response.ok) {
      throw new Error('Login failed')
    }

    const data = await response.json()
    user.value = data.user
  }

  const logout = async () => {
    await fetch('/api/logout', { method: 'POST' })
    user.value = null
  }

const register = async (name: string, email: string, password: string, role: string) => {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, password, role })
    })

    if (!response.ok) {
      throw new Error('Registration failed')
    }

    const data = await response.json()
    user.value = data.user
  }

  return {
    user,
    isAuthenticated,
    login,
    logout,
    register
  }
})

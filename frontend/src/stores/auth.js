import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/api/axios'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const loading = ref(false)

  const isLoggedIn = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isAgent = computed(() => user.value?.role === 'agent')

  const setUser = (userData) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData))
  }

  const updateUser = (partial) => {
    user.value = { ...user.value, ...partial }
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  const checkSession = async () => {
    try {
      const { data } = await axios.get('/auth/check-session.php')
      if (data.success) {
        setUser(data.data.user)
        return data.data.user
      }
    } catch (e) {
      clearUser()
    }
    return null
  }

  const logout = async () => {
    try {
      await axios.post('/auth/logout.php')
    } catch (e) {}
    clearUser()
  }

  const clearUser = () => {
    user.value = null
    localStorage.removeItem('user')
  }

  return {
    user,
    loading,
    isLoggedIn,
    isAdmin,
    isAgent,
    setUser,
    updateUser,
    checkSession,
    logout,
    clearUser
  }
})
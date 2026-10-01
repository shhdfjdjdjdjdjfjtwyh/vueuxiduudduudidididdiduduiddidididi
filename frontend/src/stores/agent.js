import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/api/axios'

export const useAgentStore = defineStore('agent', () => {
  const stats = ref({})
  const users = ref([])
  const loading = ref(false)

  const fetchStats = async () => {
    const { data } = await axios.get('/agent/dashboard/stats.php')
    if (data.success) stats.value = data.data.stats
    return data
  }

  const fetchUsers = async (params = {}) => {
    loading.value = true
    try {
      const { data } = await axios.get('/agent/users/get-my-users.php', { params })
      if (data.success) users.value = data.data.users
      return data
    } finally { loading.value = false }
  }

  return { stats, users, loading, fetchStats, fetchUsers }
})

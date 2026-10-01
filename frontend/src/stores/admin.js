import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/api/axios'

export const useAdminStore = defineStore('admin', () => {
  const stats = ref({})
  const loading = ref(false)

  const fetchStats = async () => {
    loading.value = true
    try {
      const { data } = await axios.get('/admin/dashboard/stats.php')
      if (data.success) stats.value = data.data.stats
      return data
    } finally { loading.value = false }
  }

  return { stats, loading, fetchStats }
})

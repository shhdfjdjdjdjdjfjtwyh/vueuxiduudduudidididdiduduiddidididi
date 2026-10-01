import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/api/axios'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref([])
  const unread = ref(0)

  const fetch = async () => {
    const { data } = await axios.get('/notification/get-all.php')
    if (data.success) {
      notifications.value = data.data.notifications
      unread.value = data.data.unread
    }
  }

  const markRead = async (id = null) => {
    await axios.post('/notification/mark-read.php', { id })
    if (id) {
      const n = notifications.value.find(x => x.id === id)
      if (n) n.is_read = true
      unread.value = Math.max(0, unread.value - 1)
    } else {
      notifications.value.forEach(n => n.is_read = true)
      unread.value = 0
    }
  }

  return { notifications, unread, fetch, markRead }
})

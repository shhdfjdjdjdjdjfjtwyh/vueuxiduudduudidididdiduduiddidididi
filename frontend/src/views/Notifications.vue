<template>
  <div class="notif-page">
    <div class="container">
      <div class="page-header">
        <h1>Notifications</h1>
        <button v-if="unread > 0" @click="markAllRead" class="btn btn-outline">
          Mark all as read
        </button>
      </div>

      <div v-if="loading" class="loading">Loading...</div>

      <div v-else-if="!notifications.length" class="empty">
        <div class="empty-icon">🔔</div>
        <h3>No notifications</h3>
        <p>You'll see updates here</p>
      </div>

      <div v-else class="notif-list">
        <div 
          v-for="n in notifications" 
          :key="n.id" 
          class="notif-item" 
          :class="{ unread: !n.is_read }"
          @click="handleClick(n)">
          
          <div class="notif-icon" :class="getTypeClass(n.type)">
            {{ getTypeIcon(n.type) }}
          </div>

          <div class="notif-content">
            <div class="notif-title">{{ n.title }}</div>
            <div class="notif-message">{{ n.message }}</div>
            <div class="notif-time">{{ timeAgo(n.created_at) }}</div>
          </div>

          <div v-if="!n.is_read" class="notif-dot"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'

const router = useRouter()

const notifications = ref([])
const unread = ref(0)
const loading = ref(true)

const loadNotifications = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/notification/get-all.php')
    if (data.success) {
      notifications.value = data.data.notifications
      unread.value = data.data.unread
    }
  } finally {
    loading.value = false
  }
}

const markAllRead = async () => {
  try {
    await axios.post('/notification/mark-read.php', {})
    notifications.value.forEach(n => n.is_read = true)
    unread.value = 0
  } catch (e) {}
}

const handleClick = async (n) => {
  if (!n.is_read) {
    try {
      await axios.post('/notification/mark-read.php', { id: n.id })
      n.is_read = true
      unread.value = Math.max(0, unread.value - 1)
    } catch (e) {}
  }
  if (n.link) router.push(n.link)
}

const getTypeIcon = (type) => ({
  order: '📦',
  wallet: '💰',
  product: '🛒',
  system: '⚙️',
  promotion: '🎁'
}[type] || '🔔')

const getTypeClass = (type) => type

const timeAgo = (d) => {
  const seconds = Math.floor((new Date() - new Date(d)) / 1000)
  if (seconds < 60) return 'Just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  return `${Math.floor(seconds / 86400)}d ago`
}

onMounted(loadNotifications)
</script>

<style scoped>
.notif-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h1 {
  font-size: 26px;
}

.notif-list {
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.notif-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px 22px;
  border-bottom: 1px solid #f1f3f6;
  cursor: pointer;
  transition: background 0.15s;
  position: relative;
}

.notif-item:last-child {
  border-bottom: none;
}

.notif-item:hover {
  background: #f8f9fa;
}

.notif-item.unread {
  background: #f5f8ff;
}

.notif-icon {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}

.notif-icon.order { background: #e3f2fd; color: #1976d2; }
.notif-icon.wallet { background: #e8f5e9; color: #388e3c; }
.notif-icon.product { background: #fff3e0; color: #f57c00; }
.notif-icon.system { background: #f3e5f5; color: #7b1fa2; }
.notif-icon.promotion { background: #ffebee; color: #c62828; }

.notif-content {
  flex: 1;
  min-width: 0;
}

.notif-title {
  font-size: 15px;
  font-weight: 600;
  color: #212121;
  margin-bottom: 4px;
}

.notif-message {
  font-size: 13px;
  color: #4a4a4a;
  line-height: 1.5;
  margin-bottom: 6px;
  word-wrap: break-word;
}

.notif-time {
  color: #878787;
  font-size: 12px;
}

.notif-dot {
  width: 10px;
  height: 10px;
  background: #2874f0;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 0 8px rgba(40,116,240,0.5);
}

.empty, .loading {
  text-align: center;
  padding: 80px 20px;
  background: white;
  border-radius: 12px;
  color: #878787;
}

.empty-icon {
  font-size: 60px;
  margin-bottom: 16px;
  opacity: 0.4;
}

.empty h3 {
  font-size: 20px;
  color: #212121;
  margin-bottom: 6px;
}
</style>
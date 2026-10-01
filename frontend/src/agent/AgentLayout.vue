<template>
  <div class="agent-layout">
    <!-- Sidebar -->
    <aside class="agent-sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-brand">
        <span class="brand-icon">🎯</span>
        <div>
          <strong>{{ agent?.business_name || agent?.username }}</strong>
          <small>{{ agent?.agent_code }}</small>
        </div>
      </div>

      <nav class="agent-nav">
        <router-link to="/agent/dashboard" class="nav-item">
          <span class="nav-icon">📊</span> Dashboard
        </router-link>
        <router-link to="/agent/users" class="nav-item">
          <span class="nav-icon">👥</span> My Users
        </router-link>
        <router-link to="/agent/orders" class="nav-item">
          <span class="nav-icon">🛒</span> Orders
        </router-link>
        <router-link to="/agent/earnings" class="nav-item">
          <span class="nav-icon">💰</span> Earnings
        </router-link>
        <router-link to="/agent/settings" class="nav-item">
          <span class="nav-icon">⚙️</span> Settings
        </router-link>
        <a @click="logout" class="nav-item logout">
          <span class="nav-icon">🚪</span> Logout
        </a>
      </nav>

      <div class="sidebar-wallet">
        <span>Wallet Balance</span>
        <strong>₹{{ (agent?.wallet_balance || 0).toLocaleString('en-IN') }}</strong>
      </div>
    </aside>

    <!-- Main -->
    <div class="agent-main">
      <header class="agent-header">
        <button @click="sidebarOpen = !sidebarOpen" class="menu-btn">☰</button>
        <h2>{{ pageTitle }}</h2>
        <div class="header-actions">
          <span class="commission-badge">
            {{ agent?.commission_rate }}% commission
          </span>
        </div>
      </header>

      <main class="agent-content">
        <router-view />
      </main>
    </div>

    <div v-if="sidebarOpen" class="overlay" @click="sidebarOpen = false"></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const sidebarOpen = ref(false)
const agent = computed(() => authStore.user)

const pageTitle = computed(() => ({
  '/agent/dashboard': 'Dashboard',
  '/agent/users': 'My Users',
  '/agent/orders': 'Orders',
  '/agent/earnings': 'Earnings',
  '/agent/settings': 'Settings'
}[route.path] || 'Agent Panel'))

const loadAgent = async () => {
  try {
    const { data } = await axios.get('/agent/auth/check-session.php')
    if (data.success) {
      authStore.updateUser({ ...data.data.agent, role: 'agent', is_agent: true })
    }
  } catch (e) {}
}

const logout = async () => {
  try {
    await axios.post('/auth/logout.php')
  } catch (e) {}
  authStore.clearUser()
  toast.success('Logged out')
  router.push('/agent')
}

onMounted(loadAgent)
</script>

<style scoped>
.agent-layout {
  display: flex;
  min-height: 100vh;
  background: #0f0f23;
  color: white;
}

.agent-sidebar {
  width: 260px;
  background: #16162e;
  border-right: 1px solid #2a2a4e;
  padding: 20px 12px;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  z-index: 100;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 12px 20px;
  border-bottom: 1px solid #2a2a4e;
  margin-bottom: 20px;
}

.brand-icon {
  font-size: 30px;
}

.sidebar-brand strong {
  display: block;
  font-size: 14px;
}

.sidebar-brand small {
  color: #8787a8;
  font-size: 11px;
}

.agent-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  border-radius: 8px;
  color: #a5a5c5;
  text-decoration: none;
  font-size: 14px;
  transition: all 0.2s;
  cursor: pointer;
}

.nav-item:hover {
  background: #1e1b4b;
  color: white;
}

.nav-item.router-link-active {
  background: linear-gradient(135deg, #fb641b, #f59e0b);
  color: white;
}

.nav-icon {
  font-size: 16px;
}

.logout {
  margin-top: 20px;
  color: #ff6b6b;
}

.logout:hover {
  background: rgba(239,68,68,0.15);
}

.sidebar-wallet {
  padding: 16px;
  background: linear-gradient(135deg, #1e1b4b, #312e81);
  border-radius: 10px;
  text-align: center;
  margin-top: 15px;
}

.sidebar-wallet span {
  display: block;
  color: #a5a5c5;
  font-size: 11px;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.sidebar-wallet strong {
  font-size: 20px;
  color: #fbbf24;
}

.agent-main {
  flex: 1;
  margin-left: 260px;
  min-height: 100vh;
}

.agent-header {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 18px 30px;
  background: #16162e;
  border-bottom: 1px solid #2a2a4e;
  position: sticky;
  top: 0;
  z-index: 50;
}

.agent-header h2 {
  font-size: 20px;
  flex: 1;
}

.menu-btn {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}

.commission-badge {
  background: rgba(251,100,27,0.15);
  color: #fb641b;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
  border: 1px solid rgba(251,100,27,0.3);
}

.agent-content {
  padding: 30px;
}

@media (max-width: 900px) {
  .agent-sidebar {
    left: -260px;
    transition: left 0.3s;
  }
  .agent-sidebar.open {
    left: 0;
  }
  .agent-main {
    margin-left: 0;
  }
  .menu-btn {
    display: block;
  }
  .overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    z-index: 99;
  }
  .agent-content {
    padding: 20px;
  }
}
</style>
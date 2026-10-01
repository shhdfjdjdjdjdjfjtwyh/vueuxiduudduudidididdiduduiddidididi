<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-brand">
        <span class="brand-icon">🛡️</span>
        <div>
          <strong>ShopVault</strong>
          <small>Admin Panel</small>
        </div>
      </div>

      <nav class="admin-nav">
        <router-link to="/admin/dashboard" class="nav-item">
          <span class="nav-icon">📊</span> Dashboard
        </router-link>
        <router-link to="/admin/users" class="nav-item">
          <span class="nav-icon">👥</span> Users
        </router-link>
        <router-link to="/admin/agents" class="nav-item">
          <span class="nav-icon">🎯</span> Agents
        </router-link>
        <router-link to="/admin/products" class="nav-item">
          <span class="nav-icon">📦</span> Products
        </router-link>
        <router-link to="/admin/categories" class="nav-item">
          <span class="nav-icon">📁</span> Categories
        </router-link>
        <router-link to="/admin/orders" class="nav-item">
          <span class="nav-icon">🛒</span> Orders
        </router-link>
        <router-link to="/admin/transactions" class="nav-item">
          <span class="nav-icon">💰</span> Transactions
        </router-link>
        <router-link to="/admin/reports" class="nav-item">
          <span class="nav-icon">📈</span> Reports
        </router-link>
        <router-link to="/admin/support" class="nav-item">
          <span class="nav-icon">💬</span> Support
        </router-link>
        <router-link to="/admin/settings" class="nav-item">
          <span class="nav-icon">⚙️</span> Settings
        </router-link>
        <a @click="logout" class="nav-item logout">
          <span class="nav-icon">🚪</span> Logout
        </a>
      </nav>
    </aside>

    <!-- Main -->
    <div class="admin-main">
      <header class="admin-header">
        <button @click="sidebarOpen = !sidebarOpen" class="menu-btn">☰</button>
        <h2>{{ pageTitle }}</h2>
        <div class="admin-user">
          <span>{{ adminName }}</span>
        </div>
      </header>

      <main class="admin-content">
        <router-view />
      </main>
    </div>

    <div v-if="sidebarOpen" class="overlay" @click="sidebarOpen = false"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const sidebarOpen = ref(false)

const adminName = computed(() => authStore.user?.full_name || 'Admin')

const pageTitle = computed(() => ({
  '/admin/dashboard': 'Dashboard',
  '/admin/users': 'Users Management',
  '/admin/agents': 'Agents Management',
  '/admin/products': 'Products Management',
  '/admin/categories': 'Categories',
  '/admin/orders': 'Orders',
  '/admin/transactions': 'Transactions',
  '/admin/reports': 'Reports',
  '/admin/support': 'Support Tickets',
  '/admin/settings': 'Settings'
}[route.path] || 'Admin'))

const logout = async () => {
  try {
    await axios.post('/auth/logout.php')
  } catch (e) {}
  authStore.clearUser()
  toast.success('Logged out')
  router.push('/admin')
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #0f0f23;
  color: white;
}

.admin-sidebar {
  width: 260px;
  background: #16162e;
  border-right: 1px solid #2a2a4e;
  padding: 20px 12px;
  position: fixed;
  height: 100vh;
  overflow-y: auto;
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
  font-size: 16px;
}

.sidebar-brand small {
  color: #8787a8;
  font-size: 11px;
}

.admin-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
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
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
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
  color: #ff6b6b;
}

.admin-main {
  flex: 1;
  margin-left: 260px;
  min-height: 100vh;
}

.admin-header {
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

.admin-header h2 {
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

.admin-user {
  color: #a5a5c5;
  font-size: 14px;
}

.admin-content {
  padding: 30px;
}

.overlay {
  display: none;
}

@media (max-width: 900px) {
  .admin-sidebar {
    left: -260px;
    transition: left 0.3s;
  }
  .admin-sidebar.open {
    left: 0;
  }
  .admin-main {
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
  .admin-content {
    padding: 20px;
  }
}
</style>
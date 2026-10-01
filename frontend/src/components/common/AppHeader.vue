<template>
  <header class="app-header">
    <div class="header-top">
      <div class="container header-inner">
        <router-link to="/" class="logo">
          <span class="logo-icon">🛍️</span>
          <span class="logo-text">ShopVault</span>
        </router-link>

        <div class="search-box">
          <input 
            v-model="searchQuery" 
            @keyup.enter="doSearch"
            type="text" 
            placeholder="Search for products, brands..."
          >
          <button @click="doSearch" class="search-btn">🔍</button>
        </div>

        <div class="header-actions">
          <router-link v-if="!isLoggedIn" to="/login" class="action-link">
            👤 Login
          </router-link>

          <div v-else class="user-menu">
            <button @click="menuOpen = !menuOpen" class="action-link">
              👤 {{ user?.username }}
            </button>
            <div v-if="menuOpen" class="dropdown">
              <router-link to="/profile">👤 My Profile</router-link>
              <router-link to="/my-orders">📦 My Orders</router-link>
              <router-link to="/wallet">💰 Wallet</router-link>
              <router-link to="/support">💬 Support</router-link>
              <a @click="logout" class="logout-link">🚪 Logout</a>
            </div>
          </div>

          <router-link v-if="isLoggedIn" to="/wallet" class="action-link wallet">
            💰 ₹{{ (user?.balance || 0).toLocaleString('en-IN') }}
          </router-link>

          <router-link to="/cart" class="action-link cart">
            🛒 Cart
            <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Category nav -->
    <div class="header-nav">
      <div class="container nav-inner">
        <router-link to="/products" class="nav-link">All Products</router-link>
        <router-link to="/products?featured=1" class="nav-link">Featured</router-link>
        <router-link to="/products?sort=popular" class="nav-link">Best Sellers</router-link>
        <router-link to="/products?sort=newest" class="nav-link">New Arrivals</router-link>
        <router-link to="/products?sort=discount" class="nav-link">Deals 🔥</router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { toast } from 'vue3-toastify'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const searchQuery = ref('')
const menuOpen = ref(false)

const user = computed(() => authStore.user)
const isLoggedIn = computed(() => authStore.isLoggedIn)
const cartCount = computed(() => cartStore.totalItems)

const doSearch = () => {
  if (!searchQuery.value.trim()) return
  router.push(`/products?search=${encodeURIComponent(searchQuery.value)}`)
}

const logout = async () => {
  await authStore.logout()
  toast.success('Logged out')
  router.push('/')
  menuOpen.value = false
}

const closeMenu = (e) => {
  if (!e.target.closest('.user-menu')) menuOpen.value = false
}

onMounted(() => {
  if (authStore.isLoggedIn) {
    cartStore.loadCart()
  }
  document.addEventListener('click', closeMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', closeMenu)
})
</script>

<style scoped>
.app-header {
  background: #2874f0;
  color: white;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-top {
  padding: 12px 0;
}

.header-inner {
  display: flex;
  align-items: center;
  gap: 20px;
  max-width: 1300px;
  margin: 0 auto;
  padding: 0 20px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  text-decoration: none;
  font-weight: 700;
  font-size: 20px;
  white-space: nowrap;
}

.logo-icon {
  font-size: 26px;
}

.search-box {
  flex: 1;
  display: flex;
  background: white;
  border-radius: 4px;
  overflow: hidden;
  max-width: 600px;
}

.search-box input {
  flex: 1;
  padding: 10px 16px;
  border: none;
  font-size: 14px;
  outline: none;
}

.search-btn {
  padding: 0 20px;
  background: #fb641b;
  color: white;
  border: none;
  font-size: 18px;
  cursor: pointer;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.action-link {
  color: white;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  white-space: nowrap;
  position: relative;
  transition: opacity 0.2s;
}

.action-link:hover {
  opacity: 0.85;
}

.wallet {
  color: #ffeb3b;
  font-weight: 700;
}

.cart-badge {
  position: absolute;
  top: -8px;
  right: -12px;
  background: #fb641b;
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  min-width: 18px;
  text-align: center;
}

.user-menu {
  position: relative;
}

.dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 10px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  min-width: 200px;
  overflow: hidden;
  padding: 6px 0;
}

.dropdown a {
  display: block;
  padding: 11px 18px;
  color: #212121;
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.15s;
}

.dropdown a:hover {
  background: #f1f3f6;
  color: #2874f0;
}

.logout-link {
  color: #ff3f3f !important;
  border-top: 1px solid #e0e0e0;
  margin-top: 6px;
  padding-top: 11px !important;
}

.header-nav {
  background: white;
  border-bottom: 1px solid #e0e0e0;
}

.nav-inner {
  display: flex;
  gap: 28px;
  padding: 12px 20px;
  overflow-x: auto;
  max-width: 1300px;
  margin: 0 auto;
}

.nav-link {
  color: #212121;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  white-space: nowrap;
  transition: color 0.15s;
}

.nav-link:hover {
  color: #2874f0;
}

@media (max-width: 768px) {
  .header-inner {
    flex-wrap: wrap;
  }
  .logo-text { display: none; }
  .search-box { order: 3; width: 100%; max-width: 100%; flex-basis: 100%; margin-top: 10px; }
  .wallet { display: none; }
}
</style>
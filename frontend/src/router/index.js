import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/', component: () => import('@/views/Home.vue') },
  { path: '/login', component: () => import('@/views/Login.vue'), meta: { guest: true } },
  { path: '/signup', component: () => import('@/views/Signup.vue'), meta: { guest: true } },
  { path: '/forgot-password', component: () => import('@/views/ForgotPassword.vue'), meta: { guest: true } },
  { path: '/reset-password', component: () => import('@/views/ResetPassword.vue'), meta: { guest: true } },
  
  // Protected
  { path: '/profile', component: () => import('@/views/Profile.vue'), meta: { auth: true } },
  { path: '/wallet', component: () => import('@/views/Wallet.vue'), meta: { auth: true } },
  { path: '/my-orders', component: () => import('@/views/MyOrders.vue'), meta: { auth: true } },
  
  // Admin
  { path: '/admin', component: () => import('@/admin/AdminLogin.vue'), meta: { admin: true } },
  { path: '/admin/dashboard', component: () => import('@/admin/AdminDashboard.vue'), meta: { admin: true, auth: true } },
  
  // Agent
  { path: '/agent', component: () => import('@/agent/AgentLogin.vue'), meta: { agent: true } },
  { path: '/agent/dashboard', component: () => import('@/agent/AgentDashboard.vue'), meta: { agent: true, auth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 })
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  // Guest only (login/signup)
  if (to.meta.guest && auth.isLoggedIn) {
    return next('/')
  }

  // Auth required
  if (to.meta.auth && !auth.isLoggedIn) {
    return next(`/login?redirect=${to.path}`)
  }

  // Admin
  if (to.meta.admin && !auth.isAdmin) {
    return next('/admin')
  }

  next()
})

export default router
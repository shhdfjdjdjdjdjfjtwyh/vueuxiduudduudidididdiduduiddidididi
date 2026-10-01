export default [
  { path: '/admin', component: () => import('@/admin/AdminLogin.vue'), meta: { admin: true } },
  {
    path: '/admin',
    component: () => import('@/admin/AdminLayout.vue'),
    meta: { admin: true, auth: true },
    children: [
      { path: 'dashboard', component: () => import('@/admin/AdminDashboard.vue') },
      { path: 'users', component: () => import('@/admin/AdminUsers.vue') },
      { path: 'products', component: () => import('@/admin/AdminProducts.vue') },
      { path: 'orders', component: () => import('@/admin/AdminOrders.vue') },
      { path: 'agents', component: () => import('@/admin/AdminAgents.vue') },
      { path: 'transactions', component: () => import('@/admin/AdminTransactions.vue') },
      { path: 'settings', component: () => import('@/admin/AdminSettings.vue') }
    ]
  }
]

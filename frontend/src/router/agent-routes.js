export default [
  { path: '/agent', component: () => import('@/agent/AgentLogin.vue'), meta: { agent: true } },
  {
    path: '/agent',
    component: () => import('@/agent/AgentLayout.vue'),
    meta: { agent: true, auth: true },
    children: [
      { path: 'dashboard', component: () => import('@/agent/AgentDashboard.vue') },
      { path: 'users', component: () => import('@/agent/AgentUsers.vue') },
      { path: 'orders', component: () => import('@/agent/AgentOrders.vue') },
      { path: 'earnings', component: () => import('@/agent/AgentEarnings.vue') },
      { path: 'settings', component: () => import('@/agent/AgentSettings.vue') }
    ]
  }
]

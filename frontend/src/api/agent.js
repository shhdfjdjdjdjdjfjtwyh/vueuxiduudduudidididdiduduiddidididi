import axios from './axios'
export const agentApi = {
  login: (data) => axios.post('/agent/auth/login.php', data),
  checkSession: () => axios.get('/agent/auth/check-session.php'),
  getStats: () => axios.get('/agent/dashboard/stats.php'),
  getUsers: (params) => axios.get('/agent/users/get-my-users.php', { params }),
  createUser: (data) => axios.post('/agent/users/create.php', data),
  rechargeUser: (data) => axios.post('/agent/users/recharge.php', data),
  getOrders: (params) => axios.get('/agent/orders/get-all.php', { params }),
  getEarnings: () => axios.get('/agent/earnings/summary.php'),
  getTransactions: (params) => axios.get('/agent/earnings/transactions.php', { params }),
  withdraw: (data) => axios.post('/agent/earnings/withdraw.php', data)
}

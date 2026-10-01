import axios from './axios'
export const adminApi = {
  login: (data) => axios.post('/admin/auth/login.php', data),
  checkSession: () => axios.get('/admin/auth/check-session.php'),
  getStats: () => axios.get('/admin/dashboard/stats.php'),
  getUsers: (params) => axios.get('/admin/users/get-all.php', { params }),
  getUser: (id) => axios.get(`/admin/users/get-one.php?id=${id}`),
  rechargeUser: (data) => axios.post('/admin/users/recharge.php', data),
  banUser: (data) => axios.post('/admin/users/ban.php', data),
  updateUser: (data) => axios.post('/admin/users/update.php', data),
  getProducts: (params) => axios.get('/admin/products/get-all.php', { params }),
  createProduct: (fd) => axios.post('/admin/products/create.php', fd),
  updateProduct: (fd) => axios.post('/admin/products/update.php', fd),
  deleteProduct: (id) => axios.post('/admin/products/delete.php', { product_id: id }),
  getOrders: (params) => axios.get('/admin/orders/get-all.php', { params }),
  updateOrderStatus: (data) => axios.post('/admin/orders/update-status.php', data),
  getAgents: (params) => axios.get('/admin/agents/get-all.php', { params }),
  createAgent: (data) => axios.post('/admin/agents/create.php', data),
  getTransactions: (params) => axios.get('/admin/transactions/get-all.php', { params }),
  updateSettings: (settings) => axios.post('/admin/settings/update.php', { settings })
}

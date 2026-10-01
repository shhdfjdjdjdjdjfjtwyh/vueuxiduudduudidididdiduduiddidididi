import axios from './axios'
export const ordersApi = {
  create: (data) => axios.post('/order/create.php', data),
  getAll: (params) => axios.get('/order/get-all.php', { params }),
  getOne: (id) => axios.get(`/order/get-one.php?id=${id}`),
  cancel: (orderId) => axios.post('/order/cancel.php', { order_id: orderId })
}

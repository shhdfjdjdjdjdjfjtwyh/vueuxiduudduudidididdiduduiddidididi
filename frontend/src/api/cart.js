import axios from './axios'
export const cartApi = {
  get: () => axios.get('/cart/get.php'),
  add: (data) => axios.post('/cart/add.php', data),
  update: (data) => axios.post('/cart/update.php', data),
  remove: (cartId) => axios.post('/cart/remove.php', { cart_id: cartId }),
  clear: () => axios.post('/cart/clear.php')
}

import axios from './axios'
export const productsApi = {
  getAll: (params) => axios.get('/product/get-all.php', { params }),
  getOne: (id) => axios.get(`/product/get-one.php?id=${id}`),
  getCategories: () => axios.get('/product/get-categories.php'),
  search: (q) => axios.get('/product/search.php', { params: { q } }),
  getReviews: (productId) => axios.get('/product/get-reviews.php', { params: { product_id: productId } }),
  addReview: (data) => axios.post('/product/add-review.php', data)
}

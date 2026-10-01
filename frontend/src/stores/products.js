import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/api/axios'

export const useProductsStore = defineStore('products', () => {
  const products = ref([])
  const currentProduct = ref(null)
  const categories = ref([])
  const loading = ref(false)

  const fetchProducts = async (params = {}) => {
    loading.value = true
    try {
      const { data } = await axios.get('/product/get-all.php', { params })
      if (data.success) products.value = data.data.items
      return data
    } finally { loading.value = false }
  }

  const fetchProduct = async (id) => {
    loading.value = true
    try {
      const { data } = await axios.get(`/product/get-one.php?id=${id}`)
      if (data.success) currentProduct.value = data.data.product
      return data
    } finally { loading.value = false }
  }

  const fetchCategories = async () => {
    const { data } = await axios.get('/product/get-categories.php')
    if (data.success) categories.value = data.data.categories
    return data
  }

  return { products, currentProduct, categories, loading, fetchProducts, fetchProduct, fetchCategories }
})

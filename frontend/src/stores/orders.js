import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from '@/api/axios'

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref([])
  const currentOrder = ref(null)
  const loading = ref(false)

  const fetchOrders = async (params = {}) => {
    loading.value = true
    try {
      const { data } = await axios.get('/order/get-all.php', { params })
      if (data.success) orders.value = data.data.orders
      return data
    } finally { loading.value = false }
  }

  const fetchOrder = async (id) => {
    loading.value = true
    try {
      const { data } = await axios.get(`/order/get-one.php?id=${id}`)
      if (data.success) currentOrder.value = data.data.order
      return data
    } finally { loading.value = false }
  }

  return { orders, currentOrder, loading, fetchOrders, fetchOrder }
})

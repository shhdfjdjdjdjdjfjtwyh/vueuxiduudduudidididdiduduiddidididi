import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/api/axios'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const loading = ref(false)

  const totalItems = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0))

  const subtotal = computed(() => 
    items.value.reduce((sum, i) => sum + (i.price * i.quantity), 0)
  )

  const loadCart = async () => {
    loading.value = true
    try {
      const { data } = await axios.get('/cart/get.php')
      if (data.success) {
        items.value = data.data.items
      }
    } catch (e) {
      console.error('Cart load failed', e)
    } finally {
      loading.value = false
    }
  }

  const addItem = (product, qty = 1) => {
    const existing = items.value.find(i => i.product_id === product.id)
    if (existing) {
      existing.quantity += qty
    } else {
      items.value.push({
        product_id: product.id,
        name: product.name,
        price: product.price,
        main_image: product.main_image,
        quantity: qty
      })
    }
  }

  const updateQty = async (cartId, quantity) => {
    const { data } = await axios.post('/cart/update.php', { cart_id: cartId, quantity })
    if (data.success) {
      const item = items.value.find(i => i.id === cartId)
      if (item) item.quantity = quantity
    }
    return data
  }

  const removeItem = async (cartId) => {
    const { data } = await axios.post('/cart/remove.php', { cart_id: cartId })
    if (data.success) {
      items.value = items.value.filter(i => i.id !== cartId)
    }
    return data
  }

  const clear = () => {
    items.value = []
  }

  return {
    items,
    loading,
    totalItems,
    subtotal,
    loadCart,
    addItem,
    updateQty,
    removeItem,
    clear
  }
})
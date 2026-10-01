<template>
  <div class="cart-page">
    <div class="container">
      <h1>Shopping Cart</h1>

      <div v-if="loading" class="loading">Loading...</div>

      <div v-else-if="cart.items.length === 0" class="empty-cart">
        <img src="@/assets/images/empty-cart.svg" alt="Empty cart">
        <h2>Your cart is empty</h2>
        <p>Add products to get started</p>
        <router-link to="/products" class="btn btn-primary">Continue Shopping</router-link>
      </div>

      <div v-else class="cart-layout">
        <!-- Items -->
        <div class="cart-items">
          <div v-for="item in cart.items" :key="item.id" class="cart-item">
            <img :src="item.main_image" :alt="item.name" class="item-img">
            
            <div class="item-details">
              <router-link :to="`/product/${item.product_id}`" class="item-name">
                {{ item.name }}
              </router-link>
              <div class="item-price">
                <span class="price">₹{{ item.price.toLocaleString('en-IN') }}</span>
                <span v-if="item.mrp > item.price" class="mrp">
                  ₹{{ item.mrp.toLocaleString('en-IN') }}
                </span>
              </div>
              <div v-if="!item.in_stock" class="stock-warning">Out of Stock</div>
            </div>

            <div class="item-qty">
              <button @click="decrease(item)">−</button>
              <span>{{ item.quantity }}</span>
              <button @click="increase(item)">+</button>
            </div>

            <div class="item-total">
              ₹{{ item.line_total.toLocaleString('en-IN') }}
            </div>

            <button @click="remove(item)" class="btn-remove">×</button>
          </div>
        </div>

        <!-- Summary -->
        <aside class="cart-summary">
          <h3>Price Details</h3>
          
          <div class="summary-row">
            <span>Subtotal ({{ cart.totalItems }} items)</span>
            <span>₹{{ cart.subtotal.toLocaleString('en-IN') }}</span>
          </div>
          
          <div class="summary-row">
            <span>Shipping</span>
            <span :class="{ free: cart.shipping === 0 }">
              {{ cart.shipping === 0 ? 'FREE' : '₹' + cart.shipping }}
            </span>
          </div>

          <div class="summary-row">
            <span>Tax (5%)</span>
            <span>₹{{ cart.tax.toFixed(2) }}</span>
          </div>

          <div class="summary-row total">
            <span>Total</span>
            <span>₹{{ cart.total.toFixed(2) }}</span>
          </div>

          <button @click="checkout" class="btn btn-secondary btn-block">
            PROCEED TO CHECKOUT
          </button>

          <p class="secure-note">🔒 Safe and Secure Payments</p>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const loading = ref(true)
const cart = ref({
  items: [],
  subtotal: 0,
  shipping: 0,
  tax: 0,
  total: 0,
  totalItems: 0
})

const loadCart = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/cart/get.php')
    if (data.success) {
      cart.value = data.data
      cartStore.items = data.data.items
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const increase = async (item) => {
  if (item.quantity >= item.stock) {
    toast.warning('Maximum stock reached')
    return
  }
  await cartStore.updateQty(item.id, item.quantity + 1)
  await loadCart()
}

const decrease = async (item) => {
  if (item.quantity <= 1) {
    remove(item)
    return
  }
  await cartStore.updateQty(item.id, item.quantity - 1)
  await loadCart()
}

const remove = async (item) => {
  if (!confirm('Remove this item?')) return
  await cartStore.removeItem(item.id)
  await loadCart()
  toast.success('Removed')
}

const checkout = () => {
  if (!authStore.isLoggedIn) {
    toast.warning('Please login')
    router.push('/login?redirect=/checkout')
    return
  }
  router.push('/checkout')
}

onMounted(loadCart)
</script>

<style scoped>
.cart-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.cart-page h1 {
  font-size: 24px;
  margin-bottom: 20px;
}

.cart-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 20px;
}

.cart-items {
  background: white;
  border-radius: 8px;
  overflow: hidden;
}

.cart-item {
  display: grid;
  grid-template-columns: 100px 1fr auto auto auto;
  gap: 20px;
  padding: 20px;
  align-items: center;
  border-bottom: 1px solid #e0e0e0;
}

.cart-item:last-child {
  border-bottom: none;
}

.item-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
}

.item-details {
  min-width: 0;
}

.item-name {
  display: block;
  font-size: 16px;
  font-weight: 500;
  color: #212121;
  text-decoration: none;
  margin-bottom: 8px;
}

.item-name:hover {
  color: #2874f0;
}

.item-price {
  display: flex;
  gap: 10px;
  align-items: baseline;
}

.price {
  font-size: 16px;
  font-weight: 700;
}

.mrp {
  font-size: 13px;
  color: #878787;
  text-decoration: line-through;
}

.stock-warning {
  color: #ff3f3f;
  font-size: 12px;
  margin-top: 5px;
}

.item-qty {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  overflow: hidden;
}

.item-qty button {
  width: 32px;
  height: 32px;
  background: #f1f3f6;
  border: none;
  font-size: 16px;
  cursor: pointer;
}

.item-qty span {
  padding: 0 12px;
  font-weight: 600;
}

.item-total {
  font-size: 16px;
  font-weight: 700;
}

.btn-remove {
  background: none;
  border: none;
  font-size: 24px;
  color: #878787;
  cursor: pointer;
  padding: 0 8px;
}

.btn-remove:hover {
  color: #ff3f3f;
}

.cart-summary {
  background: white;
  border-radius: 8px;
  padding: 24px;
  height: fit-content;
  position: sticky;
  top: 90px;
}

.cart-summary h3 {
  font-size: 16px;
  color: #878787;
  text-transform: uppercase;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  font-size: 14px;
}

.summary-row.total {
  border-top: 1px dashed #e0e0e0;
  margin-top: 12px;
  padding-top: 16px;
  font-size: 18px;
  font-weight: 700;
}

.free {
  color: #388e3c;
  font-weight: 600;
}

.btn-block {
  width: 100%;
  padding: 14px;
  margin-top: 20px;
  font-size: 15px;
  font-weight: 700;
}

.secure-note {
  text-align: center;
  color: #878787;
  font-size: 12px;
  margin-top: 15px;
}

.empty-cart {
  background: white;
  padding: 80px 20px;
  border-radius: 8px;
  text-align: center;
}

.empty-cart img {
  max-width: 200px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-cart h2 {
  font-size: 22px;
  margin-bottom: 8px;
}

.empty-cart p {
  color: #878787;
  margin-bottom: 20px;
}

.loading {
  text-align: center;
  padding: 60px;
  font-size: 16px;
  color: #878787;
}

@media (max-width: 900px) {
  .cart-layout {
    grid-template-columns: 1fr;
  }
  
  .cart-item {
    grid-template-columns: 80px 1fr;
    gap: 12px;
  }
  
  .item-qty, .item-total, .btn-remove {
    grid-column: 2;
  }
  
  .cart-summary {
    position: static;
  }
}
</style>
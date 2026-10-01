<template>
  <div class="my-orders">
    <div class="container">
      <h1>My Orders</h1>
      <div v-if="!orders.length" class="empty">
        <p>No orders yet</p>
        <router-link to="/products" class="btn btn-primary">Start Shopping</router-link>
      </div>
      <div v-else class="orders-list">
        <div v-for="o in orders" :key="o.id" class="order-card">
          <div class="order-header">
            <div>
              <strong>{{ o.order_number }}</strong>
              <span class="badge" :class="o.status">{{ o.status }}</span>
            </div>
            <span>{{ new Date(o.created_at).toLocaleDateString('en-IN') }}</span>
          </div>
          <div class="order-body">
            <p>{{ o.total_items }} items · ₹{{ parseFloat(o.total_amount).toLocaleString('en-IN') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
const orders = ref([])
onMounted(async () => {
  const { data } = await axios.get('/order/get-all.php')
  if (data.success) orders.value = data.data.orders
})
</script>
<style scoped>
.my-orders{padding:30px 0;background:#f1f3f6;min-height:100vh}
h1{margin-bottom:20px}.empty{text-align:center;padding:60px;background:#fff;border-radius:12px}
.orders-list{display:flex;flex-direction:column;gap:16px}
.order-card{background:#fff;border-radius:12px;padding:20px}
.order-header{display:flex;justify-content:space-between;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e0e0e0}
.badge{padding:3px 10px;border-radius:12px;font-size:11px;background:#f1f3f6;margin-left:8px}
.badge.delivered{background:#e8f5e9;color:#388e3c}
</style>

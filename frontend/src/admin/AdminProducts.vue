<template>
  <div class="admin-page">
    <div class="page-header">
      <h2>Products ({{ products.length }})</h2>
      <router-link to="/admin/products/new" class="btn-primary">+ Add Product</router-link>
    </div>
    <div class="grid">
      <div v-for="p in products" :key="p.id" class="product-card">
        <img :src="p.main_image || 'https://via.placeholder.com/150'" alt="">
        <h4>{{ p.name }}</h4>
        <p class="price">₹{{ parseFloat(p.price).toLocaleString('en-IN') }}</p>
        <p class="stock">Stock: {{ p.quantity }}</p>
        <button @click="del(p.id)" class="btn-del">Delete</button>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'
const products = ref([])
const load = async () => {
  const { data } = await axios.get('/admin/products/get-all.php')
  if (data.success) products.value = data.data.products || []
}
const del = async (id) => {
  if (!confirm('Delete?')) return
  await axios.post('/admin/products/delete.php', { product_id: id })
  toast.success('Deleted'); load()
}
onMounted(load)
</script>
<style scoped>
.admin-page{padding:20px;color:#fff}.page-header{display:flex;justify-content:space-between;margin-bottom:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}
.product-card{background:#16162e;border:1px solid #2a2a4e;border-radius:12px;padding:14px}
.product-card img{width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:10px}
.product-card h4{color:#fff;font-size:13px;margin-bottom:6px}
.price{color:#10b981;font-weight:700}
.stock{color:#8787a8;font-size:11px}
.btn-del{width:100%;padding:8px;background:rgba(239,68,68,.2);color:#ef4444;border:1px solid rgba(239,68,68,.3);border-radius:6px;cursor:pointer;margin-top:10px}
.btn-primary{padding:10px 20px;background:#6366f1;color:#fff;border-radius:8px;text-decoration:none}
</style>

<template>
  <div class="admin-page">
    <div class="page-header"><h2>Users ({{ total }})</h2></div>
    <input v-model="search" @input="load" placeholder="Search users..." class="search-input">
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>User</th><th>Balance</th><th>Orders</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <tr v-for="u in users" :key="u.id">
            <td><strong>{{ u.username }}</strong><br><small>{{ u.email }}</small></td>
            <td>₹{{ parseFloat(u.balance).toLocaleString('en-IN') }}</td>
            <td>{{ u.total_orders }}</td>
            <td><span class="badge" :class="u.status">{{ u.status }}</span></td>
            <td><button @click="recharge(u)" class="btn-xs">💰 Recharge</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'
const users = ref([]); const total = ref(0); const search = ref('')
const load = async () => {
  const { data } = await axios.get('/admin/users/get-all.php', { params: { search: search.value } })
  if (data.success) { users.value = data.data.users; total.value = data.data.total }
}
const recharge = async (u) => {
  const amt = prompt(`Amount to add for ${u.username}:`)
  if (!amt) return
  const { data } = await axios.post('/admin/users/recharge.php', { user_id: u.id, amount: +amt, action: 'add' })
  if (data.success) { toast.success('Recharged!'); load() }
}
onMounted(load)
</script>
<style scoped>
.admin-page{padding:20px}.page-header{margin-bottom:20px;color:#fff}
.search-input{width:100%;max-width:400px;padding:10px;background:#1e1b4b;border:1px solid #3a3a5e;border-radius:8px;color:#fff;margin-bottom:20px}
.table-wrap{overflow-x:auto;background:#16162e;border-radius:12px}
.data-table{width:100%;border-collapse:collapse}
.data-table th{text-align:left;padding:12px;background:#1e1b4b;color:#8787a8;font-size:11px;text-transform:uppercase}
.data-table td{padding:12px;border-bottom:1px solid #2a2a4e;color:#fff;font-size:13px}
.badge{padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
.badge.active{background:rgba(16,185,129,.2);color:#10b981}
.badge.banned{background:rgba(239,68,68,.2);color:#ef4444}
.btn-xs{padding:5px 10px;background:#1e1b4b;color:#fff;border:1px solid #3a3a5e;border-radius:6px;cursor:pointer;font-size:11px}
</style>

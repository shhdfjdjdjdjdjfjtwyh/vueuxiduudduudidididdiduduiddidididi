<template>
  <div class="admin-page">
    <h2>Orders ({{ total }})</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <tr v-for="o in orders" :key="o.id">
            <td><strong>{{ o.order_number }}</strong></td>
            <td>{{ o.full_name || o.username }}</td>
            <td>₹{{ parseFloat(o.total_amount).toLocaleString('en-IN') }}</td>
            <td><span class="badge" :class="o.status">{{ o.status }}</span></td>
            <td>
              <select @change="updateStatus(o.id, $event.target.value)" :value="o.status">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </td>
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
const orders = ref([]); const total = ref(0)
const load = async () => {
  const { data } = await axios.get('/admin/orders/get-all.php')
  if (data.success) { orders.value = data.data.orders; total.value = data.data.total }
}
const updateStatus = async (id, status) => {
  await axios.post('/admin/orders/update-status.php', { order_id: id, status })
  toast.success('Updated'); load()
}
onMounted(load)
</script>
<style scoped>
.admin-page{padding:20px;color:#fff}.table-wrap{background:#16162e;border-radius:12px;overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{text-align:left;padding:12px;background:#1e1b4b;color:#8787a8;font-size:11px;text-transform:uppercase}
.data-table td{padding:12px;border-bottom:1px solid #2a2a4e;color:#fff;font-size:13px}
.badge{padding:3px 10px;border-radius:12px;font-size:11px;background:#333;color:#fff}
.badge.delivered{background:rgba(16,185,129,.2);color:#10b981}
.badge.pending{background:rgba(245,158,11,.2);color:#f59e0b}
select{padding:5px;background:#1e1b4b;color:#fff;border:1px solid #3a3a5e;border-radius:4px;font-size:12px}
</style>

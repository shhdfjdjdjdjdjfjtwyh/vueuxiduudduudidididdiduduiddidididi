<template>
  <div class="admin-page">
    <h2>Transactions</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
        <tbody>
          <tr v-for="t in transactions" :key="t.id">
            <td>{{ t.username || '—' }}</td>
            <td>{{ t.type }}</td>
            <td :class="{ pos: t.amount > 0, neg: t.amount < 0 }">₹{{ Math.abs(t.amount).toLocaleString('en-IN') }}</td>
            <td>{{ new Date(t.created_at).toLocaleDateString('en-IN') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
const transactions = ref([])
onMounted(async () => {
  const { data } = await axios.get('/admin/transactions/get-all.php')
  if (data.success) transactions.value = data.data.transactions
})
</script>
<style scoped>
.admin-page{padding:20px;color:#fff}.table-wrap{background:#16162e;border-radius:12px;overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}
.data-table th{text-align:left;padding:12px;background:#1e1b4b;color:#8787a8;font-size:11px;text-transform:uppercase}
.data-table td{padding:12px;border-bottom:1px solid #2a2a4e;color:#fff;font-size:13px}
.pos{color:#10b981}.neg{color:#ef4444}
</style>

<template>
  <div class="admin-page">
    <div class="page-header">
      <h2>Agents ({{ agents.length }})</h2>
      <button @click="showCreate = true" class="btn-primary">+ Create Agent</button>
    </div>
    <div class="grid">
      <div v-for="a in agents" :key="a.id" class="agent-card">
        <h4>{{ a.business_name || a.username }}</h4>
        <p>{{ a.agent_code }}</p>
        <p>Users: {{ a.user_count }}</p>
        <p class="earn">₹{{ parseFloat(a.total_earned).toLocaleString('en-IN') }}</p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
const agents = ref([]); const showCreate = ref(false)
const load = async () => {
  const { data } = await axios.get('/admin/agents/get-all.php')
  if (data.success) agents.value = data.data.agents
}
onMounted(load)
</script>
<style scoped>
.admin-page{padding:20px;color:#fff}.page-header{display:flex;justify-content:space-between;margin-bottom:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.agent-card{background:#16162e;border:1px solid #2a2a4e;border-radius:12px;padding:16px;color:#fff}
.agent-card h4{margin-bottom:8px}.agent-card p{color:#8787a8;font-size:12px;margin:4px 0}
.earn{color:#10b981;font-weight:700}
.btn-primary{padding:10px 20px;background:#6366f1;color:#fff;border-radius:8px;border:none;cursor:pointer}
</style>

<template>
  <div class="dashboard">
    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
          <div class="stat-label">Total Users</div>
          <div class="stat-value">{{ stats.total_users?.toLocaleString('en-IN') || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div>
          <div class="stat-label">Total Agents</div>
          <div class="stat-value">{{ stats.total_agents || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div>
          <div class="stat-label">Products</div>
          <div class="stat-value">{{ stats.total_products || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🛒</div>
        <div>
          <div class="stat-label">Orders</div>
          <div class="stat-value">{{ stats.total_orders || 0 }}</div>
        </div>
      </div>
      <div class="stat-card highlight">
        <div class="stat-icon">💰</div>
        <div>
          <div class="stat-label">Total Revenue</div>
          <div class="stat-value">₹{{ (stats.total_revenue || 0).toLocaleString('en-IN') }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div>
          <div class="stat-label">Today's Revenue</div>
          <div class="stat-value">₹{{ (stats.today_revenue || 0).toLocaleString('en-IN') }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div>
          <div class="stat-label">Pending Orders</div>
          <div class="stat-value">{{ stats.pending_orders || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🔥</div>
        <div>
          <div class="stat-label">Today's Orders</div>
          <div class="stat-value">{{ stats.today_orders || 0 }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
      <div class="card-header">
        <h3>Recent Orders</h3>
        <router-link to="/admin/orders" class="link">View All →</router-link>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="o in recentOrders" :key="o.id">
              <td><strong>{{ o.order_number }}</strong></td>
              <td>{{ o.full_name || o.username }}</td>
              <td>₹{{ parseFloat(o.total_amount).toLocaleString('en-IN') }}</td>
              <td>
                <span class="badge" :class="o.status">{{ o.status }}</span>
              </td>
              <td>{{ formatDate(o.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Users -->
    <div class="card">
      <div class="card-header">
        <h3>Recent Signups</h3>
        <router-link to="/admin/users" class="link">View All →</router-link>
      </div>
      <div class="recent-users">
        <div v-for="u in recentUsers" :key="u.id" class="user-row">
          <div class="user-avatar">{{ (u.username || 'U')[0].toUpperCase() }}</div>
          <div class="user-info">
            <strong>{{ u.full_name || u.username }}</strong>
            <small>{{ u.email }}</small>
          </div>
          <div class="user-time">{{ formatDate(u.created_at) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'

const stats = ref({})
const recentOrders = ref([])
const recentUsers = ref([])

const loadStats = async () => {
  try {
    const { data } = await axios.get('/admin/dashboard/stats.php')
    if (data.success) {
      stats.value = data.data.stats
      recentOrders.value = data.data.recent_orders
      recentUsers.value = data.data.recent_users
    }
  } catch (e) {
    console.error(e)
  }
}

const formatDate = (d) => new Date(d).toLocaleDateString('en-IN', {
  day: 'numeric', month: 'short'
})

onMounted(loadStats)
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s;
}

.stat-card:hover {
  border-color: #6366f1;
  transform: translateY(-2px);
}

.stat-card.highlight {
  background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1));
  border-color: #6366f1;
}

.stat-icon {
  font-size: 32px;
}

.stat-label {
  color: #8787a8;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
}

.card {
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-header h3 {
  font-size: 18px;
}

.link {
  color: #6366f1;
  font-size: 13px;
  text-decoration: none;
  font-weight: 600;
}

.table-wrap {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 12px;
  background: #1e1b4b;
  color: #8787a8;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #2a2a4e;
}

.data-table td {
  padding: 12px;
  border-bottom: 1px solid #2a2a4e;
  font-size: 13px;
}

.data-table tr:hover td {
  background: #1a1a3e;
}

.badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.badge.pending { background: rgba(245,158,11,0.2); color: #f59e0b; }
.badge.processing { background: rgba(59,130,246,0.2); color: #3b82f6; }
.badge.shipped { background: rgba(139,92,246,0.2); color: #8b5cf6; }
.badge.delivered { background: rgba(16,185,129,0.2); color: #10b981; }
.badge.cancelled { background: rgba(239,68,68,0.2); color: #ef4444; }

.recent-users {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #2a2a4e;
}

.user-row:last-child {
  border-bottom: none;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-info strong {
  display: block;
  font-size: 14px;
}

.user-info small {
  color: #8787a8;
  font-size: 12px;
}

.user-time {
  color: #8787a8;
  font-size: 12px;
}
</style>
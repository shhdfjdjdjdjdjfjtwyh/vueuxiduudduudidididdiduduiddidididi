<template>
  <div class="agent-dashboard">
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card highlight">
        <div class="stat-icon">💰</div>
        <div>
          <div class="stat-label">Wallet Balance</div>
          <div class="stat-value">₹{{ (stats.wallet_balance || 0).toLocaleString('en-IN') }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💎</div>
        <div>
          <div class="stat-label">Total Earned</div>
          <div class="stat-value">₹{{ (stats.total_earned || 0).toLocaleString('en-IN') }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div>
          <div class="stat-label">This Month</div>
          <div class="stat-value">₹{{ (stats.month_commission || 0).toLocaleString('en-IN') }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
          <div class="stat-label">My Users</div>
          <div class="stat-value">{{ stats.my_users || 0 }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">✨</div>
        <div>
          <div class="stat-label">Today's Users</div>
          <div class="stat-value">{{ stats.today_users || 0 }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🛒</div>
        <div>
          <div class="stat-label">Total Orders</div>
          <div class="stat-value">{{ stats.my_orders || 0 }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💵</div>
        <div>
          <div class="stat-label">Revenue Generated</div>
          <div class="stat-value">₹{{ (stats.my_revenue || 0).toLocaleString('en-IN') }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div>
          <div class="stat-label">Commission Rate</div>
          <div class="stat-value">{{ stats.commission_rate || 0 }}%</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <router-link to="/agent/users?action=new" class="action-card">
        <div class="action-icon">➕</div>
        <div>
          <strong>Add New User</strong>
          <span>Create a user account</span>
        </div>
      </router-link>

      <router-link to="/agent/users" class="action-card">
        <div class="action-icon">💰</div>
        <div>
          <strong>Recharge User</strong>
          <span>Add balance to user</span>
        </div>
      </router-link>

      <router-link to="/agent/earnings" class="action-card">
        <div class="action-icon">🏦</div>
        <div>
          <strong>Withdraw</strong>
          <span>Request payout</span>
        </div>
      </router-link>

      <router-link to="/agent/orders" class="action-card">
        <div class="action-icon">📦</div>
        <div>
          <strong>View Orders</strong>
          <span>Track user orders</span>
        </div>
      </router-link>
    </div>

    <!-- Recent Users -->
    <div class="card">
      <div class="card-header">
        <h3>My Recent Users</h3>
        <router-link to="/agent/users" class="link">View All →</router-link>
      </div>

      <div v-if="!stats.recent_users?.length" class="empty">
        No users yet. <router-link to="/agent/users?action=new">Create one →</router-link>
      </div>

      <div v-else class="recent-list">
        <div v-for="u in stats.recent_users" :key="u.id" class="recent-item">
          <div class="user-avatar">{{ (u.username || 'U')[0].toUpperCase() }}</div>
          <div class="user-info">
            <strong>{{ u.full_name || u.username }}</strong>
            <small>{{ u.email }}</small>
          </div>
          <div class="user-balance">
            ₹{{ parseFloat(u.balance).toLocaleString('en-IN') }}
          </div>
          <div class="user-date">{{ formatDate(u.created_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Commissions -->
    <div class="card">
      <div class="card-header">
        <h3>Recent Commissions</h3>
        <router-link to="/agent/earnings" class="link">View All →</router-link>
      </div>

      <div v-if="!stats.recent_commissions?.length" class="empty">
        No commissions yet
      </div>

      <div v-else class="recent-list">
        <div v-for="c in stats.recent_commissions" :key="c.id" class="recent-item">
          <div class="commission-icon">💎</div>
          <div class="user-info">
            <strong>{{ c.description }}</strong>
            <small>{{ formatDate(c.created_at) }}</small>
          </div>
          <div class="commission-amount">
            +₹{{ parseFloat(c.amount).toLocaleString('en-IN') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'

const stats = ref({})

const loadStats = async () => {
  try {
    const { data } = await axios.get('/agent/dashboard/stats.php')
    if (data.success) stats.value = data.data.stats
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
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 12px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.2s;
}

.stat-card:hover {
  border-color: #fb641b;
  transform: translateY(-2px);
}

.stat-card.highlight {
  background: linear-gradient(135deg, rgba(251,100,27,0.15), rgba(245,158,11,0.1));
  border-color: #fb641b;
}

.stat-icon {
  font-size: 28px;
}

.stat-label {
  color: #8787a8;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 20px;
  font-weight: 800;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
  margin-bottom: 24px;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px;
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 12px;
  text-decoration: none;
  color: white;
  transition: all 0.2s;
}

.action-card:hover {
  border-color: #fb641b;
  background: #1e1b4b;
  transform: translateY(-2px);
}

.action-icon {
  font-size: 26px;
}

.action-card strong {
  display: block;
  font-size: 14px;
  margin-bottom: 2px;
}

.action-card span {
  color: #8787a8;
  font-size: 12px;
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
  color: #fb641b;
  font-size: 13px;
  text-decoration: none;
  font-weight: 600;
}

.recent-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.recent-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 0;
  border-bottom: 1px solid #2a2a4e;
}

.recent-item:last-child {
  border-bottom: none;
}

.user-avatar,
.commission-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
  flex-shrink: 0;
}

.commission-icon {
  background: linear-gradient(135deg, #fb641b, #f59e0b);
  font-size: 20px;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-info strong {
  display: block;
  font-size: 14px;
  margin-bottom: 2px;
}

.user-info small {
  color: #8787a8;
  font-size: 12px;
}

.user-balance {
  color: #10b981;
  font-weight: 700;
  font-size: 14px;
}

.user-date {
  color: #8787a8;
  font-size: 12px;
  min-width: 60px;
  text-align: right;
}

.commission-amount {
  color: #10b981;
  font-weight: 700;
  font-size: 15px;
}

.empty {
  text-align: center;
  padding: 30px;
  color: #8787a8;
  font-size: 14px;
}

.empty a {
  color: #fb641b;
  text-decoration: none;
  font-weight: 600;
}
</style>
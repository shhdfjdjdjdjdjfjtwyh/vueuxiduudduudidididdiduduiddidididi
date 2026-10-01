<template>
  <div class="agent-users">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h2>My Users ({{ total }})</h2>
        <p class="subtitle">Manage users under your account</p>
      </div>
      <button @click="showCreateModal = true" class="btn btn-primary">
        + Create New User
      </button>
    </div>

    <!-- Search -->
    <div class="toolbar">
      <input v-model="search" @input="debouncedSearch" placeholder="Search by name, email, username..." class="search-input" />
    </div>

    <!-- Users Table -->
    <div class="card">
      <div v-if="loading" class="loading">Loading...</div>

      <div v-else-if="!users.length" class="empty">
        <p>No users found</p>
        <button @click="showCreateModal = true" class="btn btn-primary">
          Create Your First User
        </button>
      </div>

      <div v-else class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Balance</th>
              <th>Spent</th>
              <th>Orders</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users" :key="u.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar">{{ (u.username || 'U')[0].toUpperCase() }}</div>
                  <div>
                    <strong>{{ u.full_name || u.username }}</strong>
                    <small>{{ u.email }}</small>
                  </div>
                </div>
              </td>
              <td class="amount">₹{{ parseFloat(u.balance).toLocaleString('en-IN') }}</td>
              <td>₹{{ parseFloat(u.total_spent).toLocaleString('en-IN') }}</td>
              <td>{{ u.total_orders }}</td>
              <td>
                <span class="badge" :class="u.status">{{ u.status }}</span>
              </td>
              <td>
                <button @click="openRecharge(u)" class="btn-xs">💰 Recharge</button>
                <button @click="viewUser(u)" class="btn-xs">👁️ View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pages > 1" class="pagination">
        <button :disabled="page === 1" @click="changePage(page - 1)">← Prev</button>
        <span>Page {{ page }} of {{ pages }}</span>
        <button :disabled="page === pages" @click="changePage(page + 1)">Next →</button>
      </div>
    </div>

    <!-- Create User Modal -->
    <div v-if="showCreateModal" class="modal-backdrop" @click.self="showCreateModal = false">
      <div class="modal">
        <h3>Create New User</h3>

        <form @submit.prevent="createUser">
          <div class="form-group">
            <label>Username *</label>
            <input v-model="newUser.username" required placeholder="john_doe" pattern="[a-zA-Z0-9_]{3,30}">
          </div>

          <div class="form-group">
            <label>Full Name</label>
            <input v-model="newUser.full_name" placeholder="John Doe">
          </div>

          <div class="form-group">
            <label>Email *</label>
            <input v-model="newUser.email" type="email" required placeholder="john@example.com">
          </div>

          <div class="form-group">
            <label>Phone</label>
            <input v-model="newUser.phone" placeholder="9876543210" pattern="[6-9]\d{9}">
          </div>

          <div class="form-group">
            <label>Password *</label>
            <input v-model="newUser.password" type="text" required minlength="6" placeholder="Min 6 chars">
          </div>

          <div class="form-group">
            <label>Initial Balance (Optional)</label>
            <input v-model.number="newUser.initial_balance" type="number" min="0" placeholder="0">
          </div>

          <div class="modal-actions">
            <button type="button" @click="showCreateModal = false" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="creating">
              {{ creating ? 'Creating...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Recharge Modal -->
    <div v-if="showRechargeModal" class="modal-backdrop" @click.self="showRechargeModal = false">
      <div class="modal">
        <h3>Recharge User</h3>
        <p class="modal-subtitle">
          {{ selectedUser?.full_name || selectedUser?.username }}
          • Balance: ₹{{ parseFloat(selectedUser?.balance || 0).toLocaleString('en-IN') }}
        </p>

        <form @submit.prevent="doRecharge">
          <div class="form-group">
            <label>Action</label>
            <div class="radio-group">
              <label class="radio-card" :class="{ active: rechargeForm.action === 'add' }">
                <input type="radio" value="add" v-model="rechargeForm.action">
                ➕ Add Balance
              </label>
              <label class="radio-card" :class="{ active: rechargeForm.action === 'deduct' }">
                <input type="radio" value="deduct" v-model="rechargeForm.action">
                ➖ Deduct
              </label>
            </div>
          </div>

          <div class="form-group">
            <label>Amount</label>
            <input v-model.number="rechargeForm.amount" type="number" min="1" required placeholder="500">
            <div class="quick-amounts">
              <button type="button" @click="rechargeForm.amount = 100">₹100</button>
              <button type="button" @click="rechargeForm.amount = 500">₹500</button>
              <button type="button" @click="rechargeForm.amount = 1000">₹1000</button>
              <button type="button" @click="rechargeForm.amount = 5000">₹5000</button>
            </div>
          </div>

          <div class="form-group">
            <label>Reason</label>
            <input v-model="rechargeForm.reason" placeholder="e.g. Cash payment received">
          </div>

          <div class="modal-actions">
            <button type="button" @click="showRechargeModal = false" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="processing">
              {{ processing ? 'Processing...' : 'Confirm' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'

const route = useRoute()

const users = ref([])
const total = ref(0)
const page = ref(1)
const pages = ref(0)
const search = ref('')
const loading = ref(true)

const showCreateModal = ref(false)
const showRechargeModal = ref(false)
const selectedUser = ref(null)
const creating = ref(false)
const processing = ref(false)

const newUser = reactive({
  username: '',
  email: '',
  password: '',
  full_name: '',
  phone: '',
  initial_balance: 0
})

const rechargeForm = reactive({
  action: 'add',
  amount: 500,
  reason: 'Agent recharge'
})

const loadUsers = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: page.value })
    if (search.value) params.append('search', search.value)

    const { data } = await axios.get(`/agent/users/get-my-users.php?${params}`)
    if (data.success) {
      users.value = data.data.users
      total.value = data.data.total
      pages.value = data.data.pages
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    page.value = 1
    loadUsers()
  }, 400)
}

const changePage = (p) => {
  page.value = p
  loadUsers()
}

const createUser = async () => {
  creating.value = true
  try {
    const { data } = await axios.post('/agent/users/create.php', newUser)
    if (data.success) {
      toast.success('User created! 🎉')
      showCreateModal.value = false
      Object.assign(newUser, {
        username: '', email: '', password: '', full_name: '', phone: '', initial_balance: 0
      })
      await loadUsers()
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed')
  } finally {
    creating.value = false
  }
}

const openRecharge = (user) => {
  selectedUser.value = user
  rechargeForm.action = 'add'
  rechargeForm.amount = 500
  rechargeForm.reason = 'Agent recharge'
  showRechargeModal.value = true
}

const doRecharge = async () => {
  processing.value = true
  try {
    const { data } = await axios.post('/agent/users/recharge.php', {
      user_id: selectedUser.value.id,
      amount: rechargeForm.amount,
      action: rechargeForm.action,
      reason: rechargeForm.reason
    })
    if (data.success) {
      toast.success(`Balance ${rechargeForm.action === 'add' ? 'added' : 'deducted'}!`)
      showRechargeModal.value = false
      await loadUsers()
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed')
  } finally {
    processing.value = false
  }
}

const viewUser = (user) => {
  toast.info(`User ID: ${user.id}`)
}

onMounted(() => {
  loadUsers()
  // Auto-open create if query param
  if (route.query.action === 'new') {
    showCreateModal.value = true
  }
})
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.page-header h2 {
  font-size: 22px;
}

.subtitle {
  color: #8787a8;
  font-size: 13px;
  margin-top: 4px;
}

.toolbar {
  margin-bottom: 20px;
}

.search-input {
  width: 100%;
  max-width: 400px;
  padding: 11px 16px;
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: #fb641b;
}

.card {
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 12px;
  padding: 20px;
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

.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fb641b, #f59e0b);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.user-cell strong {
  display: block;
  font-size: 13px;
  margin-bottom: 2px;
}

.user-cell small {
  color: #8787a8;
  font-size: 11px;
}

.amount {
  color: #10b981;
  font-weight: 700;
}

.badge {
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.badge.active { background: rgba(16,185,129,0.2); color: #10b981; }
.badge.banned { background: rgba(239,68,68,0.2); color: #ef4444; }
.badge.pending { background: rgba(245,158,11,0.2); color: #f59e0b; }

.btn-xs {
  padding: 5px 10px;
  background: #1e1b4b;
  color: white;
  border: 1px solid #3a3a5e;
  border-radius: 6px;
  font-size: 11px;
  cursor: pointer;
  margin-right: 4px;
  transition: all 0.15s;
}

.btn-xs:hover {
  border-color: #fb641b;
  color: #fb641b;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  padding: 20px;
  color: #8787a8;
  font-size: 13px;
}

.pagination button {
  padding: 8px 16px;
  background: #1e1b4b;
  color: white;
  border: 1px solid #3a3a5e;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
}

.pagination button:hover:not(:disabled) {
  border-color: #fb641b;
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.empty, .loading {
  text-align: center;
  padding: 60px 20px;
  color: #8787a8;
}

.empty p {
  margin-bottom: 20px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: #16162e;
  border: 1px solid #2a2a4e;
  border-radius: 16px;
  padding: 30px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal h3 {
  font-size: 20px;
  margin-bottom: 8px;
}

.modal-subtitle {
  color: #8787a8;
  font-size: 13px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  color: #a5a5c5;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 11px 14px;
  background: #0f0f23;
  border: 1px solid #3a3a5e;
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.form-group input:focus {
  outline: none;
  border-color: #fb641b;
}

.radio-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.radio-card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border: 2px solid #2a2a4e;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 13px;
}

.radio-card.active {
  border-color: #fb641b;
  background: rgba(251,100,27,0.1);
}

.quick-amounts {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
  margin-top: 8px;
}

.quick-amounts button {
  padding: 8px;
  background: #1e1b4b;
  color: white;
  border: 1px solid #3a3a5e;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
}

.quick-amounts button:hover {
  border-color: #fb641b;
  color: #fb641b;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 24px;
  justify-content: flex-end;
}
</style>
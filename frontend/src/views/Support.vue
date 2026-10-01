<template>
  <div class="support-page">
    <div class="container">
      <div class="page-header">
        <h1>💬 Support</h1>
        <button v-if="!showForm" @click="showForm = true" class="btn btn-primary">
          + New Ticket
        </button>
      </div>

      <!-- Create Ticket -->
      <div v-if="showForm" class="create-ticket card">
        <h3>Create Support Ticket</h3>
        <form @submit.prevent="submitTicket">
          <div class="form-group">
            <label>Subject *</label>
            <input v-model="form.subject" required placeholder="Brief description" maxlength="200" />
          </div>

          <div class="form-group">
            <label>Priority</label>
            <select v-model="form.priority">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>

          <div class="form-group">
            <label>Message *</label>
            <textarea v-model="form.message" required rows="5" placeholder="Describe your issue..."></textarea>
          </div>

          <div class="form-actions">
            <button type="button" @click="showForm = false" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              {{ submitting ? 'Submitting...' : 'Submit Ticket' }}
            </button>
          </div>
        </form>
      </div>

      <!-- My Tickets -->
      <div class="card">
        <h3>My Tickets</h3>

        <div v-if="loading" class="loading">Loading...</div>

        <div v-else-if="!tickets.length" class="empty">
          <p>No tickets yet</p>
        </div>

        <div v-else class="tickets-list">
          <div v-for="t in tickets" :key="t.id" class="ticket-item">
            <div class="ticket-header">
              <div>
                <strong>#{{ t.ticket_number }}</strong>
                <span class="badge" :class="t.status">{{ t.status }}</span>
                <span class="badge priority" :class="t.priority">{{ t.priority }}</span>
              </div>
              <small>{{ formatDate(t.created_at) }}</small>
            </div>
            <h4>{{ t.subject }}</h4>
            <p class="ticket-meta">{{ t.message_count }} message(s)</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'

const tickets = ref([])
const loading = ref(true)
const showForm = ref(false)
const submitting = ref(false)

const form = reactive({
  subject: '',
  message: '',
  priority: 'medium'
})

const loadTickets = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/support/my-tickets.php')
    if (data.success) tickets.value = data.data.tickets
  } finally {
    loading.value = false
  }
}

const submitTicket = async () => {
  submitting.value = true
  try {
    const { data } = await axios.post('/support/create-ticket.php', form)
    if (data.success) {
      toast.success('Ticket created! We\'ll respond soon.')
      showForm.value = false
      Object.assign(form, { subject: '', message: '', priority: 'medium' })
      loadTickets()
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error('Failed to create ticket')
  } finally {
    submitting.value = false
  }
}

const formatDate = (d) => new Date(d).toLocaleDateString('en-IN', {
  day: 'numeric', month: 'short', year: 'numeric'
})

onMounted(loadTickets)
</script>

<style scoped>
.support-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h1 {
  font-size: 26px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 28px;
  margin-bottom: 20px;
}

.card h3 {
  font-size: 18px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #212121;
  margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2874f0;
  box-shadow: 0 0 0 3px rgba(40,116,240,0.1);
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.tickets-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ticket-item {
  padding: 18px;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  transition: all 0.2s;
}

.ticket-item:hover {
  border-color: #2874f0;
  box-shadow: 0 4px 12px rgba(40,116,240,0.08);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  flex-wrap: wrap;
  gap: 8px;
}

.ticket-header strong {
  font-size: 13px;
  color: #2874f0;
  margin-right: 10px;
}

.badge {
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  margin-right: 6px;
}

.badge.open { background: #e3f2fd; color: #1976d2; }
.badge.in_progress { background: #fff3e0; color: #f57c00; }
.badge.resolved { background: #e8f5e9; color: #388e3c; }
.badge.closed { background: #f5f5f5; color: #757575; }

.priority.urgent { background: #ffebee; color: #c62828; }
.priority.high { background: #fff3e0; color: #e65100; }
.priority.medium { background: #f5f5f5; color: #616161; }
.priority.low { background: #e8f5e9; color: #2e7d32; }

.ticket-item h4 {
  font-size: 15px;
  margin-bottom: 6px;
  color: #212121;
}

.ticket-meta {
  color: #878787;
  font-size: 12px;
}

.ticket-header small {
  color: #878787;
  font-size: 12px;
}

.empty, .loading {
  text-align: center;
  padding: 40px;
  color: #878787;
}
</style>
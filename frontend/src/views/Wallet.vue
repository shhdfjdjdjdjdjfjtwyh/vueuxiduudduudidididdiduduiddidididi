<template>
  <div class="wallet-page">
    <div class="container">
      <h1>My Wallet</h1>

      <!-- Balance Card -->
      <div class="balance-card">
        <div class="balance-info">
          <span>Available Balance</span>
          <h2>₹{{ balance.toLocaleString('en-IN', { minimumFractionDigits: 2 }) }}</h2>
          <div class="stats-row">
            <div class="stat">
              <span>Total Deposited</span>
              <strong>₹{{ totalDeposit.toLocaleString('en-IN') }}</strong>
            </div>
            <div class="stat">
              <span>Total Spent</span>
              <strong>₹{{ totalSpent.toLocaleString('en-IN') }}</strong>
            </div>
          </div>
        </div>
        <div class="balance-actions">
          <button @click="showDepositModal = true" class="btn btn-success">
            + Add Money
          </button>
          <button @click="showWithdrawModal = true" class="btn btn-outline">
            Withdraw
          </button>
        </div>
      </div>

      <!-- Transactions -->
      <div class="transactions-card">
        <div class="tx-header">
          <h3>Recent Transactions</h3>
          <select v-model="filterType" @change="loadTransactions">
            <option value="">All</option>
            <option value="deposit">Deposits</option>
            <option value="purchase">Purchases</option>
            <option value="refund">Refunds</option>
            <option value="withdrawal">Withdrawals</option>
          </select>
        </div>

        <div v-if="loading" class="loading">Loading...</div>

        <div v-else-if="transactions.length === 0" class="empty">
          <p>No transactions yet</p>
        </div>

        <div v-else class="tx-list">
          <div v-for="tx in transactions" :key="tx.id" class="tx-row">
            <div class="tx-icon" :class="txIconClass(tx.type)">
              {{ txIcon(tx.type) }}
            </div>
            <div class="tx-info">
              <strong>{{ txLabel(tx.type) }}</strong>
              <small>{{ tx.description }}</small>
              <small class="date">{{ formatDate(tx.created_at) }}</small>
            </div>
            <div class="tx-amount" :class="{ positive: tx.amount > 0, negative: tx.amount < 0 }">
              {{ tx.amount > 0 ? '+' : '' }}₹{{ Math.abs(tx.amount).toLocaleString('en-IN') }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Deposit Modal -->
    <div v-if="showDepositModal" class="modal-backdrop" @click.self="showDepositModal = false">
      <div class="modal">
        <h3>Add Money to Wallet</h3>
        <p>Choose amount to add</p>

        <div class="quick-amounts">
          <button 
            v-for="amt in [500, 1000, 2000, 5000]" 
            :key="amt"
            :class="{ active: depositAmount === amt }"
            @click="depositAmount = amt">
            ₹{{ amt.toLocaleString('en-IN') }}
          </button>
        </div>

        <input 
          v-model.number="depositAmount" 
          type="number" 
          placeholder="Or enter custom amount"
          class="amount-input"
          :min="100"
        >

        <div class="modal-actions">
          <button @click="showDepositModal = false" class="btn btn-outline">Cancel</button>
          <button @click="initDeposit" class="btn btn-success" :disabled="depositAmount < 100 || processing">
            {{ processing ? 'Processing...' : `Pay ₹${depositAmount}` }}
          </button>
        </div>
      </div>
    </div>

    <!-- Withdraw Modal -->
    <div v-if="showWithdrawModal" class="modal-backdrop" @click.self="showWithdrawModal = false">
      <div class="modal">
        <h3>Withdraw Money</h3>
        <p>Available: ₹{{ balance.toLocaleString('en-IN') }}</p>

        <input 
          v-model.number="withdrawAmount" 
          type="number" 
          placeholder="Amount (min ₹500)"
          class="amount-input"
          :min="500"
        >

        <input 
          v-model="upiId" 
          type="text" 
          placeholder="UPI ID (e.g. yourname@paytm)"
          class="amount-input"
        >

        <p class="fee-note">
          Processing fee: 2% • You'll receive ₹{{ netWithdrawal }}
        </p>

        <div class="modal-actions">
          <button @click="showWithdrawModal = false" class="btn btn-outline">Cancel</button>
          <button @click="requestWithdraw" class="btn btn-primary" :disabled="!canWithdraw || processing">
            Request Withdrawal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const authStore = useAuthStore()

const balance = ref(0)
const totalDeposit = ref(0)
const totalSpent = ref(0)
const transactions = ref([])
const loading = ref(true)
const filterType = ref('')

const showDepositModal = ref(false)
const showWithdrawModal = ref(false)
const depositAmount = ref(1000)
const withdrawAmount = ref(500)
const upiId = ref('')
const processing = ref(false)

const netWithdrawal = computed(() => {
  const fee = withdrawAmount.value * 0.02
  return (withdrawAmount.value - fee).toFixed(2)
})

const canWithdraw = computed(() => 
  withdrawAmount.value >= 500 && 
  withdrawAmount.value <= balance.value && 
  upiId.value.includes('@')
)

const loadWallet = async () => {
  try {
    const { data } = await axios.get('/wallet/balance.php')
    if (data.success) {
      balance.value = data.data.balance
      totalDeposit.value = data.data.total_deposit
      totalSpent.value = data.data.total_spent
      authStore.updateUser({ balance: data.data.balance })
    }
  } catch (e) {}
}

const loadTransactions = async () => {
  loading.value = true
  try {
    const url = filterType.value 
      ? `/wallet/transactions.php?type=${filterType.value}`
      : '/wallet/transactions.php'
    const { data } = await axios.get(url)
    if (data.success) transactions.value = data.data.transactions
  } finally {
    loading.value = false
  }
}

const initDeposit = async () => {
  if (depositAmount.value < 100) {
    toast.warning('Minimum deposit is ₹100')
    return
  }

  processing.value = true
  try {
    const { data } = await axios.post('/wallet/deposit.php', { amount: depositAmount.value })
    
    if (!data.success) {
      toast.error(data.message)
      return
    }

    // Load Razorpay script
    await loadRazorpayScript()

    const options = {
      key: data.data.key,
      amount: data.data.amount,
      currency: 'INR',
      name: 'ShopVault',
      description: 'Wallet Deposit',
      order_id: data.data.razorpay_order_id,
      handler: async (response) => {
        const { data: verify } = await axios.post('/wallet/verify-deposit.php', {
          amount: depositAmount.value,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature
        })

        if (verify.success) {
          balance.value = verify.data.new_balance
          showDepositModal.value = false
          toast.success('Money added! 💰')
          await loadWallet()
          await loadTransactions()
        }
      },
      prefill: {
        name: authStore.user?.full_name,
        email: authStore.user?.email,
        contact: authStore.user?.phone
      },
      theme: { color: '#2874f0' }
    }

    const rzp = new window.Razorpay(options)
    rzp.open()

  } catch (e) {
    toast.error('Failed to process')
  } finally {
    processing.value = false
  }
}

const requestWithdraw = async () => {
  if (!canWithdraw.value) {
    toast.warning('Invalid withdrawal details')
    return
  }

  processing.value = true
  try {
    const { data } = await axios.post('/wallet/withdraw.php', {
      amount: withdrawAmount.value,
      upi_id: upiId.value
    })

    if (data.success) {
      toast.success('Withdrawal requested! Processing in 3-5 days.')
      showWithdrawModal.value = false
      withdrawAmount.value = 500
      upiId.value = ''
      await loadWallet()
      await loadTransactions()
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error('Failed')
  } finally {
    processing.value = false
  }
}

const loadRazorpayScript = () => {
  return new Promise((resolve) => {
    if (window.Razorpay) return resolve()
    const script = document.createElement('script')
    script.src = 'https://checkout.razorpay.com/v1/checkout.js'
    script.onload = resolve
    document.body.appendChild(script)
  })
}

const txIcon = (type) => ({
  deposit: '↓',
  purchase: '🛒',
  refund: '↺',
  withdrawal: '↑',
  commission: '💎',
  admin_credit: '+'
}[type] || '•')

const txIconClass = (type) => type

const txLabel = (type) => ({
  deposit: 'Money Added',
  purchase: 'Purchase',
  refund: 'Refund',
  withdrawal: 'Withdrawal',
  commission: 'Commission',
  admin_credit: 'Admin Credit'
}[type] || type)

const formatDate = (d) => new Date(d).toLocaleDateString('en-IN', {
  day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
})

onMounted(() => {
  loadWallet()
  loadTransactions()
})
</script>

<style scoped>
.wallet-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.wallet-page h1 {
  font-size: 26px;
  margin-bottom: 20px;
}

.balance-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #2874f0, #1a5bb8);
  color: white;
  padding: 35px;
  border-radius: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 20px;
}

.balance-info span {
  opacity: 0.85;
  font-size: 14px;
}

.balance-info h2 {
  font-size: 44px;
  margin: 8px 0 20px;
  font-weight: 800;
}

.stats-row {
  display: flex;
  gap: 40px;
}

.stat span {
  font-size: 12px;
  opacity: 0.8;
  display: block;
}

.stat strong {
  font-size: 18px;
}

.balance-actions {
  display: flex;
  gap: 12px;
  flex-direction: column;
}

.balance-actions .btn {
  padding: 14px 30px;
  font-size: 15px;
  font-weight: 700;
}

.btn-success {
  background: #388e3c;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn-success:hover {
  background: #2e7d32;
}

.transactions-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
}

.tx-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.tx-header h3 {
  font-size: 18px;
}

.tx-header select {
  padding: 8px 14px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 13px;
  cursor: pointer;
}

.tx-list {
  display: flex;
  flex-direction: column;
}

.tx-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 0;
  border-bottom: 1px solid #f1f3f6;
}

.tx-row:last-child {
  border-bottom: none;
}

.tx-icon {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 700;
  flex-shrink: 0;
}

.tx-icon.deposit { background: #e8f5e9; color: #388e3c; }
.tx-icon.purchase { background: #e3f2fd; color: #1976d2; }
.tx-icon.refund { background: #fff3e0; color: #f57c00; }
.tx-icon.withdrawal { background: #ffebee; color: #c62828; }

.tx-info {
  flex: 1;
  min-width: 0;
}

.tx-info strong {
  display: block;
  font-size: 14px;
  margin-bottom: 3px;
}

.tx-info small {
  color: #878787;
  font-size: 12px;
  display: block;
}

.tx-info .date {
  font-size: 11px;
  margin-top: 3px;
}

.tx-amount {
  font-size: 16px;
  font-weight: 700;
  white-space: nowrap;
}

.tx-amount.positive { color: #388e3c; }
.tx-amount.negative { color: #c62828; }

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: white;
  padding: 30px;
  border-radius: 16px;
  width: 100%;
  max-width: 450px;
}

.modal h3 {
  font-size: 20px;
  margin-bottom: 8px;
}

.modal > p {
  color: #878787;
  margin-bottom: 20px;
  font-size: 14px;
}

.quick-amounts {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  margin-bottom: 15px;
}

.quick-amounts button {
  padding: 12px 8px;
  border: 2px solid #e0e0e0;
  background: white;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 13px;
}

.quick-amounts button.active {
  border-color: #2874f0;
  background: #f5f8ff;
  color: #2874f0;
}

.amount-input {
  width: 100%;
  padding: 14px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 15px;
  margin-bottom: 12px;
}

.amount-input:focus {
  outline: none;
  border-color: #2874f0;
}

.fee-note {
  font-size: 12px;
  color: #f57c00;
  margin-bottom: 15px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.modal-actions .btn {
  flex: 1;
  padding: 12px;
  font-weight: 700;
}

.empty {
  text-align: center;
  padding: 40px;
  color: #878787;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #878787;
}

@media (max-width: 600px) {
  .balance-card {
    padding: 25px 20px;
    flex-direction: column;
    align-items: stretch;
  }
  .balance-info h2 { font-size: 32px; }
  .balance-actions { width: 100%; }
  .stats-row { gap: 20px; }
  .quick-amounts { grid-template-columns: repeat(2, 1fr); }
}
</style>
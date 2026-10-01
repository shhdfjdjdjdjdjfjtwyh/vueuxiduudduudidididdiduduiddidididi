<template>
  <div class="checkout-page">
    <div class="container">
      <div class="checkout-layout">
        <!-- Left: Address + Payment -->
        <div class="checkout-main">

          <!-- Step 1: Address -->
          <div class="checkout-section">
            <div class="section-header">
              <span class="step-number">1</span>
              <h2>Delivery Address</h2>
            </div>

            <div v-if="!addresses.length" class="no-address">
              <p>No address added yet</p>
              <button @click="showAddressForm = true" class="btn btn-primary">
                + Add New Address
              </button>
            </div>

            <div v-else class="addresses-list">
              <label 
                v-for="addr in addresses" 
                :key="addr.id"
                class="address-card"
                :class="{ selected: selectedAddress === addr.id }">
                <input 
                  type="radio" 
                  :value="addr.id" 
                  v-model="selectedAddress"
                >
                <div>
                  <strong>{{ addr.full_name }}</strong>
                  <span v-if="addr.is_default" class="default-badge">Default</span>
                  <p>{{ addr.address }}, {{ addr.city }}, {{ addr.state }} - {{ addr.pincode }}</p>
                  <p>📞 {{ addr.phone }}</p>
                </div>
              </label>

              <button @click="showAddressForm = true" class="btn btn-outline">
                + Add New Address
              </button>
            </div>

            <!-- Address Form Modal -->
            <div v-if="showAddressForm" class="address-form-modal">
              <div class="modal-content">
                <h3>Add New Address</h3>
                <form @submit.prevent="saveAddress">
                  <input v-model="newAddress.full_name" placeholder="Full Name" required>
                  <input v-model="newAddress.phone" placeholder="Phone (10 digits)" pattern="[6-9]\d{9}" required>
                  <textarea v-model="newAddress.address" placeholder="Full Address" required></textarea>
                  <div class="row">
                    <input v-model="newAddress.city" placeholder="City" required>
                    <input v-model="newAddress.state" placeholder="State" required>
                  </div>
                  <input v-model="newAddress.pincode" placeholder="Pincode (6 digits)" pattern="\d{6}" required>
                  <label class="checkbox-label">
                    <input type="checkbox" v-model="newAddress.is_default">
                    Set as default address
                  </label>
                  <div class="form-actions">
                    <button type="button" @click="showAddressForm = false" class="btn btn-outline">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Address</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Step 2: Payment -->
          <div class="checkout-section">
            <div class="section-header">
              <span class="step-number">2</span>
              <h2>Payment Method</h2>
            </div>

            <div class="payment-methods">
              <label class="payment-card" :class="{ selected: paymentMethod === 'razorpay' }">
                <input type="radio" value="razorpay" v-model="paymentMethod">
                <div class="payment-info">
                  <strong>💳 Credit/Debit Card</strong>
                  <p>Visa, Mastercard, RuPay</p>
                </div>
              </label>

              <label class="payment-card" :class="{ selected: paymentMethod === 'upi' }">
                <input type="radio" value="upi" v-model="paymentMethod">
                <div class="payment-info">
                  <strong>📱 UPI</strong>
                  <p>PhonePe, GPay, Paytm</p>
                </div>
              </label>

              <label class="payment-card" :class="{ selected: paymentMethod === 'wallet' }">
                <input type="radio" value="wallet" v-model="paymentMethod">
                <div class="payment-info">
                  <strong>💰 ShopVault Wallet</strong>
                  <p>Balance: ₹{{ userBalance.toLocaleString('en-IN') }}</p>
                </div>
              </label>

              <label class="payment-card" :class="{ selected: paymentMethod === 'cod' }">
                <input type="radio" value="cod" v-model="paymentMethod">
                <div class="payment-info">
                  <strong>💵 Cash on Delivery</strong>
                  <p>Pay when delivered</p>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- Right: Order Summary -->
        <aside class="order-summary">
          <h3>Order Summary</h3>
          
          <div class="summary-items">
            <div v-for="item in cart.items" :key="item.id" class="summary-item">
              <img :src="item.main_image" :alt="item.name">
              <div>
                <span class="item-name">{{ item.name }}</span>
                <span class="item-qty">Qty: {{ item.quantity }}</span>
              </div>
              <span class="item-price">₹{{ item.line_total.toLocaleString('en-IN') }}</span>
            </div>
          </div>

          <div class="summary-totals">
            <div class="row">
              <span>Subtotal</span>
              <span>₹{{ cart.subtotal.toLocaleString('en-IN') }}</span>
            </div>
            <div class="row">
              <span>Shipping</span>
              <span :class="{ free: cart.shipping === 0 }">
                {{ cart.shipping === 0 ? 'FREE' : '₹' + cart.shipping }}
              </span>
            </div>
            <div class="row">
              <span>Tax</span>
              <span>₹{{ cart.tax.toFixed(2) }}</span>
            </div>
            <div class="row total">
              <span>Total</span>
              <span>₹{{ cart.total.toFixed(2) }}</span>
            </div>
          </div>

          <button 
            @click="placeOrder" 
            class="btn btn-secondary btn-block"
            :disabled="!selectedAddress || processing"
          >
            {{ processing ? 'Processing...' : 'PLACE ORDER' }}
          </button>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const addresses = ref([])
const selectedAddress = ref(null)
const paymentMethod = ref('razorpay')
const showAddressForm = ref(false)
const processing = ref(false)
const userBalance = ref(authStore.user?.balance || 0)

const cart = ref({
  items: [],
  subtotal: 0,
  shipping: 0,
  tax: 0,
  total: 0
})

const newAddress = reactive({
  full_name: '',
  phone: '',
  address: '',
  city: '',
  state: '',
  pincode: '',
  is_default: false
})

const loadData = async () => {
  try {
    const [cartRes, addrRes] = await Promise.all([
      axios.get('/cart/get.php'),
      axios.get('/user/get-addresses.php')
    ])

    if (cartRes.data.success) {
      cart.value = cartRes.data.data
      if (!cart.value.items.length) {
        toast.warning('Cart is empty')
        router.push('/cart')
      }
    }

    if (addrRes.data.success) {
      addresses.value = addrRes.data.addresses
      const defaultAddr = addresses.value.find(a => a.is_default)
      if (defaultAddr) selectedAddress.value = defaultAddr.id
      else if (addresses.value.length) selectedAddress.value = addresses.value[0].id
    }
  } catch (e) {
    console.error(e)
  }
}

const saveAddress = async () => {
  try {
    const { data } = await axios.post('/user/add-address.php', newAddress)
    if (data.success) {
      toast.success('Address added!')
      showAddressForm.value = false
      Object.assign(newAddress, {
        full_name: '', phone: '', address: '', city: '', state: '', pincode: '', is_default: false
      })
      await loadData()
    }
  } catch (e) {
    toast.error('Failed to add address')
  }
}

const placeOrder = async () => {
  if (!selectedAddress.value) {
    toast.warning('Please select a delivery address')
    return
  }

  processing.value = true

  try {
    const { data } = await axios.post('/order/create.php', {
      address_id: selectedAddress.value,
      payment_method: paymentMethod.value
    })

    if (data.success) {
      // If Razorpay/UPI, init payment
      if (paymentMethod.value === 'razorpay') {
        await initRazorpay(data.data)
      } else if (paymentMethod.value === 'wallet') {
        // Direct wallet payment
        await verifyWalletPayment(data.data)
      } else if (paymentMethod.value === 'cod') {
        router.push(`/order-success?order=${data.data.order_number}`)
      } else {
        router.push(`/order-success?order=${data.data.order_number}`)
      }
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Order failed')
  } finally {
    processing.value = false
  }
}

const initRazorpay = async (orderData) => {
  try {
    const { data } = await axios.post('/payment/razorpay/create-order.php', {
      order_id: orderData.order_id,
      amount: orderData.total_amount
    })

    if (!data.success) {
      toast.error('Payment init failed')
      return
    }

    const options = {
      key: data.data.key,
      amount: data.data.amount,
      currency: 'INR',
      name: 'ShopVault',
      description: `Order ${orderData.order_number}`,
      order_id: data.data.razorpay_order_id,
      handler: async (response) => {
        // Verify payment
        const { data: verifyData } = await axios.post('/payment/razorpay/verify.php', {
          order_id: orderData.order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature
        })

        if (verifyData.success) {
          cartStore.clear()
          router.push(`/order-success?order=${orderData.order_number}`)
        } else {
          toast.error('Payment verification failed')
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
    toast.error('Payment failed')
  }
}

const verifyWalletPayment = async (orderData) => {
  try {
    const { data } = await axios.post('/payment/wallet-pay.php', {
      order_id: orderData.order_id
    })
    if (data.success) {
      cartStore.clear()
      router.push(`/order-success?order=${orderData.order_number}`)
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error('Wallet payment failed')
  }
}

onMounted(loadData)
</script>

<style scoped>
.checkout-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 20px;
}

.checkout-main {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.checkout-section {
  background: white;
  border-radius: 8px;
  padding: 24px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.step-number {
  width: 32px;
  height: 32px;
  background: #2874f0;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.section-header h2 {
  font-size: 18px;
}

.addresses-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.address-card {
  display: flex;
  gap: 12px;
  padding: 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.address-card.selected {
  border-color: #2874f0;
  background: #f5f8ff;
}

.address-card strong {
  font-size: 15px;
}

.address-card p {
  color: #4a4a4a;
  font-size: 13px;
  margin: 4px 0;
}

.default-badge {
  display: inline-block;
  background: #388e3c;
  color: white;
  font-size: 10px;
  padding: 2px 8px;
  border-radius: 4px;
  margin-left: 8px;
  text-transform: uppercase;
  font-weight: 700;
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.payment-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.payment-card.selected {
  border-color: #2874f0;
  background: #f5f8ff;
}

.payment-info strong {
  display: block;
  font-size: 15px;
  margin-bottom: 4px;
}

.payment-info p {
  color: #878787;
  font-size: 13px;
}

.order-summary {
  background: white;
  border-radius: 8px;
  padding: 24px;
  height: fit-content;
  position: sticky;
  top: 90px;
}

.order-summary h3 {
  font-size: 16px;
  color: #878787;
  text-transform: uppercase;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.summary-items {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.summary-item {
  display: flex;
  gap: 12px;
  padding: 10px 0;
  align-items: center;
}

.summary-item img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}

.summary-item > div {
  flex: 1;
  min-width: 0;
}

.item-name {
  display: block;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-qty {
  font-size: 12px;
  color: #878787;
}

.item-price {
  font-weight: 600;
  font-size: 14px;
}

.summary-totals {
  margin-bottom: 20px;
}

.row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 14px;
}

.row.total {
  border-top: 1px dashed #e0e0e0;
  margin-top: 12px;
  padding-top: 16px;
  font-size: 18px;
  font-weight: 700;
}

.free {
  color: #388e3c;
  font-weight: 600;
}

.btn-block {
  width: 100%;
  padding: 14px;
  font-size: 15px;
  font-weight: 700;
}

.address-form-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 30px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h3 {
  font-size: 20px;
  margin-bottom: 20px;
}

.modal-content input, .modal-content textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 14px;
  font-family: inherit;
}

.modal-content textarea {
  min-height: 80px;
  resize: vertical;
}

.row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 12px 0;
  font-size: 14px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 10px;
}

.no-address {
  text-align: center;
  padding: 30px;
  color: #878787;
}

@media (max-width: 900px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
  .order-summary {
    position: static;
  }
}
</style>
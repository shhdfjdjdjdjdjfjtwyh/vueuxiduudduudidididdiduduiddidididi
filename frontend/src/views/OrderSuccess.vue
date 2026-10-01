<template>
  <div class="success-page">
    <div class="container">
      <div class="success-card">
        <div class="success-icon">✓</div>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for shopping with ShopVault</p>

        <div class="order-info">
          <div class="info-row">
            <span>Order Number</span>
            <strong>{{ orderNumber }}</strong>
          </div>
        </div>

        <div class="delivery-info">
          <h3>📦 Expected Delivery</h3>
          <p>{{ deliveryDate }}</p>
        </div>

        <div class="actions">
          <router-link to="/my-orders" class="btn btn-primary">
            View My Orders
          </router-link>
          <router-link to="/products" class="btn btn-outline">
            Continue Shopping
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const orderNumber = route.query.order || 'N/A'

const deliveryDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 4)
  return d.toLocaleDateString('en-IN', { 
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' 
  })
})
</script>

<style scoped>
.success-page {
  padding: 60px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.success-card {
  max-width: 600px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  padding: 50px 40px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #388e3c;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  margin: 0 auto 24px;
  animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
  from { transform: scale(0); }
  to { transform: scale(1); }
}

.success-card h1 {
  font-size: 26px;
  margin-bottom: 10px;
  color: #212121;
}

.success-card > p {
  color: #878787;
  margin-bottom: 30px;
}

.order-info {
  background: #f1f3f6;
  padding: 16px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.info-row strong {
  color: #2874f0;
  font-size: 16px;
}

.delivery-info {
  padding: 20px;
  background: #e8f5e9;
  border-radius: 8px;
  margin-bottom: 30px;
}

.delivery-info h3 {
  font-size: 15px;
  margin-bottom: 6px;
}

.delivery-info p {
  color: #388e3c;
  font-weight: 600;
  font-size: 15px;
}

.actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

@media (max-width: 600px) {
  .success-card {
    padding: 30px 20px;
  }
  .actions {
    flex-direction: column;
  }
  .actions .btn {
    width: 100%;
  }
}
</style>
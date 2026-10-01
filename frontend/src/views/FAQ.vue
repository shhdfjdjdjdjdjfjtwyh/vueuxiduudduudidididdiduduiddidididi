<template>
  <div class="static-page">
    <div class="container">
      <h1>Frequently Asked Questions</h1>
      <p class="lead">Everything you need to know about ShopVault</p>

      <div class="faq-list">
        <div v-for="(item, i) in faqs" :key="i" class="faq-item">
          <div class="faq-question" @click="item.open = !item.open">
            <span>{{ item.q }}</span>
            <span class="toggle">{{ item.open ? '−' : '+' }}</span>
          </div>
          <div v-if="item.open" class="faq-answer">{{ item.a }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const faqs = reactive([
  { q: 'What is ShopVault?', a: 'ShopVault is a multi-vendor marketplace where you can buy products from verified sellers with fast delivery across India.', open: true },
  { q: 'How do I place an order?', a: 'Browse products, add to cart, proceed to checkout, select payment method, and confirm. That\'s it!', open: false },
  { q: 'What payment methods are accepted?', a: 'We accept Credit/Debit Cards, UPI (PhonePe, GPay, Paytm), ShopVault Wallet, and Cash on Delivery.', open: false },
  { q: 'How long does delivery take?', a: 'Most orders are delivered within 2-5 business days. Remote areas may take longer.', open: false },
  { q: 'Can I return a product?', a: 'Yes, we offer 7-day return policy on most items. Visit My Orders to initiate a return.', open: false },
  { q: 'Is my payment secure?', a: 'Absolutely. All payments are encrypted and processed via trusted gateways like Razorpay.', open: false },
  { q: 'How do I track my order?', a: 'Go to My Orders and click on your order to see real-time tracking updates.', open: false },
  { q: 'Do you offer refunds?', a: 'Yes, refunds are processed within 5-7 business days for eligible returns.', open: false },
  { q: 'How can I become an agent?', a: 'Contact our support team to apply for an agent account. You\'ll earn commission on every sale.', open: false },
  { q: 'Is ShopVault available pan-India?', a: 'Yes! We deliver to over 19,000 pin codes across India.', open: false }
])
</script>

<style scoped>
.static-page {
  padding: 60px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  background: white;
  padding: 50px 40px;
  border-radius: 12px;
}

.static-page h1 {
  font-size: 34px;
  margin-bottom: 12px;
}

.lead {
  font-size: 16px;
  color: #4a4a4a;
  margin-bottom: 32px;
}

.faq-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.faq-item {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.2s;
}

.faq-item:hover {
  border-color: #2874f0;
}

.faq-question {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px;
  cursor: pointer;
  font-weight: 600;
  font-size: 15px;
  color: #212121;
}

.toggle {
  font-size: 22px;
  color: #2874f0;
  font-weight: 700;
}

.faq-answer {
  padding: 0 22px 20px;
  color: #4a4a4a;
  font-size: 14px;
  line-height: 1.7;
  border-top: 1px solid #f1f3f6;
  padding-top: 16px;
}

@media (max-width: 700px) {
  .container { padding: 30px 20px; }
}
</style>
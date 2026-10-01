<template>
  <footer class="app-footer">
    <div class="container footer-top">
      <div class="footer-col">
        <h4>ABOUT</h4>
        <router-link to="/about">About Us</router-link>
        <router-link to="/contact">Contact</router-link>
        <router-link to="/faq">FAQ</router-link>
      </div>

      <div class="footer-col">
        <h4>HELP</h4>
        <router-link to="/support">Customer Support</router-link>
        <router-link to="/my-orders">Track Order</router-link>
        <router-link to="/faq">Returns</router-link>
      </div>

      <div class="footer-col">
        <h4>POLICY</h4>
        <router-link to="/privacy">Privacy</router-link>
        <router-link to="/terms">Terms</router-link>
      </div>

      <div class="footer-col">
        <h4>CONNECT</h4>
        <a href="#">📧 support@shopvault.in</a>
        <a href="#">📞 +91 XXXXX XXXXX</a>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <p>© 2026 ShopVault. All rights reserved.</p>
        <p class="payment-info">Secure payments via 🔒 Razorpay, UPI, Cards</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.app-footer {
  background: #172337;
  color: white;
  padding: 40px 0 0;
  margin-top: auto;
}

.footer-top {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
  padding: 0 20px 30px;
  max-width: 1300px;
  margin: 0 auto;
}

.footer-col h4 {
  font-size: 12px;
  color: #878787;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 14px;
}

.footer-col a {
  display: block;
  color: white;
  text-decoration: none;
  font-size: 13px;
  padding: 5px 0;
  transition: color 0.15s;
}

.footer-col a:hover {
  color: #fb641b;
}

.footer-bottom {
  border-top: 1px solid #2a3a4f;
  padding: 20px 0;
  text-align: center;
}

.footer-bottom p {
  color: #878787;
  font-size: 12px;
  margin: 4px 0;
}

@media (max-width: 700px) {
  .footer-top {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
}
</style>
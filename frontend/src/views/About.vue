<template>
  <div class="static-page">
    <div class="container">
      <h1>About ShopVault</h1>
      <p class="lead">India's own trusted marketplace connecting buyers, sellers and agents.</p>

      <h2>Our Mission</h2>
      <p>
        ShopVault was built with a simple idea: give every Indian access to quality products
        at the best prices, with the trust of a local shop and the reach of a national platform.
      </p>

      <h2>How It Works</h2>
      <ul>
        <li>Millions of products from verified sellers</li>
        <li>Multi-level agent system for local reach</li>
        <li>Secure payment via UPI, Cards, Wallet</li>
        <li>Fast delivery across India</li>
        <li>Easy returns and 24/7 support</li>
      </ul>

      <h2>Why ShopVault</h2>
      <ul>
        <li>✅ Verified products</li>
        <li>🔒 Secure payments</li>
        <li>🚚 Fast delivery</li>
        <li>💰 Wallet & cashback</li>
        <li>🤝 Local agent support</li>
      </ul>

      <h2>Contact</h2>
      <p>Questions? Visit our <router-link to="/contact">Contact page</router-link>.</p>
    </div>
  </div>
</template>

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
  font-size: 36px;
  margin-bottom: 12px;
  color: #212121;
}

.lead {
  font-size: 17px;
  color: #4a4a4a;
  margin-bottom: 40px;
}

.static-page h2 {
  font-size: 22px;
  margin: 32px 0 14px;
  color: #212121;
}

.static-page p,
.static-page li {
  color: #4a4a4a;
  line-height: 1.8;
  font-size: 15px;
}

.static-page ul {
  padding-left: 24px;
  margin: 12px 0;
}

.static-page li {
  margin: 8px 0;
}

.static-page a {
  color: #2874f0;
  text-decoration: none;
  font-weight: 600;
}
</style>
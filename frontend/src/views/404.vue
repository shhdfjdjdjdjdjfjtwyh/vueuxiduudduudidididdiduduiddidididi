<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-code">404</div>
      <h1>Lost in the aisles? 🛒</h1>
      <p>This page doesn't exist or has been moved.</p>
      <div class="actions">
        <router-link to="/" class="btn btn-primary">Go Home</router-link>
        <router-link to="/products" class="btn btn-outline">Browse Products</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.error-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  background: #f1f3f6;
}

.error-content {
  text-align: center;
  max-width: 500px;
}

.error-code {
  font-size: clamp(100px, 20vw, 180px);
  font-weight: 900;
  line-height: 1;
  background: linear-gradient(135deg, #2874f0, #fb641b);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 20px;
}

.error-content h1 {
  font-size: 26px;
  margin-bottom: 12px;
  color: #212121;
}

.error-content p {
  color: #878787;
  margin-bottom: 30px;
  font-size: 15px;
}

.actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}
</style>
<template>
  <div class="home">
    <!-- Hero Banner -->
    <section class="hero">
      <div class="hero-slider">
        <div class="hero-content">
          <h1>Welcome to ShopVault</h1>
          <p>India's Trusted Marketplace</p>
          <router-link to="/products" class="btn btn-primary btn-lg">
            Shop Now →
          </router-link>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="section">
      <div class="container">
        <div class="section-header">
          <h2>Shop by Category</h2>
          <router-link to="/products" class="view-all">View All →</router-link>
        </div>
        <div class="categories-grid">
          <router-link 
            v-for="cat in categories" 
            :key="cat.id"
            :to="`/products?category=${cat.id}`"
            class="category-card">
            <div class="cat-img">
              <img :src="cat.image || defaultCatImg" :alt="cat.name">
            </div>
            <h3>{{ cat.name }}</h3>
            <p>{{ cat.product_count }} products</p>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="section bg-light">
      <div class="container">
        <div class="section-header">
          <h2>Featured Products</h2>
          <router-link to="/products?featured=1" class="view-all">View All →</router-link>
        </div>
        <div class="products-grid">
          <ProductCard 
            v-for="p in featuredProducts" 
            :key="p.id" 
            :product="p" 
          />
        </div>
      </div>
    </section>

    <!-- New Arrivals -->
    <section class="section">
      <div class="container">
        <div class="section-header">
          <h2>New Arrivals</h2>
          <router-link to="/products?sort=newest" class="view-all">View All →</router-link>
        </div>
        <div class="products-grid">
          <ProductCard 
            v-for="p in newProducts" 
            :key="p.id" 
            :product="p" 
          />
        </div>
      </div>
    </section>

    <!-- Trust Badges -->
    <section class="trust-section">
      <div class="container">
        <div class="trust-grid">
          <div class="trust-item">
            <div class="trust-icon">✅</div>
            <h4>Verified Products</h4>
            <p>100% authentic</p>
          </div>
          <div class="trust-item">
            <div class="trust-icon">🚚</div>
            <h4>Fast Delivery</h4>
            <p>2-5 days</p>
          </div>
          <div class="trust-item">
            <div class="trust-icon">💰</div>
            <h4>Secure Payment</h4>
            <p>UPI, Cards, Wallet</p>
          </div>
          <div class="trust-item">
            <div class="trust-icon">🔄</div>
            <h4>Easy Returns</h4>
            <p>7 days return</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
import ProductCard from '@/components/product/ProductCard.vue'

const categories = ref([])
const featuredProducts = ref([])
const newProducts = ref([])
const defaultCatImg = 'https://via.placeholder.com/200'

onMounted(async () => {
  try {
    const [catRes, featRes, newRes] = await Promise.all([
      axios.get('/product/get-categories.php'),
      axios.get('/product/get-all.php?featured=1&limit=8'),
      axios.get('/product/get-all.php?sort=newest&limit=8')
    ])

    if (catRes.data.success) categories.value = catRes.data.data.categories
    if (featRes.data.success) featuredProducts.value = featRes.data.data.items
    if (newRes.data.success) newProducts.value = newRes.data.data.items
  } catch (e) {
    console.error('Home load failed', e)
  }
})
</script>

<style scoped>
.hero {
  background: linear-gradient(135deg, #2874f0, #1a5bb8);
  color: white;
  padding: 80px 20px;
  text-align: center;
}

.hero-content h1 {
  font-size: 48px;
  margin-bottom: 15px;
}

.hero-content p {
  font-size: 20px;
  margin-bottom: 30px;
  opacity: 0.9;
}

.section {
  padding: 60px 20px;
}

.bg-light {
  background: #f1f3f6;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.section-header h2 {
  font-size: 28px;
  color: #212121;
}

.view-all {
  color: #2874f0;
  font-weight: 600;
  text-decoration: none;
}

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 20px;
}

.category-card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.cat-img {
  width: 100px;
  height: 100px;
  margin: 0 auto 15px;
  border-radius: 50%;
  overflow: hidden;
  background: #f1f3f6;
}

.cat-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.category-card h3 {
  font-size: 16px;
  margin-bottom: 4px;
}

.category-card p {
  color: #878787;
  font-size: 13px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
}

.trust-section {
  background: #2874f0;
  color: white;
  padding: 40px 20px;
}

.trust-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 30px;
  text-align: center;
}

.trust-icon {
  font-size: 40px;
  margin-bottom: 10px;
}

.trust-item h4 {
  font-size: 16px;
  margin-bottom: 4px;
}

.trust-item p {
  opacity: 0.9;
  font-size: 13px;
}

@media (max-width: 768px) {
  .hero-content h1 { font-size: 32px; }
  .hero-content p { font-size: 16px; }
  .section-header h2 { font-size: 22px; }
}
</style>
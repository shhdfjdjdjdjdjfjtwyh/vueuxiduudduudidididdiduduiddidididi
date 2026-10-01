<template>
  <div v-if="loading" class="loading-page">
    <div class="spinner"></div>
  </div>

  <div v-else-if="product" class="product-detail">
    <div class="container">
      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/">Home</router-link>
        <span>›</span>
        <router-link to="/products">Products</router-link>
        <span>›</span>
        <router-link :to="`/products?category=${product.category_id}`">
          {{ product.category_name }}
        </router-link>
        <span>›</span>
        <span>{{ product.name }}</span>
      </nav>

      <div class="product-layout">
        <!-- Left: Images -->
        <div class="product-images">
          <div class="main-image">
            <img :src="selectedImage" :alt="product.name">
          </div>
          <div v-if="allImages.length > 1" class="thumbnails">
            <img 
              v-for="(img, i) in allImages" 
              :key="i"
              :src="img"
              :class="{ active: selectedImage === img }"
              @click="selectedImage = img"
            >
          </div>

          <!-- Actions -->
          <div class="image-actions">
            <button @click="addToCart" class="btn btn-primary btn-block" :disabled="!product.in_stock">
              🛒 ADD TO CART
            </button>
            <button @click="buyNow" class="btn btn-secondary btn-block" :disabled="!product.in_stock">
              ⚡ BUY NOW
            </button>
          </div>
        </div>

        <!-- Right: Info -->
        <div class="product-info">
          <div class="brand">{{ product.brand || 'ShopVault' }}</div>
          <h1>{{ product.name }}</h1>
          
          <div class="rating-row">
            <span class="rating-badge">
              {{ product.rating.toFixed(1) }} ★
            </span>
            <span class="rating-count">{{ product.review_count }} ratings</span>
            <span class="sold">• {{ product.sold }} sold</span>
          </div>

          <div class="price-section">
            <span class="price">₹{{ product.price.toLocaleString('en-IN') }}</span>
            <span v-if="product.mrp > product.price" class="mrp">
              ₹{{ product.mrp.toLocaleString('en-IN') }}
            </span>
            <span v-if="product.discount_percent > 0" class="discount">
              {{ Math.round(product.discount_percent) }}% off
            </span>
          </div>

          <div class="stock-info" :class="{ out: !product.in_stock }">
            {{ product.in_stock ? '✓ In Stock' : '✗ Out of Stock' }}
          </div>

          <div class="description">
            <h3>Description</h3>
            <p>{{ product.short_description || product.description }}</p>
          </div>

          <!-- Quantity -->
          <div class="quantity-selector">
            <label>Quantity:</label>
            <div class="qty-controls">
              <button @click="qty = Math.max(1, qty - 1)">−</button>
              <input v-model.number="qty" type="number" min="1" :max="product.quantity">
              <button @click="qty = Math.min(product.quantity, qty + 1)">+</button>
            </div>
          </div>

          <!-- Features -->
          <div class="features-list">
            <div class="feature">✅ 7 Days Return Policy</div>
            <div class="feature">🚚 Free Delivery above ₹499</div>
            <div class="feature">🔒 Secure Payment</div>
          </div>
        </div>
      </div>

      <!-- Reviews Section -->
      <section class="reviews-section">
        <h2>Customer Reviews</h2>
        <div v-if="reviews.length" class="reviews-list">
          <div v-for="r in reviews" :key="r.id" class="review-card">
            <div class="review-header">
              <div class="reviewer-info">
                <img :src="r.avatar || defaultAvatar" :alt="r.username" class="reviewer-avatar">
                <div>
                  <strong>{{ r.full_name || r.username }}</strong>
                  <span v-if="r.is_verified_purchase" class="verified-badge">✓ Verified Purchase</span>
                </div>
              </div>
              <div class="review-rating">
                {{ '★'.repeat(r.rating) }}{{ '☆'.repeat(5 - r.rating) }}
              </div>
            </div>
            <h4 v-if="r.title">{{ r.title }}</h4>
            <p>{{ r.review }}</p>
            <small>{{ formatDate(r.created_at) }}</small>
          </div>
        </div>
        <p v-else class="no-reviews">No reviews yet. Be the first to review!</p>
      </section>

      <!-- Related Products -->
      <section v-if="related.length" class="related-section">
        <h2>You May Also Like</h2>
        <div class="products-grid">
          <ProductCard v-for="p in related" :key="p.id" :product="p" />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/api/axios'
import ProductCard from '@/components/product/ProductCard.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

const product = ref(null)
const related = ref([])
const reviews = ref([])
const loading = ref(true)
const qty = ref(1)
const selectedImage = ref('')
const defaultAvatar = 'https://via.placeholder.com/40'

const allImages = computed(() => {
  if (!product.value) return []
  const imgs = []
  if (product.value.main_image) imgs.push(product.value.main_image)
  if (product.value.gallery_images) imgs.push(...product.value.gallery_images)
  return imgs
})

const loadProduct = async () => {
  loading.value = true
  try {
    const idOrSlug = route.params.id
    const { data } = await axios.get(`/product/get-one.php?id=${idOrSlug}`)
    
    if (data.success) {
      product.value = data.data.product
      related.value = data.data.related
      selectedImage.value = product.value.main_image
      loadReviews()
    } else {
      router.push('/404')
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const loadReviews = async () => {
  try {
    const { data } = await axios.get(`/product/get-reviews.php?product_id=${product.value.id}`)
    if (data.success) reviews.value = data.data.reviews
  } catch (e) {}
}

const addToCart = async () => {
  if (!authStore.isLoggedIn) {
    toast.warning('Please login')
    router.push('/login')
    return
  }

  try {
    const { data } = await axios.post('/cart/add.php', {
      product_id: product.value.id,
      quantity: qty.value
    })

    if (data.success) {
      cartStore.addItem(product.value, qty.value)
      toast.success('Added to cart!')
    }
  } catch (e) {
    toast.error('Failed')
  }
}

const buyNow = async () => {
  await addToCart()
  router.push('/cart')
}

const formatDate = (d) => new Date(d).toLocaleDateString('en-IN', { 
  year: 'numeric', month: 'short', day: 'numeric' 
})

onMounted(loadProduct)
</script>

<style scoped>
.loading-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e0e0e0;
  border-top-color: #2874f0;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.product-detail {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.breadcrumb {
  display: flex;
  gap: 8px;
  font-size: 13px;
  color: #878787;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.breadcrumb a {
  color: #2874f0;
  text-decoration: none;
}

.product-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  background: white;
  padding: 30px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.product-images {
  position: sticky;
  top: 90px;
  height: fit-content;
}

.main-image {
  aspect-ratio: 1;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  background: #f8f9fa;
  margin-bottom: 15px;
}

.main-image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.thumbnails {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.thumbnails img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border: 2px solid #e0e0e0;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.thumbnails img.active {
  border-color: #2874f0;
}

.image-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.product-info h1 {
  font-size: 24px;
  margin: 8px 0 12px;
  color: #212121;
}

.brand {
  font-size: 12px;
  color: #878787;
  text-transform: uppercase;
  font-weight: 600;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.rating-badge {
  background: #388e3c;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
}

.rating-count {
  color: #878787;
  font-size: 13px;
}

.sold {
  color: #878787;
  font-size: 13px;
}

.price-section {
  display: flex;
  align-items: baseline;
  gap: 12px;
  margin-bottom: 15px;
  flex-wrap: wrap;
}

.price {
  font-size: 32px;
  font-weight: 700;
  color: #212121;
}

.mrp {
  font-size: 18px;
  color: #878787;
  text-decoration: line-through;
}

.discount {
  font-size: 16px;
  color: #388e3c;
  font-weight: 600;
}

.stock-info {
  display: inline-block;
  padding: 6px 12px;
  background: #e8f5e9;
  color: #388e3c;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 20px;
}

.stock-info.out {
  background: #ffebee;
  color: #ff3f3f;
}

.description {
  padding: 20px 0;
  border-top: 1px solid #e0e0e0;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.description h3 {
  font-size: 15px;
  margin-bottom: 10px;
}

.description p {
  color: #4a4a4a;
  line-height: 1.7;
  font-size: 14px;
}

.quantity-selector {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
}

.qty-controls {
  display: flex;
  align-items: center;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  overflow: hidden;
}

.qty-controls button {
  width: 36px;
  height: 36px;
  background: #f1f3f6;
  border: none;
  font-size: 18px;
  cursor: pointer;
}

.qty-controls input {
  width: 50px;
  height: 36px;
  border: none;
  text-align: center;
  font-size: 14px;
  font-weight: 600;
}

.features-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.feature {
  font-size: 13px;
  color: #4a4a4a;
  padding: 8px 0;
}

.btn-block {
  width: 100%;
  padding: 14px;
  font-size: 15px;
  font-weight: 700;
}

.reviews-section, .related-section {
  background: white;
  padding: 30px;
  border-radius: 8px;
  margin-bottom: 24px;
}

.reviews-section h2, .related-section h2 {
  font-size: 22px;
  margin-bottom: 20px;
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.review-card {
  padding: 20px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.reviewer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.verified-badge {
  display: block;
  font-size: 11px;
  color: #388e3c;
  font-weight: 600;
}

.review-rating {
  color: #ffc200;
  font-size: 16px;
}

.review-card h4 {
  font-size: 15px;
  margin-bottom: 6px;
}

.review-card p {
  color: #4a4a4a;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 8px;
}

.review-card small {
  color: #878787;
  font-size: 12px;
}

.no-reviews {
  color: #878787;
  text-align: center;
  padding: 30px;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}

@media (max-width: 900px) {
  .product-layout {
    grid-template-columns: 1fr;
  }
  
  .product-images {
    position: static;
  }
  
  .image-actions {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 12px 20px;
    border-top: 1px solid #e0e0e0;
    flex-direction: row;
    z-index: 100;
  }
}
</style>
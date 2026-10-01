<template>
  <div class="product-card">
    <!-- Badge -->
    <div v-if="product.discount_percent > 0" class="badge-discount">
      {{ Math.round(product.discount_percent) }}% OFF
    </div>
    <div v-if="product.is_new" class="badge-new">NEW</div>

    <!-- Wishlist -->
    <button class="wishlist-btn" @click.prevent="toggleWishlist">
      {{ isWishlisted ? '❤️' : '🤍' }}
    </button>

    <!-- Image -->
    <router-link :to="`/product/${product.slug || product.id}`" class="product-img">
      <img 
        :src="product.main_image || defaultImg" 
        :alt="product.name"
        loading="lazy"
      >
    </router-link>

    <!-- Content -->
    <div class="product-info">
      <div class="brand">{{ product.brand || 'ShopVault' }}</div>
      
      <router-link :to="`/product/${product.slug || product.id}`" class="product-name">
        {{ product.name }}
      </router-link>

      <div class="rating">
        <span class="stars">{{ stars }}</span>
        <span class="rating-count">({{ product.review_count }})</span>
      </div>

      <div class="price-row">
        <span class="price">₹{{ product.price.toLocaleString('en-IN') }}</span>
        <span v-if="product.mrp > product.price" class="mrp">
          ₹{{ product.mrp.toLocaleString('en-IN') }}
        </span>
      </div>

      <div v-if="!product.in_stock" class="out-of-stock">Out of Stock</div>
      <button 
        v-else
        class="btn-add-cart"
        :disabled="adding"
        @click.prevent="addToCart"
      >
        {{ adding ? 'Adding...' : '🛒 Add to Cart' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import axios from '@/api/axios'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'
import { useRouter } from 'vue-router'

const props = defineProps({
  product: { type: Object, required: true }
})

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

const defaultImg = 'https://via.placeholder.com/300x300?text=Product'
const adding = ref(false)
const isWishlisted = ref(false)

const stars = computed(() => {
  const r = Math.round(props.product.rating || 0)
  return '★'.repeat(r) + '☆'.repeat(5 - r)
})

const addToCart = async () => {
  if (!authStore.isLoggedIn) {
    toast.warning('Please login first')
    router.push('/login')
    return
  }

  adding.value = true
  try {
    const { data } = await axios.post('/cart/add.php', {
      product_id: props.product.id,
      quantity: 1
    })

    if (data.success) {
      cartStore.addItem(props.product, 1)
      toast.success('Added to cart! 🛒')
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error('Failed to add to cart')
  } finally {
    adding.value = false
  }
}

const toggleWishlist = () => {
  isWishlisted.value = !isWishlisted.value
  toast.success(isWishlisted.value ? 'Added to wishlist' : 'Removed from wishlist')
}
</script>

<style scoped>
.product-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
  transition: all 0.3s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}

.product-card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  transform: translateY(-4px);
}

.badge-discount {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #388e3c;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  z-index: 2;
}

.badge-new {
  position: absolute;
  top: 10px;
  left: 10px;
  background: #fb641b;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  z-index: 2;
}

.wishlist-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: white;
  border: none;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  z-index: 2;
  font-size: 16px;
  transition: all 0.2s;
}

.wishlist-btn:hover {
  transform: scale(1.1);
}

.product-img {
  display: block;
  aspect-ratio: 1;
  overflow: hidden;
  background: #f8f9fa;
}

.product-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s;
}

.product-card:hover .product-img img {
  transform: scale(1.08);
}

.product-info {
  padding: 14px;
}

.brand {
  font-size: 11px;
  color: #878787;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.product-name {
  display: block;
  margin: 6px 0;
  font-size: 14px;
  font-weight: 500;
  color: #212121;
  text-decoration: none;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 40px;
}

.product-name:hover {
  color: #2874f0;
}

.rating {
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 8px 0;
}

.stars {
  color: #ffc200;
  font-size: 14px;
}

.rating-count {
  color: #878787;
  font-size: 12px;
}

.price-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 10px 0;
}

.price {
  font-size: 18px;
  font-weight: 700;
  color: #212121;
}

.mrp {
  font-size: 14px;
  color: #878787;
  text-decoration: line-through;
}

.btn-add-cart {
  width: 100%;
  padding: 10px;
  background: #2874f0;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add-cart:hover:not(:disabled) {
  background: #1a5bb8;
}

.btn-add-cart:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.out-of-stock {
  text-align: center;
  padding: 10px;
  color: #ff3f3f;
  font-size: 13px;
  font-weight: 600;
  background: #fff5f5;
  border-radius: 6px;
}
</style>
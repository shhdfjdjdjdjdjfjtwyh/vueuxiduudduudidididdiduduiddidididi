<template>
  <div class="products-page">
    <div class="container">
      <div class="products-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar" :class="{ open: filtersOpen }">
          <div class="filter-header">
            <h3>Filters</h3>
            <button @click="filtersOpen = false" class="close-btn">×</button>
          </div>

          <!-- Category Filter -->
          <div class="filter-group">
            <h4>Category</h4>
            <label v-for="cat in categories" :key="cat.id" class="filter-item">
              <input type="radio" :value="cat.id" v-model="filters.category" @change="applyFilters">
              <span>{{ cat.name }} ({{ cat.product_count }})</span>
            </label>
            <label class="filter-item">
              <input type="radio" :value="0" v-model="filters.category" @change="applyFilters">
              <span>All Categories</span>
            </label>
          </div>

          <!-- Price Range -->
          <div class="filter-group">
            <h4>Price Range</h4>
            <div class="price-inputs">
              <input v-model.number="filters.min_price" type="number" placeholder="Min" @change="applyFilters">
              <span>—</span>
              <input v-model.number="filters.max_price" type="number" placeholder="Max" @change="applyFilters">
            </div>
          </div>

          <!-- Sort -->
          <div class="filter-group">
            <h4>Sort By</h4>
            <select v-model="filters.sort" @change="applyFilters" class="sort-select">
              <option value="newest">Newest First</option>
              <option value="popular">Most Popular</option>
              <option value="rating">Highest Rated</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="discount">Biggest Discount</option>
            </select>
          </div>

          <button @click="clearFilters" class="btn-clear">Clear All Filters</button>
        </aside>

        <!-- Products Grid -->
        <main class="products-main">
          <!-- Search + Mobile Filter Toggle -->
          <div class="products-toolbar">
            <div class="results-info">
              <strong>{{ total }}</strong> products found
              <span v-if="search"> for "{{ search }}"</span>
            </div>
            <button @click="filtersOpen = true" class="btn-filter-mobile">
              🔍 Filters
            </button>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="products-grid">
            <SkeletonLoader v-for="i in 8" :key="i" type="product" />
          </div>

          <!-- Products -->
          <div v-else-if="products.length" class="products-grid">
            <ProductCard 
              v-for="p in products" 
              :key="p.id" 
              :product="p" 
            />
          </div>

          <!-- Empty -->
          <div v-else class="empty-state">
            <img src="@/assets/images/empty-cart.svg" alt="No products">
            <h3>No products found</h3>
            <p>Try changing filters or search query</p>
            <button @click="clearFilters" class="btn btn-primary">Clear Filters</button>
          </div>

          <!-- Pagination -->
          <Pagination 
            v-if="pages > 1"
            :current="page"
            :total="pages"
            @change="changePage"
          />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/api/axios'
import ProductCard from '@/components/product/ProductCard.vue'
import Pagination from '@/components/common/Pagination.vue'
import SkeletonLoader from '@/components/common/SkeletonLoader.vue'

const route = useRoute()
const router = useRouter()

const products = ref([])
const categories = ref([])
const loading = ref(true)
const total = ref(0)
const pages = ref(0)
const page = ref(1)
const filtersOpen = ref(false)
const search = ref(route.query.search || '')

const filters = reactive({
  category: parseInt(route.query.category) || 0,
  min_price: parseFloat(route.query.min_price) || 0,
  max_price: parseFloat(route.query.max_price) || 0,
  sort: route.query.sort || 'newest',
  featured: route.query.featured || 0
})

const loadProducts = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.value,
      limit: 20,
      sort: filters.sort
    })

    if (filters.category) params.append('category', filters.category)
    if (filters.min_price) params.append('min_price', filters.min_price)
    if (filters.max_price) params.append('max_price', filters.max_price)
    if (filters.featured) params.append('featured', filters.featured)
    if (search.value) params.append('search', search.value)

    const { data } = await axios.get(`/product/get-all.php?${params}`)
    if (data.success) {
      products.value = data.data.items
      total.value = data.data.pagination.total
      pages.value = data.data.pagination.pages
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const { data } = await axios.get('/product/get-categories.php')
    if (data.success) categories.value = data.data.categories
  } catch (e) {}
}

const applyFilters = () => {
  page.value = 1
  updateURL()
  loadProducts()
}

const updateURL = () => {
  const query = {}
  if (filters.category) query.category = filters.category
  if (filters.min_price) query.min_price = filters.min_price
  if (filters.max_price) query.max_price = filters.max_price
  if (filters.sort !== 'newest') query.sort = filters.sort
  if (filters.featured) query.featured = filters.featured
  if (search.value) query.search = search.value

  router.replace({ query })
}

const clearFilters = () => {
  filters.category = 0
  filters.min_price = 0
  filters.max_price = 0
  filters.sort = 'newest'
  filters.featured = 0
  search.value = ''
  page.value = 1
  updateURL()
  loadProducts()
}

const changePage = (p) => {
  page.value = p
  loadProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

watch(() => route.query, () => {
  filters.category = parseInt(route.query.category) || 0
  filters.sort = route.query.sort || 'newest'
  search.value = route.query.search || ''
  page.value = 1
  loadProducts()
})

onMounted(() => {
  loadCategories()
  loadProducts()
})
</script>

<style scoped>
.products-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.products-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 24px;
}

.filters-sidebar {
  background: white;
  border-radius: 8px;
  padding: 20px;
  height: fit-content;
  position: sticky;
  top: 90px;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.filter-header h3 {
  font-size: 18px;
}

.close-btn {
  display: none;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.filter-group {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.filter-group h4 {
  font-size: 14px;
  margin-bottom: 12px;
  color: #212121;
}

.filter-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  cursor: pointer;
  font-size: 14px;
  color: #212121;
}

.filter-item:hover {
  color: #2874f0;
}

.price-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-inputs input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  font-size: 13px;
}

.sort-select {
  width: 100%;
  padding: 10px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  font-size: 13px;
  background: white;
  cursor: pointer;
}

.btn-clear {
  width: 100%;
  padding: 10px;
  background: #f1f3f6;
  border: none;
  border-radius: 4px;
  color: #2874f0;
  font-weight: 600;
  cursor: pointer;
}

.btn-clear:hover {
  background: #e8eaf0;
}

.products-main {
  min-width: 0;
}

.products-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 15px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.results-info {
  font-size: 14px;
  color: #878787;
}

.results-info strong {
  color: #212121;
}

.btn-filter-mobile {
  display: none;
  background: #2874f0;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
}

.empty-state img {
  max-width: 200px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 22px;
  margin-bottom: 8px;
}

.empty-state p {
  color: #878787;
  margin-bottom: 20px;
}

@media (max-width: 900px) {
  .products-layout {
    grid-template-columns: 1fr;
  }
  
  .filters-sidebar {
    position: fixed;
    top: 0;
    left: -100%;
    width: 300px;
    height: 100vh;
    z-index: 1000;
    border-radius: 0;
    overflow-y: auto;
    transition: left 0.3s;
  }
  
  .filters-sidebar.open {
    left: 0;
  }
  
  .close-btn {
    display: block;
  }
  
  .btn-filter-mobile {
    display: block;
  }
}
</style>
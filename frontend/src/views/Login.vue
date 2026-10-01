<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-left">
        <div class="auth-logo">
          <img src="@/assets/images/logo-white.png" alt="ShopVault" />
          <h1>Welcome Back</h1>
          <p>Login to continue shopping</p>
        </div>
      </div>

      <div class="auth-right">
        <div class="auth-form-wrap">
          <h2>Login</h2>
          <p class="subtitle">Get access to your orders, wishlist and recommendations</p>

          <GoogleButton @success="handleGoogleSuccess" />

          <div class="divider"><span>OR</span></div>

          <form @submit.prevent="handleLogin">
            <div v-if="error" class="alert alert-error">{{ error }}</div>

            <div class="form-group">
              <label>Username / Email / Phone</label>
              <input 
                v-model="form.identifier" 
                type="text" 
                placeholder="Enter username, email or phone"
                required
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label>Password</label>
              <div class="password-input">
                <input 
                  v-model="form.password" 
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter password"
                  required
                  class="form-input"
                />
                <button type="button" @click="showPassword = !showPassword" class="eye-btn">
                  {{ showPassword ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
            </div>

            <div class="form-footer">
              <label class="checkbox-label">
                <input type="checkbox" v-model="remember" />
                <span>Remember me</span>
              </label>
              <router-link to="/forgot-password" class="forgot-link">Forgot Password?</router-link>
            </div>

            <button 
              type="submit" 
              class="btn btn-primary btn-block" 
              :disabled="loading"
            >
              {{ loading ? 'Logging in...' : 'Login' }}
            </button>
          </form>

          <p class="auth-footer">
            New to ShopVault? <router-link to="/signup">Create Account</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'
import GoogleButton from '@/components/auth/GoogleButton.vue'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  identifier: '',
  password: ''
})

const loading = ref(false)
const error = ref('')
const showPassword = ref(false)
const remember = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await axios.post('/auth/login.php', form)
    
    if (data.success) {
      authStore.setUser(data.data.user)
      toast.success('Welcome back! 👋')
      
      // Redirect to intended page or home
      const redirect = router.currentRoute.value.query.redirect || '/'
      router.push(redirect)
    } else {
      error.value = data.message
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}

const handleGoogleSuccess = async (credential) => {
  try {
    const { data } = await axios.post('/auth/google-login.php', { credential })
    if (data.success) {
      authStore.setUser(data.data.user)
      toast.success('Welcome!')
      router.push('/')
    }
  } catch (e) {
    toast.error('Google login failed')
  }
}
</script>

<style scoped>
/* Same as Signup.vue styles */
@import '@/assets/css/auth.css';
</style>
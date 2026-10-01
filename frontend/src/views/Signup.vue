<template>
  <div class="auth-page">
    <div class="auth-container">
      <!-- Left Side -->
      <div class="auth-left">
        <div class="auth-logo">
          <img src="@/assets/images/logo-white.png" alt="ShopVault" />
          <h1>ShopVault</h1>
          <p>India's Trusted Marketplace</p>
        </div>
        <div class="auth-features">
          <div class="feature">✅ Verified Products</div>
          <div class="feature">🚚 Fast Delivery</div>
          <div class="feature">💰 Secure Wallet</div>
          <div class="feature">🎁 Best Deals</div>
        </div>
      </div>

      <!-- Right Side - Form -->
      <div class="auth-right">
        <div class="auth-form-wrap">
          <h2>Create Account</h2>
          <p class="subtitle">Join 1 million+ happy shoppers</p>

          <!-- Google Signup -->
          <GoogleButton @success="handleGoogleSuccess" />

          <div class="divider"><span>OR</span></div>

          <form @submit.prevent="handleSignup">
            <div v-if="error" class="alert alert-error">{{ error }}</div>

            <div class="form-group">
              <label>Full Name</label>
              <input 
                v-model="form.full_name" 
                type="text" 
                placeholder="John Doe"
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label>Username *</label>
              <input 
                v-model="form.username" 
                type="text" 
                placeholder="john_doe"
                pattern="[a-zA-Z0-9_]{3,30}"
                required
                class="form-input"
              />
              <small v-if="form.username" class="hint">Only letters, numbers, underscore</small>
            </div>

            <div class="form-group">
              <label>Email *</label>
              <input 
                v-model="form.email" 
                type="email" 
                placeholder="john@example.com"
                required
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label>Phone (Optional)</label>
              <input 
                v-model="form.phone" 
                type="tel" 
                placeholder="9876543210"
                pattern="[6-9]\d{9}"
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label>Password *</label>
              <div class="password-input">
                <input 
                  v-model="form.password" 
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="At least 6 characters"
                  minlength="6"
                  required
                  class="form-input"
                />
                <button type="button" @click="showPassword = !showPassword" class="eye-btn">
                  {{ showPassword ? '👁️' : '👁️‍🗨️' }}
                </button>
              </div>
              <PasswordStrength :password="form.password" />
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.terms" required />
                <span>I agree to <a href="/terms">Terms</a> & <a href="/privacy">Privacy Policy</a></span>
              </label>
            </div>

            <button 
              type="submit" 
              class="btn btn-primary btn-block" 
              :disabled="loading"
            >
              {{ loading ? 'Creating...' : 'Create Account' }}
            </button>
          </form>

          <p class="auth-footer">
            Already have an account? <router-link to="/login">Login</router-link>
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
import PasswordStrength from '@/components/auth/PasswordStrength.vue'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  full_name: '',
  username: '',
  email: '',
  phone: '',
  password: '',
  terms: false
})

const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const handleSignup = async () => {
  if (!form.terms) return
  loading.value = true
  error.value = ''

  try {
    const { data } = await axios.post('/auth/signup.php', form)
    
    if (data.success) {
      authStore.setUser(data.data.user)
      toast.success('Account created! Welcome to ShopVault 🎉')
      router.push('/')
    } else {
      error.value = data.message
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Signup failed'
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
    toast.error('Google signup failed')
  }
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f3f6;
  padding: 20px;
}

.auth-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 1000px;
  width: 100%;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.1);
}

.auth-left {
  background: linear-gradient(135deg, #2874f0, #1a5bb8);
  padding: 60px 40px;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.auth-logo img {
  width: 60px;
  margin-bottom: 20px;
}

.auth-logo h1 {
  font-size: 32px;
  margin-bottom: 10px;
}

.auth-logo p {
  opacity: 0.9;
  margin-bottom: 40px;
}

.auth-features {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.feature {
  font-size: 16px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(255,255,255,0.2);
}

.auth-right {
  padding: 60px 40px;
}

.auth-form-wrap h2 {
  font-size: 28px;
  margin-bottom: 8px;
  color: #212121;
}

.subtitle {
  color: #878787;
  margin-bottom: 30px;
}

.divider {
  text-align: center;
  margin: 20px 0;
  position: relative;
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 45%;
  height: 1px;
  background: #e0e0e0;
}

.divider::before { left: 0; }
.divider::after { right: 0; }

.divider span {
  background: white;
  padding: 0 15px;
  color: #878787;
  font-size: 12px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #212121;
  margin-bottom: 6px;
}

.form-input {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #2874f0;
  box-shadow: 0 0 0 3px rgba(40,116,240,0.1);
}

.hint {
  color: #878787;
  font-size: 11px;
  margin-top: 4px;
  display: block;
}

.password-input {
  position: relative;
}

.eye-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 13px;
  color: #212121;
  cursor: pointer;
}

.checkbox-label input {
  margin-top: 3px;
}

.checkbox-label a {
  color: #2874f0;
  text-decoration: none;
}

.btn-block {
  width: 100%;
  padding: 14px;
  margin-top: 10px;
}

.auth-footer {
  text-align: center;
  margin-top: 24px;
  color: #878787;
  font-size: 14px;
}

.auth-footer a {
  color: #2874f0;
  font-weight: 600;
  text-decoration: none;
}

@media (max-width: 768px) {
  .auth-container {
    grid-template-columns: 1fr;
  }
  .auth-left {
    display: none;
  }
  .auth-right {
    padding: 40px 24px;
  }
}
</style>
<template>
  <div class="agent-login">
    <div class="login-card">
      <div class="login-header">
        <div class="logo">🎯</div>
        <h1>Agent Panel</h1>
        <p>ShopVault Partner Portal</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div v-if="error" class="alert-error">{{ error }}</div>

        <div class="form-group">
          <label>Email or Username</label>
          <input v-model="form.identifier" type="text" required placeholder="agent@example.com" />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" required placeholder="••••••••" />
        </div>

        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="hint">Contact admin if you don't have agent access</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({ identifier: '', password: '' })
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await axios.post('/agent/auth/login.php', form)
    if (data.success) {
      authStore.setUser({
        ...data.data.agent,
        role: 'agent',
        is_agent: true
      })
      toast.success('Welcome back, Agent!')
      router.push('/agent/dashboard')
    } else {
      error.value = data.message
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.agent-login {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a3e 0%, #0f0f23 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.login-card {
  background: rgba(30, 30, 60, 0.95);
  padding: 40px;
  border-radius: 16px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
  border: 1px solid rgba(251,100,27,0.3);
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.logo {
  font-size: 56px;
  margin-bottom: 10px;
}

.login-header h1 {
  color: white;
  font-size: 26px;
  margin-bottom: 6px;
}

.login-header p {
  color: #a5a5c5;
  font-size: 14px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  color: #a5a5c5;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}

.form-group input {
  width: 100%;
  padding: 12px 14px;
  background: #0f0f23;
  border: 1px solid #3a3a5e;
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.form-group input:focus {
  outline: none;
  border-color: #fb641b;
  box-shadow: 0 0 0 3px rgba(251,100,27,0.2);
}

.btn-login {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #fb641b, #f59e0b);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(251,100,27,0.4);
}

.btn-login:disabled {
  opacity: 0.6;
}

.alert-error {
  background: rgba(239,68,68,0.15);
  color: #ff6b6b;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 13px;
  border: 1px solid rgba(239,68,68,0.3);
}

.hint {
  text-align: center;
  color: #6b6b8f;
  font-size: 12px;
  margin-top: 20px;
}
</style>
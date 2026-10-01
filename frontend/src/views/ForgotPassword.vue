<template>
  <div class="auth-page">
    <div class="card">
      <h2>Forgot Password</h2>
      <form @submit.prevent="submit">
        <input v-model="email" type="email" placeholder="Your email" required>
        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
      </form>
      <p><router-link to="/login">Back to Login</router-link></p>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'
const email = ref('')
const submit = async () => {
  const { data } = await axios.post('/auth/forgot-password.php', { email: email.value })
  toast.success(data.message || 'Check your email')
}
</script>
<style scoped>
.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f3f6;padding:20px}
.card{background:#fff;padding:40px;border-radius:12px;width:100%;max-width:420px}
.card h2{margin-bottom:20px}
input{width:100%;padding:12px;border:1px solid #e0e0e0;border-radius:8px;margin-bottom:14px}
p{text-align:center;margin-top:16px}p a{color:#2874f0}
</style>

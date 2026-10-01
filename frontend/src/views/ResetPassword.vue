<template>
  <div class="auth-page">
    <div class="card">
      <h2>Reset Password</h2>
      <form @submit.prevent="submit">
        <input v-model="password" type="password" placeholder="New password" minlength="6" required>
        <button type="submit" class="btn btn-primary btn-block">Reset</button>
      </form>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'
const route = useRoute(); const router = useRouter()
const password = ref('')
const submit = async () => {
  const { data } = await axios.post('/auth/reset-password.php', { token: route.query.token, password: password.value })
  if (data.success) { toast.success('Password reset!'); router.push('/login') }
}
</script>
<style scoped>
.auth-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f3f6}
.card{background:#fff;padding:40px;border-radius:12px;width:100%;max-width:420px}
input{width:100%;padding:12px;border:1px solid #e0e0e0;border-radius:8px;margin-bottom:14px}
</style>

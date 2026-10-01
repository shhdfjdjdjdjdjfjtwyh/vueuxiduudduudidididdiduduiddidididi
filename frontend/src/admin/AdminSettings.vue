<template>
  <div class="admin-page">
    <h2>Settings</h2>
    <div class="settings-grid">
      <div v-for="(val, key) in settings" :key="key" class="setting-row">
        <label>{{ key }}</label>
        <input v-model="settings[key]" :placeholder="key">
      </div>
    </div>
    <button @click="save" class="btn-primary">Save Settings</button>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'
const settings = ref({})
onMounted(async () => {
  const { data } = await axios.get('/admin/settings/get.php')
  if (data.success) settings.value = data.data.settings
})
const save = async () => {
  await axios.post('/admin/settings/update.php', { settings: settings.value })
  toast.success('Saved!')
}
</script>
<style scoped>
.admin-page{padding:20px;color:#fff}.settings-grid{display:grid;gap:12px;margin-bottom:20px}
.setting-row{display:grid;grid-template-columns:200px 1fr;gap:12px;align-items:center}
.setting-row label{color:#a5a5c5;font-size:13px;text-transform:capitalize}
.setting-row input{padding:10px;background:#1e1b4b;border:1px solid #3a3a5e;border-radius:8px;color:#fff}
.btn-primary{padding:12px 24px;background:#6366f1;color:#fff;border-radius:8px;border:none;cursor:pointer;font-weight:700}
</style>

<template>
  <div class="google-btn-wrap">
    <div id="g_id_onload"
      :data-client_id="clientId"
      data-context="signin"
      data-ux_mode="popup"
      data-callback="handleGoogleCallback"
      data-auto_prompt="false">
    </div>
    <div class="g_id_signin"
      data-type="standard"
      data-shape="rectangular"
      data-theme="outline"
      data-text="signup_with"
      data-size="large"
      data-logo_alignment="left"
      data-width="100%">
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const emit = defineEmits(['success'])
const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID

onMounted(() => {
  // Load Google script
  if (!document.getElementById('google-script')) {
    const script = document.createElement('script')
    script.id = 'google-script'
    script.src = 'https://accounts.google.com/gsi/client'
    script.async = true
    script.defer = true
    document.head.appendChild(script)
  }

  // Register callback
  window.handleGoogleCallback = (response) => {
    if (response.credential) {
      emit('success', response.credential)
    }
  }
})
</script>

<style scoped>
.google-btn-wrap {
  width: 100%;
  margin-bottom: 20px;
}
</style>
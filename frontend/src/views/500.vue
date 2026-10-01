<template>
  <div class="error-page">
    <div class="error-content">
      <div class="error-code">500</div>
      <h1>Something went wrong 💥</h1>
      <p>Our servers hit a glitch. Please try again in a moment.</p>
      <div class="actions">
        <router-link to="/" class="btn btn-primary">Go Home</router-link>
        <button @click="reload" class="btn btn-outline">Retry</button>
      </div>
    </div>
  </div>
</template>

<script setup>
const reload = () => window.location.reload()
</script>

<style scoped>
@import '@/views/404.vue';
</style>
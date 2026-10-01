<template>
  <div id="app">
    <!-- Header (except admin/agent) -->
    <AppHeader v-if="showLayout" />

    <!-- Main content -->
    <main :class="{ 'with-header': showLayout }">
      <router-view />
    </main>

    <!-- Footer (except admin/agent) -->
    <AppFooter v-if="showLayout" />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/common/AppHeader.vue'
import AppFooter from '@/components/common/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

const showLayout = computed(() => {
  return !route.path.startsWith('/admin') && 
         !route.path.startsWith('/agent')
})

onMounted(() => {
  authStore.checkSession()
})
</script>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}

main.with-header {
  padding-top: 0;
}
</style>
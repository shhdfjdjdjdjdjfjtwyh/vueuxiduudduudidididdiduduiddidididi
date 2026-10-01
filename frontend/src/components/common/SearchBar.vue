<template>
  <div class="search-bar">
    <input v-model="query" @keyup.enter="$emit('search', query)" placeholder="Search...">
    <button @click="$emit('search', query)">🔍</button>
  </div>
</template>
<script setup>
import { ref } from 'vue'
defineEmits(['search'])
const query = ref('')
</script>
<style scoped>
.search-bar{display:flex;background:#fff;border-radius:6px;overflow:hidden}
.search-bar input{flex:1;padding:10px 14px;border:none;outline:none}
.search-bar button{padding:0 18px;background:#fb641b;color:#fff;border:none;cursor:pointer}
</style>

<template>
  <div class="pagination">
    <button :disabled="current === 1" @click="$emit('change', current - 1)">← Prev</button>
    <span>Page {{ current }} of {{ total }}</span>
    <button :disabled="current === total" @click="$emit('change', current + 1)">Next →</button>
  </div>
</template>
<script setup>
defineProps({ current: Number, total: Number })
defineEmits(['change'])
</script>
<style scoped>
.pagination{display:flex;justify-content:center;align-items:center;gap:16px;padding:24px}
.pagination button{padding:8px 16px;background:#fff;border:1px solid #e0e0e0;border-radius:6px;cursor:pointer}
.pagination button:disabled{opacity:.5;cursor:not-allowed}
</style>

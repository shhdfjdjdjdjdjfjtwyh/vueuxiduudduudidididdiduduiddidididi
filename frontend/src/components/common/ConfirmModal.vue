<template>
  <div v-if="show" class="modal-backdrop" @click.self="$emit('cancel')">
    <div class="modal">
      <h3>{{ title }}</h3>
      <p>{{ message }}</p>
      <div class="actions">
        <button @click="$emit('cancel')" class="btn btn-outline">Cancel</button>
        <button @click="$emit('confirm')" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ show: Boolean, title: String, message: String })
defineEmits(['confirm', 'cancel'])
</script>
<style scoped>
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:1000}
.modal{background:#fff;padding:30px;border-radius:12px;max-width:400px;width:90%}
.modal h3{margin-bottom:12px}.modal p{color:#4a4a4a;margin-bottom:20px}
.actions{display:flex;gap:10px;justify-content:flex-end}
</style>

<template><div class="loader-overlay"><div class="spinner"></div></div></template>
<script setup></script>
<style scoped>
.loader-overlay{position:fixed;inset:0;background:rgba(255,255,255,.8);display:flex;align-items:center;justify-content:center;z-index:9999}
.spinner{width:50px;height:50px;border:4px solid #e0e0e0;border-top-color:#2874f0;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
</style>

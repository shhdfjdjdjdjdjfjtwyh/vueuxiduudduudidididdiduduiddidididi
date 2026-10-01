<template><div class="skeleton-card"><div class="skeleton skeleton-image"></div><div class="skeleton skeleton-text"></div><div class="skeleton skeleton-text"></div></div></template>
<script setup></script>
<style scoped>
.skeleton-card{padding:16px;background:#fff;border-radius:12px}
.skeleton{background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:4px}
.skeleton-image{height:180px;margin-bottom:12px}
.skeleton-text{height:14px;margin-bottom:8px}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>

<template>
  <div v-if="password" class="strength-wrap">
    <div class="strength-bar">
      <div class="strength-fill" :class="strengthClass" :style="{ width: strengthPercent }"></div>
    </div>
    <div class="strength-text" :class="strengthClass">
      {{ strengthText }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  password: String
})

const strength = computed(() => {
  const p = props.password || ''
  let score = 0
  if (p.length >= 6) score++
  if (p.length >= 10) score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++
  return score
})

const strengthPercent = computed(() => `${(strength.value / 5) * 100}%`)

const strengthClass = computed(() => {
  if (strength.value <= 2) return 'weak'
  if (strength.value <= 3) return 'medium'
  return 'strong'
})

const strengthText = computed(() => {
  if (strength.value <= 2) return 'Weak password'
  if (strength.value <= 3) return 'Medium password'
  return 'Strong password ✓'
})
</script>

<style scoped>
.strength-wrap {
  margin-top: 6px;
}

.strength-bar {
  height: 4px;
  background: #e0e0e0;
  border-radius: 2px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  transition: all 0.3s;
}

.strength-fill.weak   { background: #ff3f3f; }
.strength-fill.medium { background: #ff9f00; }
.strength-fill.strong { background: #388e3c; }

.strength-text {
  font-size: 11px;
  margin-top: 4px;
}

.strength-text.weak   { color: #ff3f3f; }
.strength-text.medium { color: #ff9f00; }
.strength-text.strong { color: #388e3c; }
</style>
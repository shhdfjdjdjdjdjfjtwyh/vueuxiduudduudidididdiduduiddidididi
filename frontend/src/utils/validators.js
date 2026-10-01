export const isEmail = (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
export const isPhone = (v) => /^[6-9]\d{9}$/.test(v)
export const isPincode = (v) => /^\d{6}$/.test(v)
export const isStrongPassword = (v) => v && v.length >= 6
export const isUsername = (v) => /^[a-zA-Z0-9_]{3,30}$/.test(v)
export const isEmpty = (v) => !v || v.trim() === ''

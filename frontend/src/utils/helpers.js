export const debounce = (fn, delay = 300) => {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}
export const truncate = (str, len = 50) => str?.length > len ? str.slice(0, len) + '...' : str
export const copy = async (text) => {
  try { await navigator.clipboard.writeText(text); return true }
  catch (e) { return false }
}
export const getInitial = (name) => name ? name[0].toUpperCase() : '?'
export const avatarUrl = (avatar, type = 'users') => {
  if (!avatar || avatar === 'default-avatar.png') return 'https://via.placeholder.com/100'
  const base = import.meta.env.VITE_API_URL?.replace('/backend', '') || ''
  return `${base}/uploads/${type}/${avatar}`
}

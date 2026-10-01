export const storage = {
  set(key, value) {
    try { localStorage.setItem(key, JSON.stringify(value)); return true }
    catch (e) { return false }
  },
  get(key, def = null) {
    try {
      const v = localStorage.getItem(key)
      return v ? JSON.parse(v) : def
    } catch (e) { return def }
  },
  remove(key) { localStorage.removeItem(key) },
  clear() { localStorage.clear() }
}
export default storage

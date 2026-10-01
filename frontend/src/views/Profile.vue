<template>
  <div class="profile-page">
    <div class="container">
      <h1>My Profile</h1>

      <div class="profile-layout">
        <!-- Left Sidebar -->
        <aside class="profile-sidebar">
          <div class="user-card">
            <div class="user-avatar">
              <img :src="avatarUrl" :alt="user.username">
            </div>
            <h3>{{ user.full_name || user.username }}</h3>
            <p>@{{ user.username }}</p>
            <p class="email">{{ user.email }}</p>
          </div>

          <nav class="sidebar-nav">
            <router-link to="/profile" class="nav-item active">👤 Profile</router-link>
            <router-link to="/my-orders" class="nav-item">📦 My Orders</router-link>
            <router-link to="/wallet" class="nav-item">💰 Wallet</router-link>
            <router-link to="/addresses" class="nav-item">📍 Addresses</router-link>
            <router-link to="/support" class="nav-item">💬 Support</router-link>
            <a @click="logout" class="nav-item logout">🚪 Logout</a>
          </nav>
        </aside>

        <!-- Main Content -->
        <main class="profile-main">
          <div class="card">
            <div class="card-header">
              <h3>Personal Information</h3>
            </div>

            <form @submit.prevent="updateProfile" class="profile-form">
              <div class="avatar-section">
                <img :src="avatarPreview || avatarUrl" class="avatar-preview" alt="Avatar">
                <div>
                  <label for="avatar" class="btn-upload">📷 Change Photo</label>
                  <input type="file" id="avatar" accept="image/*" @change="onAvatarChange" style="display:none">
                  <p class="hint">JPG, PNG, WebP · Max 5MB</p>
                </div>
              </div>

              <div class="form-group">
                <label>Full Name</label>
                <input v-model="form.full_name" type="text" placeholder="John Doe" />
              </div>

              <div class="form-group">
                <label>Email (cannot be changed)</label>
                <input :value="user.email" type="email" disabled />
              </div>

              <div class="form-group">
                <label>Phone</label>
                <input v-model="form.phone" type="tel" pattern="[6-9]\d{9}" placeholder="9876543210" />
              </div>

              <div class="form-group">
                <label>Bio</label>
                <textarea v-model="form.bio" rows="3" placeholder="Tell us about yourself..." maxlength="500"></textarea>
              </div>

              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </form>
          </div>

          <!-- Stats -->
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-icon">🛒</div>
              <div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ user.total_orders || 0 }}</div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">💰</div>
              <div>
                <div class="stat-label">Wallet Balance</div>
                <div class="stat-value">₹{{ (user.balance || 0).toLocaleString('en-IN') }}</div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">💸</div>
              <div>
                <div class="stat-label">Total Spent</div>
                <div class="stat-value">₹{{ (user.total_spent || 0).toLocaleString('en-IN') }}</div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">📅</div>
              <div>
                <div class="stat-label">Member Since</div>
                <div class="stat-value">{{ formatDate(user.created_at) }}</div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/api/axios'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'

const router = useRouter()
const authStore = useAuthStore()

const user = ref(authStore.user || {})
const avatarFile = ref(null)
const avatarPreview = ref('')
const saving = ref(false)

const form = reactive({
  full_name: user.value.full_name || '',
  phone: user.value.phone || '',
  bio: user.value.bio || ''
})

const avatarUrl = computed(() => {
  if (user.value.avatar) {
    return `http://localhost/shopvault/uploads/users/${user.value.avatar}`
  }
  return 'https://via.placeholder.com/120'
})

const loadProfile = async () => {
  try {
    const { data } = await axios.get('/user/get-profile.php')
    if (data.success) {
      user.value = data.data.user
      authStore.updateUser(data.data.user)
      form.full_name = user.value.full_name || ''
      form.phone = user.value.phone || ''
      form.bio = user.value.bio || ''
    }
  } catch (e) {
    console.error(e)
  }
}

const onAvatarChange = (e) => {
  const file = e.target.files[0]
  if (!file) return

  if (file.size > 5 * 1024 * 1024) {
    toast.error('Image too large (max 5MB)')
    return
  }

  avatarFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => avatarPreview.value = ev.target.result
  reader.readAsDataURL(file)
}

const updateProfile = async () => {
  saving.value = true

  const fd = new FormData()
  fd.append('full_name', form.full_name)
  fd.append('phone', form.phone)
  fd.append('bio', form.bio)
  if (avatarFile.value) fd.append('avatar', avatarFile.value)

  try {
    const { data } = await axios.post('/user/update-profile.php', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (data.success) {
      user.value = data.data.user
      authStore.updateUser(data.data.user)
      avatarPreview.value = ''
      avatarFile.value = null
      toast.success('Profile updated! ✅')
    } else {
      toast.error(data.message)
    }
  } catch (e) {
    toast.error('Failed to update')
  } finally {
    saving.value = false
  }
}

const logout = async () => {
  try { await axios.post('/auth/logout.php') } catch (e) {}
  authStore.clearUser()
  toast.success('Logged out')
  router.push('/')
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-IN', {
  day: 'numeric', month: 'short', year: 'numeric'
}) : '-'

onMounted(loadProfile)
</script>

<style scoped>
.profile-page {
  padding: 30px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.profile-page h1 {
  font-size: 26px;
  margin-bottom: 20px;
}

.profile-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
}

.profile-sidebar {
  background: white;
  border-radius: 12px;
  padding: 24px 16px;
  height: fit-content;
  position: sticky;
  top: 90px;
}

.user-card {
  text-align: center;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.user-avatar {
  width: 90px;
  height: 90px;
  margin: 0 auto 12px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #2874f0;
  background: #f1f3f6;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-card h3 {
  font-size: 16px;
  margin-bottom: 4px;
  color: #212121;
}

.user-card p {
  color: #878787;
  font-size: 13px;
  margin-bottom: 4px;
}

.user-card .email {
  font-size: 12px;
  margin-top: 6px;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  border-radius: 8px;
  color: #4a4a4a;
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.15s;
}

.nav-item:hover {
  background: #f1f3f6;
  color: #2874f0;
}

.nav-item.router-link-active,
.nav-item.active {
  background: #f5f8ff;
  color: #2874f0;
  font-weight: 600;
}

.logout {
  color: #ff3f3f;
  margin-top: 8px;
}

.logout:hover {
  background: #fff5f5;
  color: #ff3f3f;
}

.profile-main {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 28px;
}

.card-header {
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.card-header h3 {
  font-size: 18px;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e0e0e0;
}

.avatar-preview {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #2874f0;
}

.btn-upload {
  display: inline-block;
  padding: 10px 18px;
  background: #f1f3f6;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-upload:hover {
  background: #f5f8ff;
  border-color: #2874f0;
  color: #2874f0;
}

.hint {
  color: #878787;
  font-size: 11px;
  margin-top: 6px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #212121;
  margin-bottom: 6px;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2874f0;
  box-shadow: 0 0 0 3px rgba(40,116,240,0.1);
}

.form-group input:disabled {
  background: #f1f3f6;
  color: #878787;
  cursor: not-allowed;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  transition: all 0.2s;
}

.stat-card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}

.stat-icon {
  font-size: 30px;
}

.stat-label {
  color: #878787;
  font-size: 12px;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 20px;
  font-weight: 800;
  color: #212121;
}

@media (max-width: 900px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }
  .profile-sidebar {
    position: static;
  }
}
</style>
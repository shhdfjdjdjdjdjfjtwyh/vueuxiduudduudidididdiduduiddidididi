<template>
  <div class="static-page">
    <div class="container">
      <h1>Contact Us</h1>
      <p class="lead">We'd love to hear from you.</p>

      <div class="contact-grid">
        <div class="contact-info">
          <h2>📧 Email</h2>
          <p>General: hello@shopvault.in</p>
          <p>Support: support@shopvault.in</p>

          <h2>📞 Phone</h2>
          <p>+91 XXXXX XXXXX</p>
          <p>Mon-Sat, 10 AM - 7 PM</p>

          <h2>🏢 Address</h2>
          <p>ShopVault HQ,<br>Mumbai, Maharashtra<br>India - 400001</p>
        </div>

        <form @submit.prevent="submitForm" class="contact-form">
          <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" type="text" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" required>
          </div>
          <div class="form-group">
            <label>Subject</label>
            <input v-model="form.subject" type="text" required>
          </div>
          <div class="form-group">
            <label>Message</label>
            <textarea v-model="form.message" rows="5" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { toast } from 'vue3-toastify'

const form = reactive({ name: '', email: '', subject: '', message: '' })

const submitForm = () => {
  toast.success('Message sent! We\'ll respond within 24 hours.')
  Object.assign(form, { name: '', email: '', subject: '', message: '' })
}
</script>

<style scoped>
.static-page {
  padding: 60px 0;
  background: #f1f3f6;
  min-height: 100vh;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  background: white;
  padding: 50px 40px;
  border-radius: 12px;
}

.static-page h1 {
  font-size: 36px;
  margin-bottom: 12px;
}

.lead {
  font-size: 17px;
  color: #4a4a4a;
  margin-bottom: 40px;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 40px;
}

.contact-info h2 {
  font-size: 16px;
  margin: 20px 0 8px;
}

.contact-info p {
  color: #4a4a4a;
  font-size: 14px;
  margin: 4px 0;
}

.contact-form .form-group {
  margin-bottom: 16px;
}

.contact-form label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}

.contact-form input,
.contact-form textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
}

.contact-form input:focus,
.contact-form textarea:focus {
  outline: none;
  border-color: #2874f0;
}

@media (max-width: 700px) {
  .contact-grid { grid-template-columns: 1fr; }
  .container { padding: 30px 20px; }
}
</style>
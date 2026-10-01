import axios from './axios'
export const authApi = {
  signup: (data) => axios.post('/auth/signup.php', data),
  login: (data) => axios.post('/auth/login.php', data),
  logout: () => axios.post('/auth/logout.php'),
  checkSession: () => axios.get('/auth/check-session.php'),
  googleLogin: (credential) => axios.post('/auth/google-login.php', { credential }),
  forgotPassword: (email) => axios.post('/auth/forgot-password.php', { email }),
  resetPassword: (data) => axios.post('/auth/reset-password.php', data)
}

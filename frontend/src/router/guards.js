import { useAuthStore } from '@/stores/auth'

export const authGuard = (to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.guest && auth.isLoggedIn) return next('/')
  if (to.meta.auth && !auth.isLoggedIn) return next(`/login?redirect=${to.path}`)
  if (to.meta.admin && !auth.isAdmin) return next('/admin')
  if (to.meta.agent && !auth.isAgent) return next('/agent')

  next()
}

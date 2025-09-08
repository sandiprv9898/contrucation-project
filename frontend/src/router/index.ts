import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/modules/auth'
import HomeView from '../pages/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.isAuthenticated) {
          next('/dashboard')
        } else {
          next()
        }
      }
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../pages/AboutView.vue'),
    },
    {
      path: '/components',
      name: 'components',
      component: () => import('../pages/ComponentTest.vue'),
    },
    // Authentication Routes
    {
      path: '/auth',
      component: () => import('../pages/auth/AuthLayout.vue'),
      children: [
        {
          path: 'login',
          name: 'Login',
          component: () => import('../pages/auth/LoginPage.vue'),
          meta: { requiresGuest: true }
        },
        {
          path: 'register',
          name: 'Register', 
          component: () => import('../pages/auth/RegisterPage.vue'),
          meta: { requiresGuest: true }
        },
        {
          path: 'forgot-password',
          name: 'ForgotPassword',
          component: () => import('../pages/auth/ForgotPasswordPage.vue'),
          meta: { requiresGuest: true }
        }
      ]
    },
    // Protected Routes
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('../pages/DashboardPage.vue'),
      meta: { requiresAuth: true }
    }
  ],
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Initialize auth state if not already done
  if (!authStore.currentUser && authStore.token) {
    try {
      await authStore.initAuth()
    } catch (error) {
      console.warn('Auth initialization failed:', error)
    }
  }
  
  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!authStore.isAuthenticated) {
      next('/auth/login')
      return
    }
  }
  
  // Check if route requires guest (not authenticated)
  if (to.matched.some(record => record.meta.requiresGuest)) {
    if (authStore.isAuthenticated) {
      next('/dashboard')
      return
    }
  }
  
  next()
})

export default router

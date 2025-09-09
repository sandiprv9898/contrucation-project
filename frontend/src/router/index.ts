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
      beforeEnter: (_to, _from, next) => {
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
      meta: { requiresAuth: true, title: 'Dashboard' }
    },
    // Main Navigation Routes
    {
      path: '/projects',
      name: 'Projects',
      component: () => import('../pages/ProjectsPage.vue'),
      meta: { requiresAuth: true, title: 'Projects' }
    },
    {
      path: '/tasks',
      name: 'Tasks',
      component: () => import('../pages/TasksPage.vue'),
      meta: { requiresAuth: true, title: 'Tasks' }
    },
    {
      path: '/calendar',
      name: 'Calendar',
      component: () => import('../pages/CalendarPage.vue'),
      meta: { requiresAuth: true, title: 'Calendar' }
    },
    {
      path: '/documents',
      name: 'Documents',
      component: () => import('../pages/DocumentsPage.vue'),
      meta: { requiresAuth: true, title: 'Documents' }
    },
    // Admin Routes
    {
      path: '/admin/company',
      name: 'CompanySettings',
      component: () => import('../pages/admin/CompanySettingsPage.vue'),
      meta: { requiresAuth: true, requiresPermission: 'canManageCompany', title: 'Company Settings' }
    },
    {
      path: '/admin/settings',
      name: 'SystemSettings',
      component: () => import('../pages/admin/SystemSettingsPage.vue'),
      meta: { requiresAuth: true, requiresPermission: 'canManageCompany', title: 'System Settings' }
    },
    // User Management Routes
    {
      path: '/users',
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'users.index',
          component: () => import('../pages/users/UsersListPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewUsers', title: 'Team Members' }
        },
        {
          path: 'create',
          name: 'users.create',
          component: () => import('../pages/users/UserCreatePage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageUsers', title: 'Add New User' }
        },
        {
          path: ':id',
          name: 'users.view',
          component: () => import('../pages/users/UserProfilePage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewUsers', title: 'User Profile' }
        },
        {
          path: ':id/edit',
          name: 'users.edit',
          component: () => import('../pages/users/UserEditPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canEditUser', title: 'Edit User' }
        },
        {
          path: ':id/role',
          name: 'users.role',
          component: () => import('../pages/users/UserRoleChangePage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageUsers', title: 'Change User Role' }
        }
      ]
    }
  ],
})

// Navigation guards
router.beforeEach(async (to, _from, next) => {
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

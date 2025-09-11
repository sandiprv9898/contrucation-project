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
          next('/app/dashboard')
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
    {
      path: '/localization-demo',
      name: 'localization-demo',
      component: () => import('../pages/LocalizationDemo.vue'),
      meta: { title: 'Construction Localization Demo' }
    },
    {
      path: '/server-localization-demo',
      name: 'server-localization-demo',
      component: () => import('../views/ServerLocalizationDemo.vue'),
      meta: { title: 'Server-Side Localization Demo' }
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
    // Protected Routes with Shared Layout
    {
      path: '/app',
      component: () => import('../layouts/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/app/dashboard'
        },
        {
          path: 'dashboard',
          name: 'Dashboard',
          component: () => import('../pages/DashboardPage.vue'),
          meta: { requiresAuth: true, title: 'Dashboard' }
        },
        // Main Navigation Routes
        {
          path: 'projects',
          name: 'Projects',
          component: () => import('../pages/ProjectsPage.vue'),
          meta: { requiresAuth: true, title: 'Projects' }
        },
        {
          path: 'projects/:id',
          name: 'ProjectDetail',
          component: () => import('../modules/projects/pages/ProjectDetail.vue'),
          meta: { requiresAuth: true, title: 'Project Details' }
        },
        {
          path: 'projects/settings',
          name: 'ProjectSettings',
          component: () => import('../pages/projects/ProjectSettingsPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageProjects', title: 'Project Settings' }
        },
        {
          path: 'tasks',
          name: 'Tasks',
          component: () => import('../pages/TasksPage.vue'),
          meta: { requiresAuth: true, title: 'Tasks' }
        },
        {
          path: 'tasks/:id',
          name: 'TaskDetail',
          component: () => import('../pages/TaskDetailPage.vue'),
          meta: { requiresAuth: true, title: 'Task Details' }
        },
        // Worker-optimized task routes
        {
          path: 'worker/tasks',
          name: 'WorkerTasks',
          component: () => import('../pages/TasksPageWorker.vue'),
          meta: { requiresAuth: true, title: 'My Tasks', workerMode: true }
        },
        {
          path: 'worker/tasks/:id',
          name: 'WorkerTaskDetail',
          component: () => import('../pages/TaskDetailPageWorker.vue'),
          meta: { requiresAuth: true, title: 'Task Details', workerMode: true }
        },
        {
          path: 'tasks/assign',
          name: 'TaskAssign',
          component: () => import('../pages/TaskAssignmentPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canAssignTasks', title: 'Task Assignment' }
        },
        {
          path: 'calendar',
          name: 'Calendar',
          component: () => import('../pages/CalendarPage.vue'),
          meta: { requiresAuth: true, title: 'Calendar' }
        },
        {
          path: 'notifications',
          name: 'Notifications',
          component: () => import('../pages/NotificationsPage.vue'),
          meta: { requiresAuth: true, title: 'Notifications' }
        },
        {
          path: 'documents',
          name: 'Documents',
          component: () => import('../pages/DocumentsPage.vue'),
          meta: { requiresAuth: true, title: 'Documents' }
        },
        // Admin Routes
        {
          path: 'admin/company',
          name: 'CompanySettings',
          component: () => import('../pages/admin/CompanySettingsPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageCompany', title: 'Company Settings' }
        },
        {
          path: 'admin/settings',
          name: 'SystemSettings',
          component: () => import('../modules/settings/pages/SettingsPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageCompany', title: 'System Settings' }
        },
        // User Management Routes
        {
          path: 'users',
          name: 'users.index',
          component: () => import('../pages/users/UsersListPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewUsers', title: 'Team Members' }
        },
        {
          path: 'users/create',
          name: 'users.create',
          component: () => import('../pages/users/UserCreatePage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageUsers', title: 'Add New User' }
        },
        {
          path: 'users/:id',
          name: 'users.view',
          component: () => import('../pages/users/UserProfilePage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewUsers', title: 'User Profile' }
        },
        {
          path: 'users/:id/edit',
          name: 'users.edit',
          component: () => import('../pages/users/UserEditPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canEditUser', title: 'Edit User' }
        },
        {
          path: 'users/:id/role',
          name: 'users.role',
          component: () => import('../pages/users/UserRoleChangePage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canManageUsers', title: 'Change User Role' }
        },
        // Report Routes
        {
          path: 'reports/projects',
          name: 'reports.projects',
          component: () => import('../pages/reports/ProjectReportsPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewReports', title: 'Project Reports' }
        },
        {
          path: 'reports/time',
          name: 'reports.time',
          component: () => import('../pages/reports/TimeReportsPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewReports', title: 'Time Tracking Reports' }
        },
        {
          path: 'reports/costs',
          name: 'reports.costs',
          component: () => import('../pages/reports/CostAnalysisPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewReports', title: 'Cost Analysis' }
        },
        {
          path: 'reports/performance',
          name: 'reports.performance',
          component: () => import('../pages/reports/PerformanceReportsPage.vue'),
          meta: { requiresAuth: true, requiresPermission: 'canViewReports', title: 'Performance Reports' }
        }
      ]
    },
    // Legacy redirects
    {
      path: '/dashboard',
      redirect: '/app/dashboard'
    },
    {
      path: '/users',
      redirect: '/app/users'
    },
    {
      path: '/notifications',
      redirect: '/app/notifications'
    },
    // Reports Routes (temporarily disabled due to import issues)
    // TODO: Re-enable once import issues are resolved
  ],
})

// Navigation guards
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()
  
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
      next('/app/dashboard')
      return
    }
  }
  
  next()
})

export default router

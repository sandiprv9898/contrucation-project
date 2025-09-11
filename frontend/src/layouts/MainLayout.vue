<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar 
      :is-open="sidebarOpen" 
      @close="sidebarOpen = false" 
    />

    <!-- Main content area -->
    <div class="lg:pl-64">
      <!-- Top navigation -->
      <div class="sticky top-0 z-40 bg-white shadow lg:hidden">
        <div class="flex items-center justify-between h-16 px-4">
          <button
            type="button"
            @click="sidebarOpen = true"
            class="text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange-500"
          >
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="text-lg font-semibold text-orange-600">
            Construction Platform
          </div>
          <div class="w-6"></div> <!-- Spacer for centering -->
        </div>
      </div>

      <!-- Page header -->
      <header class="bg-white shadow">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <h1 class="text-xl font-semibold text-gray-900">
                {{ pageTitle }}
              </h1>
            </div>
            
            <div class="flex items-center space-x-4">
              <!-- Server-Side Language Switcher -->
              <ServerLanguageSwitcher 
                :show-stats="false" 
                @language-changed="handleLanguageChange" 
              />
              
              <!-- Notification Bell -->
              <NotificationBell />
              
              <!-- User menu -->
              <UserMenu />
            </div>
          </div>
        </div>
      </header>

      <!-- Main content -->
      <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from '@/components/layout/Sidebar.vue'
import UserMenu from '@/components/layout/UserMenu.vue'
import ServerLanguageSwitcher from '@/components/ui/ServerLanguageSwitcher.vue'
import NotificationBell from '@/components/notifications/NotificationBell.vue'
import { useServerI18n } from '@/composables/useServerI18n'

defineOptions({ name: 'MainLayout' })

// Sidebar state
const sidebarOpen = ref(false)

// Get current route for page title
const route = useRoute()

// Server-side i18n setup
const { initialize, t } = useServerI18n()

// Language change handler
const handleLanguageChange = (newLocale: string) => {
  console.log('Language changed to:', newLocale)
  // Additional logic can be added here:
  // - Update user preferences in backend
  // - Refresh data with new locale
  // - Show success message
}

// Initialize server-side localization - singleton pattern
onMounted(async () => {
  console.log('🚀 MainLayout: Initializing server-side i18n system...')
  await initialize()
  console.log('✅ MainLayout: Server-side i18n system initialized')
})

const pageTitle = computed(() => {
  const meta = route.meta
  if (meta?.title) return meta.title as string
  
  // Fallback to route name formatting
  const name = route.name as string
  if (!name) return 'Dashboard'
  
  return name
    .replace(/\./g, ' ')
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase())
    .trim()
})
</script>
<template>
  <div class="relative">
    <!-- User avatar/trigger -->
    <button
      @click="isOpen = !isOpen"
      class="flex items-center space-x-3 text-sm bg-white rounded-lg p-2 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
    >
      <div class="flex items-center space-x-2">
        <img
          v-if="currentUser?.avatar_url"
          :src="currentUser.avatar_url"
          :alt="currentUser.name"
          class="w-8 h-8 rounded-full"
        >
        <div
          v-else
          class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center"
        >
          <span class="text-orange-600 font-medium text-sm">
            {{ currentUser?.name?.charAt(0).toUpperCase() }}
          </span>
        </div>
        <div class="hidden md:block text-left">
          <div class="text-sm font-medium text-gray-900">
            {{ currentUser?.name }}
          </div>
          <div class="text-xs text-gray-500 capitalize">
            {{ currentUser?.role?.replace('_', ' ') }}
          </div>
        </div>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </button>

    <!-- Dropdown menu -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        v-click-outside="() => isOpen = false"
        class="absolute right-0 z-50 mt-2 w-64 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
      >
        <!-- User info -->
        <div class="px-4 py-3">
          <div class="flex items-center space-x-3">
            <img
              v-if="currentUser?.avatar_url"
              :src="currentUser.avatar_url"
              :alt="currentUser.name"
              class="w-10 h-10 rounded-full"
            >
            <div
              v-else
              class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center"
            >
              <span class="text-orange-600 font-medium">
                {{ currentUser?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ currentUser?.name }}
              </p>
              <p class="text-sm text-gray-500 truncate">
                {{ currentUser?.email }}
              </p>
              <p class="text-xs text-gray-400 capitalize">
                {{ currentUser?.role?.replace('_', ' ') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Menu items -->
        <div class="py-1">
          <router-link
            to="/profile"
            @click="isOpen = false"
            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            <User class="mr-3 w-4 h-4" />
            My Profile
          </router-link>
          <router-link
            to="/settings"
            @click="isOpen = false"
            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            <Settings class="mr-3 w-4 h-4" />
            Settings
          </router-link>
          <router-link
            to="/notifications"
            @click="isOpen = false"
            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            <Bell class="mr-3 w-4 h-4" />
            Notifications
            <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">3</span>
          </router-link>
        </div>

        <!-- Logout -->
        <div class="py-1">
          <button
            @click="handleLogout"
            :disabled="authStore.isLoading"
            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 disabled:opacity-50"
          >
            <LogOut class="mr-3 w-4 h-4" />
            {{ authStore.isLoading ? 'Signing out...' : 'Sign out' }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth'
import { User, Settings, Bell, LogOut } from 'lucide-vue-next'

defineOptions({ name: 'UserMenu' })

const router = useRouter()
const authStore = useAuthStore()
const isOpen = ref(false)

const currentUser = computed(() => authStore.currentUser)

const handleLogout = async () => {
  try {
    isOpen.value = false
    await authStore.logout()
    router.push('/auth/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

// Click outside directive
const vClickOutside = {
  beforeMount(el: HTMLElement, binding: any) {
    el._clickOutside = (event: Event) => {
      if (!(el === event.target || el.contains(event.target as Node))) {
        binding.value()
      }
    }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el: HTMLElement) {
    document.removeEventListener('click', el._clickOutside)
  }
}
</script>
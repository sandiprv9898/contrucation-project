<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth'

const router = useRouter()
const authStore = useAuthStore()

onMounted(() => {
  // Redirect based on auth status
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  } else {
    router.push('/auth/login')
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600 mx-auto mb-4"></div>
      <p class="text-gray-600">Loading Construction Management Platform...</p>
    </div>
  </div>
</template>

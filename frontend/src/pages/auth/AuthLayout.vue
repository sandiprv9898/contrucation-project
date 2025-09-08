<template>
  <div class="min-h-screen flex">
    <!-- Marketing Section - Left Side -->
    <div class="hidden lg:flex lg:w-1/2">
      <MarketingSection />
    </div>

    <!-- Mobile Marketing Header -->
    <div class="lg:hidden w-full bg-gradient-to-r from-primary to-primary-600 text-white p-6">
      <div class="text-center">
        <h1 class="text-2xl font-bold mb-2">Construction Management Platform</h1>
        <p class="text-orange-100">Professional project management for construction teams</p>
      </div>
    </div>

    <!-- Auth Forms Section - Right Side -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-background">
      <div class="max-w-md w-full mx-auto p-6 lg:p-8">
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-foreground mb-2">
            Welcome
          </h2>
          <p class="text-muted-foreground">
            {{ getWelcomeMessage() }}
          </p>
        </div>

        <!-- Dynamic Form Content -->
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import MarketingSection from '@/components/auth/MarketingSection.vue'

const route = useRoute()

const getWelcomeMessage = () => {
  const routeName = route.name as string
  
  switch (routeName) {
    case 'Login':
      return 'Sign in to your account to continue'
    case 'Register':
      return 'Create your account to get started'
    case 'ForgotPassword':
      return 'Reset your password to regain access'
    default:
      return 'Access your construction management platform'
  }
}
</script>

<style scoped>
/* Mobile responsive adjustments */
@media (max-width: 1023px) {
  .min-h-screen {
    flex-direction: column;
  }
}
</style>
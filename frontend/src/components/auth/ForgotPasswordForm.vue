<template>
  <form @submit.prevent="handleSubmit" class="space-y-5">
    <!-- Success Alert -->
    <div v-if="isSuccess" class="bg-accent/10 border border-accent/20 text-accent px-4 py-3 rounded-lg text-sm font-medium">
      Password reset instructions have been sent to your email address.
    </div>

    <!-- Error Alert -->
    <div v-if="authStore.error" class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg text-sm font-medium">
      {{ authStore.error }}
    </div>

    <!-- Instructions -->
    <div class="text-center mb-6">
      <p class="text-gray-600 text-sm leading-relaxed">
        Enter your email address and we'll send you instructions to reset your password.
      </p>
    </div>

    <!-- Email Field -->
    <div class="space-y-2">
      <label for="email" class="block text-sm font-semibold text-secondary-700">
        Email address
      </label>
      <input 
        id="email"
        v-model="form.email"
        type="email" 
        placeholder="Enter your email address"
        :disabled="authStore.isLoading || isSuccess"
        required
        class="h-12 w-full px-4 text-sm rounded-lg transition-all duration-200 bg-gray-50 border border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.email" class="text-xs text-destructive font-medium">{{ errors.email }}</p>
    </div>

    <!-- Submit Button -->
    <button
      type="submit"
      :disabled="!isFormValid || isSuccess"
      class="w-full h-12 px-4 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:ring-offset-2 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary-500 shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 active:scale-[0.98]"
    >
      <span v-if="authStore.isLoading" class="flex items-center justify-center">
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Sending instructions...
      </span>
      <span v-else>Send reset instructions</span>
    </button>

    <!-- Divider -->
    <div class="relative py-4">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-200"></div>
      </div>
      <div class="relative flex justify-center text-xs">
        <span class="bg-white px-4 text-gray-500 font-medium">Remember your password?</span>
      </div>
    </div>

    <!-- Back to Login Link -->
    <div class="text-center">
      <router-link
        to="/auth/login"
        class="inline-flex items-center justify-center w-full h-12 px-4 text-sm font-semibold text-secondary-700 bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to sign in
      </router-link>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useAuthStore } from '@/modules/auth'

// Define component name
defineOptions({ name: 'ForgotPasswordForm' })

const authStore = useAuthStore()

const form = reactive({
  email: '',
})

const errors = ref<Record<string, string>>({})
const isSuccess = ref(false)

const isFormValid = computed(() => {
  return form.email.length > 0 && !Object.keys(errors.value).length
})

const validateForm = () => {
  errors.value = {}

  // Email validation
  if (!form.email) {
    errors.value.email = 'Email address is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Please enter a valid email address'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) return

  try {
    authStore.clearError()
    await authStore.forgotPassword({ email: form.email })
    isSuccess.value = true
  } catch (error) {
    // Error is handled by the store
    console.error('Password reset request failed:', error)
  }
}
</script>
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
      <p class="text-gray-600">Sign in to your account to continue</p>
    </div>

    <!-- Error Alert -->
    <div v-if="authStore.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ authStore.error }}
    </div>

    <!-- Email -->
    <div class="space-y-2">
      <label for="email" class="block text-sm font-medium text-gray-700">
        Email Address <span class="text-red-500">*</span>
      </label>
      <input 
        id="email"
        v-model="form.email"
        type="email" 
        placeholder="Enter your email address"
        :disabled="authStore.isLoading"
        required
        class="h-10 w-full px-3 py-2 text-sm rounded-md transition-colors bg-white border border-gray-300 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
    </div>

    <!-- Password -->
    <div class="space-y-2">
      <label for="password" class="block text-sm font-medium text-gray-700">
        Password <span class="text-red-500">*</span>
      </label>
      <input 
        id="password"
        v-model="form.password"
        type="password" 
        placeholder="Enter your password"
        :disabled="authStore.isLoading"
        required
        class="h-10 w-full px-3 py-2 text-sm rounded-md transition-colors bg-white border border-gray-300 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="flex items-center justify-between">
      <label class="flex items-center">
        <input
          type="checkbox"
          v-model="form.remember"
          :disabled="authStore.isLoading"
          class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
        />
        <span class="ml-2 text-sm text-gray-700">Remember me</span>
      </label>
      <router-link
        to="/auth/forgot-password"
        class="text-sm text-orange-600 hover:text-orange-700 transition-colors"
      >
        Forgot password?
      </router-link>
    </div>

    <!-- Submit Button -->
    <button
      type="submit"
      :disabled="!canSubmit"
      class="w-full h-10 px-4 py-2 text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-500 focus:ring-opacity-50 rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ authStore.isLoading ? 'Signing in...' : 'Sign in' }}
    </button>

    <!-- Register Link -->
    <div class="text-center">
      <p class="text-gray-600">
        Don't have an account?
        <router-link
          to="/auth/register"
          class="text-orange-600 hover:text-orange-700 font-medium transition-colors"
        >
          Sign up
        </router-link>
      </p>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth'

// Define component name
defineOptions({ name: 'LoginForm' })

const router = useRouter()
const authStore = useAuthStore()

// Simple reactive form
const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const errors = ref<Record<string, string>>({})

const canSubmit = computed(() => {
  return form.email.length > 0 && 
         form.password.length > 0 &&
         Object.keys(errors.value).length === 0 &&
         !authStore.isLoading
})

const validateForm = () => {
  errors.value = {}

  // Email validation
  if (!form.email) {
    errors.value.email = 'Email address is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Please enter a valid email address'
  }

  // Password validation
  if (!form.password) {
    errors.value.password = 'Password is required'
  } else if (form.password.length < 6) {
    errors.value.password = 'Password must be at least 6 characters'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) return

  authStore.clearError()

  try {
    await authStore.login({
      email: form.email,
      password: form.password,
      remember: form.remember
    })
    
    // Success - redirect to dashboard
    router.push('/dashboard')
  } catch (err) {
    console.error('Login error:', err)
    // Error is handled by the store
  }
}
</script>
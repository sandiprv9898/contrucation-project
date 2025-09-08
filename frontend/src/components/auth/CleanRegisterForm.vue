<template>
  <form @submit.prevent="handleSubmit" class="space-y-5">
    <!-- Error Alert -->
    <div v-if="authStore.error" class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg text-sm font-medium">
      {{ authStore.error }}
    </div>

    <!-- Full Name -->
    <div class="space-y-2">
      <label for="name" class="block text-sm font-semibold text-secondary-700">
        Full name
      </label>
      <input 
        id="name"
        v-model="form.name"
        type="text" 
        placeholder="John Smith"
        :disabled="authStore.isLoading"
        required
        class="h-12 w-full px-4 text-sm rounded-lg transition-all duration-200 bg-gray-50 border border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.name" class="text-xs text-destructive font-medium">{{ errors.name }}</p>
    </div>

    <!-- Email -->
    <div class="space-y-2">
      <label for="email" class="block text-sm font-semibold text-secondary-700">
        Work email
      </label>
      <input 
        id="email"
        v-model="form.email"
        type="email" 
        placeholder="john@company.com"
        :disabled="authStore.isLoading"
        required
        class="h-12 w-full px-4 text-sm rounded-lg transition-all duration-200 bg-gray-50 border border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.email" class="text-xs text-destructive font-medium">{{ errors.email }}</p>
    </div>

    <!-- Password -->
    <div class="space-y-2">
      <label for="password" class="block text-sm font-semibold text-secondary-700">
        Password
      </label>
      <div class="relative">
        <input 
          id="password"
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          placeholder="Create a strong password"
          :disabled="authStore.isLoading"
          required
          class="h-12 w-full px-4 pr-12 text-sm rounded-lg transition-all duration-200 bg-gray-50 border border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
        />
        <button
          type="button"
          @click="showPassword = !showPassword"
          class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
          </svg>
        </button>
      </div>
      <p v-if="errors.password" class="text-xs text-destructive font-medium">{{ errors.password }}</p>
      <p class="text-xs text-gray-500">
        Must be 8+ characters with uppercase, lowercase, and number
      </p>
    </div>

    <!-- Confirm Password -->
    <div class="space-y-2">
      <label for="password_confirmation" class="block text-sm font-semibold text-secondary-700">
        Confirm password
      </label>
      <input 
        id="password_confirmation"
        v-model="form.password_confirmation"
        type="password" 
        placeholder="Confirm your password"
        :disabled="authStore.isLoading"
        required
        class="h-12 w-full px-4 text-sm rounded-lg transition-all duration-200 bg-gray-50 border border-gray-200 placeholder:text-gray-400 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.password_confirmation" class="text-xs text-destructive font-medium">{{ errors.password_confirmation }}</p>
    </div>

    <!-- Role Selection -->
    <div class="space-y-2">
      <label for="role" class="block text-sm font-semibold text-secondary-700">Your role</label>
      <select
        id="role"
        v-model="form.role"
        :disabled="authStore.isLoading"
        class="h-12 px-4 border border-gray-200 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 disabled:opacity-50 disabled:cursor-not-allowed bg-gray-50 hover:bg-white focus:bg-white text-secondary-700 transition-all duration-200"
      >
        <option value="field_worker">Field Worker</option>
        <option value="supervisor">Supervisor</option>
        <option value="project_manager">Project Manager</option>
        <option value="admin">Administrator</option>
      </select>
    </div>

    <!-- Terms Agreement -->
    <div class="space-y-3 pt-2">
      <label class="flex items-start gap-3.5">
        <input
          type="checkbox"
          v-model="form.agreeToTerms"
          :disabled="authStore.isLoading"
          required
          class="mt-0.5 w-4 h-4 text-primary-500 focus:ring-primary-500 border-gray-300 rounded transition-colors"
        />
        <span class="text-sm text-secondary-600 leading-relaxed">
          I agree to ConstructFlow's
          <a href="#" class="text-primary-600 hover:text-primary-700 underline font-medium">Terms of Service</a>
          and
          <a href="#" class="text-primary-600 hover:text-primary-700 underline font-medium">Privacy Policy</a>
        </span>
      </label>
      <p v-if="errors.terms" class="text-xs text-destructive font-medium">{{ errors.terms }}</p>
    </div>

    <!-- Submit Button -->
    <button
      type="submit"
      :disabled="!canSubmit"
      class="w-full h-12 px-4 text-sm font-semibold text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:ring-offset-2 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary-500 shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 active:scale-[0.98]"
    >
      <span v-if="authStore.isLoading" class="flex items-center justify-center">
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Creating account...
      </span>
      <span v-else>Create account</span>
    </button>

    <!-- Divider -->
    <div class="relative py-4">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-200"></div>
      </div>
      <div class="relative flex justify-center text-xs">
        <span class="bg-white px-4 text-gray-500 font-medium">Already have an account?</span>
      </div>
    </div>

    <!-- Login Link -->
    <div class="text-center">
      <router-link
        to="/auth/login"
        class="inline-flex items-center justify-center w-full h-12 px-4 text-sm font-semibold text-secondary-700 bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
      >
        Sign in instead
      </router-link>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/auth'
import type { UserRole } from '@/modules/auth/types/auth.types'

// Define component name
defineOptions({ name: 'RegisterForm' })

const router = useRouter()
const authStore = useAuthStore()

// Simple reactive form
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'field_worker' as UserRole,
  agreeToTerms: false,
})

const errors = ref<Record<string, string>>({})
const showPassword = ref(false)

const canSubmit = computed(() => {
  return form.name.length > 0 && 
         form.email.length > 0 && 
         form.password.length > 0 &&
         form.password_confirmation.length > 0 &&
         form.agreeToTerms &&
         Object.keys(errors.value).length === 0 &&
         !authStore.isLoading
})

const validateForm = () => {
  errors.value = {}

  // Name validation
  if (!form.name) {
    errors.value.name = 'Full name is required'
  } else if (form.name.length < 2) {
    errors.value.name = 'Name must be at least 2 characters'
  }

  // Email validation
  if (!form.email) {
    errors.value.email = 'Email address is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.value.email = 'Please enter a valid email address'
  }

  // Password validation
  if (!form.password) {
    errors.value.password = 'Password is required'
  } else if (form.password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters'
  } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(form.password)) {
    errors.value.password = 'Password must contain uppercase, lowercase, and number'
  }

  // Password confirmation validation
  if (!form.password_confirmation) {
    errors.value.password_confirmation = 'Please confirm your password'
  } else if (form.password !== form.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
  }

  // Terms validation
  if (!form.agreeToTerms) {
    errors.value.terms = 'You must agree to the terms and privacy policy'
  }

  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) return

  authStore.clearError()

  try {
    await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      role: form.role
    })
    
    // Success - redirect to dashboard
    router.push('/dashboard')
  } catch (err) {
    console.error('Registration error:', err)
    // Error is handled by the store
  }
}
</script>
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h2>
      <p class="text-gray-600">Join our construction management platform</p>
    </div>

    <!-- Error Alert -->
    <div v-if="authStore.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ authStore.error }}
    </div>

    <!-- Full Name -->
    <div class="space-y-2">
      <label for="name" class="block text-sm font-medium text-gray-700">
        Full Name <span class="text-red-500">*</span>
      </label>
      <input 
        id="name"
        v-model="form.name"
        type="text" 
        placeholder="Enter your full name"
        :disabled="authStore.isLoading"
        required
        class="h-10 w-full px-3 py-2 text-sm rounded-md transition-colors bg-white border border-gray-300 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
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
      <p class="text-xs text-gray-500">
        Password must be at least 8 characters with uppercase, lowercase, and number
      </p>
    </div>

    <!-- Confirm Password -->
    <div class="space-y-2">
      <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
        Confirm Password <span class="text-red-500">*</span>
      </label>
      <input 
        id="password_confirmation"
        v-model="form.password_confirmation"
        type="password" 
        placeholder="Confirm your password"
        :disabled="authStore.isLoading"
        required
        class="h-10 w-full px-3 py-2 text-sm rounded-md transition-colors bg-white border border-gray-300 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ errors.password_confirmation }}</p>
    </div>

    <!-- Role Selection -->
    <div class="space-y-2">
      <label for="role" class="block text-sm font-medium text-gray-700">Role (Optional)</label>
      <select
        id="role"
        v-model="form.role"
        :disabled="authStore.isLoading"
        class="h-10 px-3 py-2 border border-gray-300 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:opacity-50 disabled:cursor-not-allowed bg-white text-gray-900"
      >
        <option value="field_worker">Field Worker</option>
        <option value="supervisor">Supervisor</option>
        <option value="project_manager">Project Manager</option>
        <option value="admin">Administrator</option>
      </select>
    </div>

    <!-- Terms Agreement -->
    <div class="space-y-2">
      <label class="flex items-start gap-3">
        <input
          type="checkbox"
          v-model="form.agreeToTerms"
          :disabled="authStore.isLoading"
          required
          class="mt-0.5 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
        />
        <span class="text-sm text-gray-700">
          I agree to the
          <a href="#" class="text-orange-600 hover:text-orange-700 underline">Terms of Service</a>
          and
          <a href="#" class="text-orange-600 hover:text-orange-700 underline">Privacy Policy</a>
        </span>
      </label>
      <p v-if="errors.terms" class="mt-1 text-xs text-red-600">{{ errors.terms }}</p>
    </div>

    <!-- Submit Button -->
    <button
      type="submit"
      :disabled="!canSubmit"
      class="w-full h-10 px-4 py-2 text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-500 focus:ring-opacity-50 rounded-md font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ authStore.isLoading ? 'Creating account...' : 'Create account' }}
    </button>

    <!-- Login Link -->
    <div class="text-center">
      <p class="text-gray-600">
        Already have an account?
        <router-link
          to="/auth/login"
          class="text-orange-600 hover:text-orange-700 font-medium transition-colors"
        >
          Sign in
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
defineOptions({ name: 'RegisterForm' })

const router = useRouter()
const authStore = useAuthStore()

// Simple reactive form
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'field_worker',
  agreeToTerms: false,
})

const errors = ref<Record<string, string>>({})

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
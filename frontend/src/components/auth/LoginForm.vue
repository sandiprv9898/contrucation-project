<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Error Alert -->
    <VAlert v-if="error" variant="error" class="mb-4">
      {{ error }}
    </VAlert>

    <!-- Email Field -->
    <div class="space-y-2">
      <VLabel for="email" required>Email Address</VLabel>
      <input 
        id="email"
        v-model="form.email" 
        type="email" 
        placeholder="Enter your email address"
        :disabled="isLoading"
        required
        class="h-10 w-full px-3 py-2 text-sm rounded-md transition-colors bg-white border border-gray-300 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
    </div>

    <!-- Password Field -->
    <div class="space-y-2">
      <VLabel for="password" required>Password</VLabel>
      <input 
        id="password"
        v-model="form.password" 
        type="password" 
        placeholder="Enter your password"
        :disabled="isLoading"
        required
        class="h-10 w-full px-3 py-2 text-sm rounded-md transition-colors bg-white border border-gray-300 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
      />
      <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="flex items-center justify-between">
      <VCheckbox
        id="remember"
        v-model="form.remember"
        label="Remember me"
        :disabled="isLoading"
      />
      <router-link
        to="/auth/forgot-password"
        class="text-sm text-orange-600 hover:text-orange-700 transition-colors"
      >
        Forgot password?
      </router-link>
    </div>

    <!-- Login Button -->
    <VButton
      type="submit"
      variant="primary"
      size="md"
      :loading="isLoading"
      :disabled="!canSubmit"
      class="w-full"
    >
      {{ isLoading ? 'Signing in...' : 'Sign in' }}
    </VButton>

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
import { reactive, computed, watch } from 'vue'
import { useAuth } from '@/modules/auth/composables/useAuth'
import { useLoginValidation } from '@/modules/auth/composables/useAuthValidation'
import VButton from '@/components/ui/VButton.vue'
import VCheckbox from '@/components/ui/VCheckbox.vue'
import VLabel from '@/components/ui/VLabel.vue'
import VAlert from '@/components/ui/VAlert.vue'

const { login, clearError, error, isLoading } = useAuth()
const { errors, validate, isFormValid } = useLoginValidation()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const canSubmit = computed(() => {
  return form.email.length > 0 && 
         form.password.length > 0 && 
         isFormValid.value
})

// Watch form changes and validate
watch([() => form.email, () => form.password], () => {
  if (form.email || form.password) {
    validate(form.email, form.password)
  }
})

const handleSubmit = async () => {
  const isValid = validate(form.email, form.password)
  if (!isValid) return

  try {
    clearError()
    await login({
      email: form.email,
      password: form.password,
    })
  } catch (error) {
    console.error('Login failed:', error)
  }
}
</script>
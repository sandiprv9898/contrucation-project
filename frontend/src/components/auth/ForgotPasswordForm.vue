<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Success Alert -->
    <VAlert v-if="isSuccess" variant="success" class="mb-4">
      Password reset instructions have been sent to your email address.
    </VAlert>

    <!-- Error Alert -->
    <VAlert v-if="authStore.error" variant="error" class="mb-4">
      {{ authStore.error }}
    </VAlert>

    <!-- Instructions -->
    <div class="text-center mb-6">
      <p class="text-muted-foreground">
        Enter your email address and we'll send you instructions to reset your password.
      </p>
    </div>

    <!-- Email Field -->
    <div class="space-y-2">
      <VLabel for="email" required>Email Address</VLabel>
      <VInput
        id="email"
        v-model="form.email"
        type="email"
        placeholder="Enter your email address"
        :error="errors.email"
        :disabled="authStore.isLoading || isSuccess"
        required
      />
    </div>

    <!-- Submit Button -->
    <VButton
      type="submit"
      variant="primary"
      size="md"
      :loading="authStore.isLoading"
      :disabled="!isFormValid || isSuccess"
      class="w-full"
    >
      {{ authStore.isLoading ? 'Sending instructions...' : 'Send reset instructions' }}
    </VButton>

    <!-- Back to Login Link -->
    <div class="text-center">
      <router-link
        to="/auth/login"
        class="text-primary hover:text-primary-600 font-medium transition-colors flex items-center justify-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import VInput from '@/components/ui/VInput.vue'
import VButton from '@/components/ui/VButton.vue'
import VLabel from '@/components/ui/VLabel.vue'
import VAlert from '@/components/ui/VAlert.vue'

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
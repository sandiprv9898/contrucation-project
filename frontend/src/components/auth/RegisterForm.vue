<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Error Alert -->
    <VAlert v-if="authStore.error" variant="error" class="mb-4">
      {{ authStore.error }}
    </VAlert>

    <!-- Name Field -->
    <div class="space-y-2">
      <VLabel for="name" required>Full Name</VLabel>
      <VInput
        id="name"
        v-model="form.name"
        type="text"
        placeholder="Enter your full name"
        :error="errors.name"
        :disabled="authStore.isLoading"
        required
      />
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
        :disabled="authStore.isLoading"
        required
      />
    </div>

    <!-- Password Field -->
    <div class="space-y-2">
      <VLabel for="password" required>Password</VLabel>
      <VInput
        id="password"
        v-model="form.password"
        type="password"
        placeholder="Enter your password"
        :error="errors.password"
        :disabled="authStore.isLoading"
        required
      />
      <p class="text-xs text-muted-foreground">
        Password must be at least 8 characters with uppercase, lowercase, and number
      </p>
    </div>

    <!-- Confirm Password Field -->
    <div class="space-y-2">
      <VLabel for="password_confirmation" required>Confirm Password</VLabel>
      <VInput
        id="password_confirmation"
        v-model="form.password_confirmation"
        type="password"
        placeholder="Confirm your password"
        :error="errors.password_confirmation"
        :disabled="authStore.isLoading"
        required
      />
    </div>

    <!-- Role Selection -->
    <div class="space-y-2">
      <VLabel for="role">Role (Optional)</VLabel>
      <select
        id="role"
        v-model="form.role"
        :disabled="authStore.isLoading"
        class="h-8 px-3 py-2 border border-border rounded-md w-full focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary disabled:opacity-50 disabled:cursor-not-allowed bg-background text-foreground"
      >
        <option value="field_worker">Field Worker</option>
        <option value="supervisor">Supervisor</option>
        <option value="project_manager">Project Manager</option>
        <option value="admin">Administrator</option>
      </select>
    </div>

    <!-- Terms Agreement -->
    <div class="space-y-2">
      <VCheckbox
        id="terms"
        v-model="form.agreeToTerms"
        :disabled="authStore.isLoading"
        :error="errors.terms"
        required
      >
        <template #label>
          I agree to the
          <a href="#" class="text-primary hover:text-primary-600 underline">Terms of Service</a>
          and
          <a href="#" class="text-primary hover:text-primary-600 underline">Privacy Policy</a>
        </template>
      </VCheckbox>
    </div>

    <!-- Register Button -->
    <VButton
      type="submit"
      variant="primary"
      size="md"
      :loading="authStore.isLoading"
      :disabled="!isFormValid"
      class="w-full"
    >
      {{ authStore.isLoading ? 'Creating account...' : 'Create account' }}
    </VButton>

    <!-- Login Link -->
    <div class="text-center">
      <p class="text-muted-foreground">
        Already have an account?
        <router-link
          to="/auth/login"
          class="text-primary hover:text-primary-600 font-medium transition-colors"
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
import { useAuthStore } from '@/stores/auth'
import VInput from '@/components/ui/VInput.vue'
import VButton from '@/components/ui/VButton.vue'
import VCheckbox from '@/components/ui/VCheckbox.vue'
import VLabel from '@/components/ui/VLabel.vue'
import VAlert from '@/components/ui/VAlert.vue'

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'field_worker',
  agreeToTerms: false,
})

const errors = ref<Record<string, string>>({})

const isFormValid = computed(() => {
  return form.name.length > 0 && 
         form.email.length > 0 && 
         form.password.length > 0 &&
         form.password_confirmation.length > 0 &&
         form.agreeToTerms &&
         !Object.keys(errors.value).length
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

  try {
    authStore.clearError()
    await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      role: form.role as any,
    })

    // Redirect to dashboard on successful registration
    router.push('/dashboard')
  } catch (error) {
    // Error is handled by the store
    console.error('Registration failed:', error)
  }
}
</script>
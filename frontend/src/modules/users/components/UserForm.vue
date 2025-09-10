<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          {{ isEditMode ? 'Edit User' : 'Create User' }}
        </h1>
        <p class="mt-1 text-sm text-gray-600">
          {{ isEditMode ? 'Update user information and permissions' : 'Add a new user to the system' }}
        </p>
      </div>
    </div>

    <!-- Form -->
    <VCard>
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Basic Information -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <VValidatedField
                v-model="formData.name"
                name="name"
                label="Full Name"
                placeholder="Enter full name"
                :min-length="2"
                required
              />
              
              <VValidatedField
                v-model="formData.email"
                name="email"
                type="email"
                label="Email Address"
                placeholder="Enter email address"
                email
                required
              />
            </div>
          </div>

          <!-- Role and Permissions -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Role & Access</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <VValidatedField
                v-model="formData.role"
                name="role"
                type="select"
                label="Role"
                placeholder="Select user role"
                :options="roleOptions"
                required
              />
              
              <VValidatedField
                v-model="formData.department"
                name="department"
                label="Department"
                placeholder="Enter department"
              />
            </div>

            <!-- Role Description -->
            <div v-if="formData.role" class="mt-4 p-4 bg-blue-50 rounded-lg">
              <div class="flex items-start">
                <Info class="h-5 w-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" />
                <div>
                  <p class="text-sm font-medium text-blue-800">{{ getRoleLabel(formData.role) }}</p>
                  <p class="text-sm text-blue-600 mt-1">{{ getRoleDescription(formData.role) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <VValidatedField
                v-model="formData.phone"
                name="phone"
                type="tel"
                label="Phone Number"
                placeholder="Enter phone number"
                phone
              />
            </div>
          </div>

          <!-- Password Fields (Create Mode Only) -->
          <div v-if="!isEditMode">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Security</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <VValidatedField
                v-model="formData.password"
                name="password"
                type="password"
                label="Password"
                placeholder="Enter password"
                :min-length="8"
                :required="!isEditMode"
              />
              
              <VValidatedField
                v-model="formData.password_confirmation"
                name="password_confirmation"
                type="password"
                label="Confirm Password"
                placeholder="Confirm password"
                :match="formData.password || ''"
                :required="!isEditMode"
              />
            </div>
          </div>

          <!-- Bio (Edit Mode Only) -->
          <div v-if="isEditMode">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
            <VValidatedField
              v-model="formData.bio"
              name="bio"
              type="textarea"
              label="Bio"
              placeholder="Enter user bio..."
              :rows="3"
            />
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
            <VButton
              type="button"
              variant="outline"
              @click="handleCancel"
              :disabled="loading"
            >
              Cancel
            </VButton>
            
            <VButton
              type="submit"
              :loading="loading"
              :disabled="!isFormValid"
            >
              <UserPlus v-if="!isEditMode" class="w-4 h-4 mr-2" />
              <Save v-else class="w-4 h-4 mr-2" />
              {{ isEditMode ? 'Update User' : 'Create User' }}
            </VButton>
          </div>
        </form>
      </div>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { VCard, VButton, VValidatedField } from '@/components/ui'
import { UserPlus, Save, Info } from 'lucide-vue-next'
import type { 
  CreateUserRequest, 
  UpdateUserRequest, 
  UserProfile,
  UserRole 
} from '../types/users.types'
import { ROLE_LABELS, ROLE_DESCRIPTIONS } from '../types/users.types'

// Props
interface Props {
  user?: UserProfile | null
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
  loading: false
})

// Emits
const emit = defineEmits<{
  submit: [data: CreateUserRequest | UpdateUserRequest]
  cancel: []
}>()

// Form state
const isEditMode = computed(() => !!props.user)

const formData = ref<Partial<CreateUserRequest & UpdateUserRequest>>({
  name: '',
  email: '',
  role: 'field_worker',
  department: '',
  phone: '',
  password: '',
  password_confirmation: '',
  bio: ''
})

const errors = ref<Record<string, string>>({})

// Role options
const roleOptions = [
  { value: 'field_worker', label: 'Field Worker' },
  { value: 'supervisor', label: 'Supervisor' },
  { value: 'project_manager', label: 'Project Manager' },
  { value: 'admin', label: 'Administrator' }
]

// Form validation
const isFormValid = computed(() => {
  if (!formData.value.name || formData.value.name.length < 2) return false
  if (!formData.value.email || !isValidEmail(formData.value.email)) return false
  if (!formData.value.role) return false
  
  // Password validation for create mode
  if (!isEditMode.value) {
    if (!formData.value.password || formData.value.password.length < 8) return false
    if (formData.value.password !== formData.value.password_confirmation) return false
  }
  
  return true
})

// Email validation
const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Initialize form data when user prop changes
watch(() => props.user, (newUser) => {
  if (newUser) {
    formData.value = {
      name: newUser.name,
      email: newUser.email,
      role: newUser.role,
      department: newUser.department || '',
      phone: newUser.phone || '',
      bio: newUser.bio || ''
    }
  }
}, { immediate: true })

// Form submission
const handleSubmit = () => {
  if (!isFormValid.value) return

  // Clear any previous errors
  errors.value = {}

  if (isEditMode.value) {
    // Update user - only send changed fields
    const updateData: UpdateUserRequest = {
      name: formData.value.name!,
      email: formData.value.email!,
      role: formData.value.role as UserRole,
      ...(formData.value.department && { department: formData.value.department }),
      ...(formData.value.phone && { phone: formData.value.phone }),
      ...(formData.value.bio && { bio: formData.value.bio })
    }
    emit('submit', updateData)
  } else {
    // Create user - include password
    const createData: CreateUserRequest = {
      name: formData.value.name!,
      email: formData.value.email!,
      role: formData.value.role as UserRole,
      password: formData.value.password!,
      password_confirmation: formData.value.password_confirmation!,
      ...(formData.value.department && { department: formData.value.department }),
      ...(formData.value.phone && { phone: formData.value.phone })
    }
    emit('submit', createData)
  }
}

const handleCancel = () => {
  emit('cancel')
}

// Utility functions
const getRoleLabel = (role: string): string => {
  return ROLE_LABELS[role as UserRole] || role
}

const getRoleDescription = (role: string): string => {
  return ROLE_DESCRIPTIONS[role as UserRole] || ''
}

// Reset form for create mode
onMounted(() => {
  if (!isEditMode.value) {
    formData.value = {
      name: '',
      email: '',
      role: 'field_worker',
      department: '',
      phone: '',
      password: '',
      password_confirmation: '',
      bio: ''
    }
  }
})
</script>
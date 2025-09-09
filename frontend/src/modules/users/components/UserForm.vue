<template>
  <div class="user-form">
    <VCard>
      <template #content>
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Form Header -->
          <div class="flex items-center justify-between pb-4 border-b">
            <h3 class="text-lg font-semibold">
              {{ isEditing ? 'Edit User' : 'Create New User' }}
            </h3>
            <VButton 
              variant="outline" 
              type="button"
              @click="$emit('cancel')"
            >
              Cancel
            </VButton>
          </div>

          <!-- Personal Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
              <VLabel for="name" class="text-sm font-medium text-gray-700">
                Full Name *
              </VLabel>
              <VInput
                id="name"
                v-model="formData.name"
                type="text"
                class="mt-1"
                :class="{ 'border-red-500': hasError('name') }"
                placeholder="Enter full name"
                required
              />
              <div v-if="hasError('name')" class="mt-1 text-sm text-red-600">
                {{ getFirstError('name') }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <VLabel for="email" class="text-sm font-medium text-gray-700">
                Email Address *
              </VLabel>
              <VInput
                id="email"
                v-model="formData.email"
                type="email"
                class="mt-1"
                :class="{ 'border-red-500': hasError('email') }"
                placeholder="Enter email address"
                required
              />
              <div v-if="hasError('email')" class="mt-1 text-sm text-red-600">
                {{ getFirstError('email') }}
              </div>
            </div>
          </div>

          <!-- Role and Company -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Role -->
            <div>
              <VLabel for="role" class="text-sm font-medium text-gray-700">
                Role *
              </VLabel>
              <select
                id="role"
                v-model="formData.role"
                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white text-sm"
                :class="{ 'border-red-500': hasError('role') }"
                required
              >
                <option value="">Select a role</option>
                <option 
                  v-for="role in availableRoles" 
                  :key="role.value" 
                  :value="role.value"
                >
                  {{ role.label }}
                </option>
              </select>
              <div v-if="hasError('role')" class="mt-1 text-sm text-red-600">
                {{ getFirstError('role') }}
              </div>
            </div>

            <!-- Phone -->
            <div>
              <VLabel for="phone" class="text-sm font-medium text-gray-700">
                Phone Number
              </VLabel>
              <VInput
                id="phone"
                v-model="formData.phone"
                type="tel"
                class="mt-1"
                :class="{ 'border-red-500': hasError('phone') }"
                placeholder="Enter phone number"
              />
              <div v-if="hasError('phone')" class="mt-1 text-sm text-red-600">
                {{ getFirstError('phone') }}
              </div>
            </div>
          </div>

          <!-- Department -->
          <div>
            <VLabel for="department" class="text-sm font-medium text-gray-700">
              Department
            </VLabel>
            <VInput
              id="department"
              v-model="formData.department"
              type="text"
              class="mt-1"
              :class="{ 'border-red-500': hasError('department') }"
              placeholder="Enter department"
            />
            <div v-if="hasError('department')" class="mt-1 text-sm text-red-600">
              {{ getFirstError('department') }}
            </div>
          </div>

          <!-- Password fields (only for creation) -->
          <div v-if="!isEditing" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Password -->
            <div>
              <VLabel for="password" class="text-sm font-medium text-gray-700">
                Password *
              </VLabel>
              <VInput
                id="password"
                v-model="formData.password"
                type="password"
                class="mt-1"
                :class="{ 'border-red-500': hasError('password') }"
                placeholder="Enter password"
                required
              />
              <div v-if="hasError('password')" class="mt-1 text-sm text-red-600">
                {{ getFirstError('password') }}
              </div>
              <div class="mt-1 text-xs text-gray-500">
                Password must contain at least 8 characters with uppercase, lowercase, number, and special character.
              </div>
            </div>

            <!-- Password Confirmation -->
            <div>
              <VLabel for="password_confirmation" class="text-sm font-medium text-gray-700">
                Confirm Password *
              </VLabel>
              <VInput
                id="password_confirmation"
                v-model="formData.password_confirmation"
                type="password"
                class="mt-1"
                :class="{ 'border-red-500': hasError('password_confirmation') }"
                placeholder="Confirm password"
                required
              />
              <div v-if="hasError('password_confirmation')" class="mt-1 text-sm text-red-600">
                {{ getFirstError('password_confirmation') }}
              </div>
            </div>
          </div>

          <!-- Bio (for updates) -->
          <div v-if="isEditing">
            <VLabel for="bio" class="text-sm font-medium text-gray-700">
              Bio
            </VLabel>
            <textarea
              id="bio"
              v-model="formData.bio"
              rows="3"
              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
              :class="{ 'border-red-500': hasError('bio') }"
              placeholder="Tell us about yourself..."
            ></textarea>
            <div v-if="hasError('bio')" class="mt-1 text-sm text-red-600">
              {{ getFirstError('bio') }}
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center justify-end pt-4 border-t gap-3">
            <VButton 
              variant="outline" 
              type="button"
              @click="$emit('cancel')"
            >
              Cancel
            </VButton>
            <VButton 
              type="submit"
              :disabled="loading || !isFormValid"
              class="min-w-[120px]"
            >
              {{ loading ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
            </VButton>
          </div>
        </form>
      </template>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useUserValidation } from '../composables/useUserValidation';
import { useUserPermissions } from '../composables/useUserPermissions';
import { VButton, VCard, VInput, VLabel } from '@/components/ui';
import type { CreateUserRequest, UpdateUserRequest, UserProfile } from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

interface Props {
  user?: UserProfile | null;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
  loading: false
});

const emit = defineEmits<{
  submit: [data: CreateUserRequest | UpdateUserRequest];
  cancel: [];
}>();

// ==================== COMPOSABLES ====================
const {
  validateCreateUser,
  validateUpdateUser,
  validateFieldRealTime,
  getFirstFieldError,
  hasFieldError,
  clearErrors
} = useUserValidation();

const { getAssignableRoles } = useUserPermissions();

// ==================== STATE ====================
const isEditing = computed(() => !!props.user);

const formData = ref<CreateUserRequest | UpdateUserRequest>({
  name: '',
  email: '',
  role: '' as UserRole,
  phone: '',
  department: '',
  password: '',
  password_confirmation: '',
  bio: ''
});

// ==================== COMPUTED ====================
const availableRoles = computed(() => {
  const assignableRoles = getAssignableRoles();
  
  const roleLabels = {
    admin: 'Administrator',
    project_manager: 'Project Manager', 
    supervisor: 'Supervisor',
    field_worker: 'Field Worker'
  };
  
  return assignableRoles.map(role => ({
    value: role,
    label: roleLabels[role] || role
  }));
});

const isFormValid = computed(() => {
  // Basic validation - all required fields must be filled
  const hasName = formData.value.name?.trim();
  const hasEmail = formData.value.email?.trim();
  const hasRole = formData.value.role;
  
  if (!isEditing.value) {
    // For creation, password fields are required
    const hasPassword = formData.value.password;
    const hasPasswordConfirm = formData.value.password_confirmation;
    return !!(hasName && hasEmail && hasRole && hasPassword && hasPasswordConfirm);
  }
  
  // For editing, just basic fields
  return !!(hasName && hasEmail && hasRole);
});

// ==================== METHODS ====================
/**
 * Check if field has error
 */
const hasError = (fieldName: string): boolean => {
  return hasFieldError(fieldName);
};

/**
 * Get first error for field
 */
const getFirstError = (fieldName: string): string | null => {
  return getFirstFieldError(fieldName);
};

/**
 * Handle form submission
 */
const handleSubmit = (): void => {
  clearErrors();
  
  // Validate form
  const errors = isEditing.value 
    ? validateUpdateUser(formData.value as UpdateUserRequest)
    : validateCreateUser(formData.value as CreateUserRequest);
  
  if (Object.keys(errors).length === 0) {
    emit('submit', formData.value);
  }
};

/**
 * Initialize form with user data
 */
const initializeForm = (): void => {
  if (props.user) {
    formData.value = {
      name: props.user.name || '',
      email: props.user.email || '',
      role: props.user.role || '' as UserRole,
      phone: props.user.phone || '',
      department: props.user.department || '',
      bio: props.user.bio || ''
    };
  } else {
    // Reset form for new user
    formData.value = {
      name: '',
      email: '',
      role: '' as UserRole,
      phone: '',
      department: '',
      password: '',
      password_confirmation: '',
      bio: ''
    };
  }
  clearErrors();
};

// ==================== WATCHERS ====================
watch(() => props.user, initializeForm, { deep: true });

// Real-time field validation
watch(() => formData.value.name, (newValue) => {
  if (newValue) {
    validateFieldRealTime('name', newValue, isEditing.value ? 'update' : 'create', formData.value);
  }
});

watch(() => formData.value.email, (newValue) => {
  if (newValue) {
    validateFieldRealTime('email', newValue, isEditing.value ? 'update' : 'create', formData.value);
  }
});

watch(() => formData.value.password, (newValue) => {
  if (newValue && !isEditing.value) {
    validateFieldRealTime('password', newValue, 'create', formData.value);
  }
});

watch(() => formData.value.password_confirmation, (newValue) => {
  if (newValue && !isEditing.value) {
    validateFieldRealTime('password_confirmation', newValue, 'create', formData.value);
  }
});

// ==================== LIFECYCLE ====================
onMounted(() => {
  initializeForm();
});
</script>
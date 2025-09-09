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
            <VFormField
              label="Full Name"
              :error-message="getFirstError('name')"
              required
            >
              <template #default="{ fieldId, inputClass }">
                <VInput
                  :id="fieldId"
                  v-model="formData.name"
                  type="text"
                  :class="inputClass"
                  placeholder="Enter full name"
                  required
                />
              </template>
            </VFormField>

            <!-- Email -->
            <VFormField
              label="Email Address"
              :error-message="getFirstError('email')"
              required
            >
              <template #default="{ fieldId, inputClass }">
                <VInput
                  :id="fieldId"
                  v-model="formData.email"
                  type="email"
                  :class="inputClass"
                  placeholder="Enter email address"
                  required
                />
              </template>
            </VFormField>
          </div>

          <!-- Role and Company -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Role -->
            <VFormField
              label="Role"
              :error-message="getFirstError('role')"
              required
            >
              <template #default="{ fieldId, hasError }">
                <VSelect
                  :id="fieldId"
                  v-model="formData.role"
                  :items="availableRoles"
                  placeholder="Select a role"
                  :has-error="hasError"
                  required
                />
              </template>
            </VFormField>

            <!-- Phone -->
            <VFormField
              label="Phone Number"
              :error-message="getFirstError('phone')"
            >
              <template #default="{ fieldId, inputClass }">
                <VInput
                  :id="fieldId"
                  v-model="formData.phone"
                  type="tel"
                  :class="inputClass"
                  placeholder="Enter phone number"
                />
              </template>
            </VFormField>
          </div>

          <!-- Department -->
          <VFormField
            label="Department"
            :error-message="getFirstError('department')"
          >
            <template #default="{ fieldId, inputClass }">
              <VInput
                :id="fieldId"
                v-model="formData.department"
                type="text"
                :class="inputClass"
                placeholder="Enter department"
              />
            </template>
          </VFormField>

          <!-- Password fields (only for creation) -->
          <div v-if="!isEditing" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Password -->
            <VFormField
              label="Password"
              :error-message="getFirstError('password')"
              help-text="Password must contain at least 8 characters with uppercase, lowercase, number, and special character."
              required
            >
              <template #default="{ fieldId, inputClass }">
                <VInput
                  :id="fieldId"
                  v-model="formData.password"
                  type="password"
                  :class="inputClass"
                  placeholder="Enter password"
                  required
                />
              </template>
            </VFormField>

            <!-- Password Confirmation -->
            <VFormField
              label="Confirm Password"
              :error-message="getFirstError('password_confirmation')"
              required
            >
              <template #default="{ fieldId, inputClass }">
                <VInput
                  :id="fieldId"
                  v-model="formData.password_confirmation"
                  type="password"
                  :class="inputClass"
                  placeholder="Confirm password"
                  required
                />
              </template>
            </VFormField>
          </div>

          <!-- Bio (for updates) -->
          <div v-if="isEditing">
            <VFormField
              label="Bio"
              :error-message="getFirstError('bio')"
            >
              <template #default="{ fieldId, hasError }">
                <VTextarea
                  :id="fieldId"
                  v-model="formData.bio"
                  :rows="3"
                  :has-error="hasError"
                  placeholder="Tell us about yourself..."
                />
              </template>
            </VFormField>
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
import { VButton, VCard, VFormField, VInput, VSelect, VTextarea } from '@/components/ui';
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
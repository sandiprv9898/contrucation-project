<template>
  <div class="user-form">
    <VCard variant="elevated">
      <template #header>
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ isEditing ? 'Edit User' : 'Create New User' }}
            </h3>
            <p class="text-sm text-gray-600 mt-1">
              {{ isEditing ? 'Update user information and permissions' : 'Add a new team member to your construction project' }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <VButton 
              variant="ghost" 
              size="sm"
              @click="toggleFormBuilder"
            >
              {{ useFormBuilder ? 'Manual Form' : 'Form Builder' }}
            </VButton>
            <VButton 
              variant="outline" 
              size="sm"
              @click="$emit('cancel')"
            >
              Cancel
            </VButton>
          </div>
        </div>
      </template>
      
      <template #content>
        <!-- Form Builder Mode -->
        <VFormBuilder
          v-if="useFormBuilder"
          :steps="formSteps"
          :validation-rules="validationRules"
          @submit="handleFormBuilderSubmit"
          @cancel="$emit('cancel')"
          :loading="loading"
          :submit-text="isEditing ? 'Update User' : 'Create User'"
        />
        
        <!-- Manual Form Mode -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-6">

          <!-- Personal Information Section -->
          <div class="space-y-6">
            <div class="border-l-4 border-orange-500 pl-4">
              <h4 class="text-md font-semibold text-gray-900 mb-1">Personal Information</h4>
              <p class="text-sm text-gray-600">Basic details about the team member</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Enhanced Name Field -->
              <VValidatedField
                v-model="formData.name"
                name="name"
                label="Full Name"
                :validation-rules="[validationRules.required('Full name is required'), validationRules.minLength(2, 'Name must be at least 2 characters')]"
                placeholder="Enter full name"
                required
              >
                <template #prefix>
                  <User class="w-4 h-4 text-gray-400" />
                </template>
                <template #badge>
                  <span class="text-xs font-medium">Primary</span>
                </template>
              </VValidatedField>

              <!-- Enhanced Email Field -->
              <VValidatedField
                v-model="formData.email"
                name="email"
                label="Email Address"
                type="email"
                :validation-rules="[validationRules.required('Email is required'), validationRules.email('Please enter a valid email')]"
                placeholder="Enter email address"
                required
              >
                <template #prefix>
                  <Mail class="w-4 h-4 text-gray-400" />
                </template>
                <template #suffix>
                  <CheckCircle v-if="formData.email && !getFirstError('email')" class="w-4 h-4 text-green-500" />
                </template>
              </VValidatedField>
            </div>
          </div>

          <!-- Role and Contact Section -->
          <div class="space-y-6">
            <div class="border-l-4 border-blue-500 pl-4">
              <h4 class="text-md font-semibold text-gray-900 mb-1">Role & Contact</h4>
              <p class="text-sm text-gray-600">Position and communication details</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Enhanced Role Field -->
              <VFormField
                label="Role"
                :error-message="getFirstError('role')"
                required
                badge="Critical"
                badge-variant="warning"
                tooltip="User role determines permissions and access levels"
              >
                <template #prefix>
                  <Shield class="w-4 h-4 text-gray-400" />
                </template>
                <template #default="{ fieldId, hasError }">
                  <VSelect
                    :id="fieldId"
                    v-model="formData.role"
                    :items="availableRoles"
                    placeholder="Select a role"
                    :has-error="hasError"
                    mode="custom"
                    searchable
                    required
                  >
                    <template #option="{ item }">
                      <div class="flex items-center gap-3">
                        <component :is="getRoleIcon(item.value)" class="w-4 h-4" :class="getRoleColor(item.value)" />
                        <div>
                          <div class="font-medium">{{ item.label }}</div>
                          <div class="text-xs text-gray-500">{{ getRoleDescription(item.value) }}</div>
                        </div>
                      </div>
                    </template>
                  </VSelect>
                </template>
              </VFormField>

              <!-- Enhanced Phone Field -->
              <VValidatedField
                v-model="formData.phone"
                name="phone"
                label="Phone Number"
                type="tel"
                :validation-rules="[validationRules.phone('Please enter a valid phone number')]"
                placeholder="+1 (555) 123-4567"
              >
                <template #prefix>
                  <Phone class="w-4 h-4 text-gray-400" />
                </template>
              </VValidatedField>
            </div>
          </div>

          <!-- Department Section -->
          <div class="space-y-6">
            <div class="border-l-4 border-green-500 pl-4">
              <h4 class="text-md font-semibold text-gray-900 mb-1">Organization</h4>
              <p class="text-sm text-gray-600">Department and team assignment</p>
            </div>
            
            <VFormField
              label="Department"
              :error-message="getFirstError('department')"
              help-text="Which department or team will this user belong to?"
            >
              <template #prefix>
                <Building class="w-4 h-4 text-gray-400" />
              </template>
              <template #default="{ fieldId, hasError }">
                <VSelect
                  :id="fieldId"
                  v-model="formData.department"
                  :items="departmentOptions"
                  placeholder="Select department"
                  :has-error="hasError"
                  mode="custom"
                  searchable
                  allow-create
                >
                  <template #option="{ item }">
                    <div class="flex items-center gap-3">
                      <component :is="getDepartmentIcon(item.value)" class="w-4 h-4 text-blue-600" />
                      <div>
                        <div class="font-medium">{{ item.label }}</div>
                        <div class="text-xs text-gray-500">{{ getDepartmentDescription(item.value) }}</div>
                      </div>
                    </div>
                  </template>
                </VSelect>
              </template>
            </VFormField>
          </div>

          <!-- Security Section (only for creation) -->
          <div v-if="!isEditing" class="space-y-6">
            <div class="border-l-4 border-red-500 pl-4">
              <h4 class="text-md font-semibold text-gray-900 mb-1">Security</h4>
              <p class="text-sm text-gray-600">Set up account security credentials</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Enhanced Password Field -->
              <VValidatedField
                v-model="formData.password"
                name="password"
                label="Password"
                type="password"
                :validation-rules="[validationRules.required('Password is required'), validationRules.minLength(8, 'Password must be at least 8 characters'), validationRules.strongPassword('Password must contain uppercase, lowercase, number, and special character')]"
                placeholder="Enter secure password"
                required
                :show-strength="true"
              >
                <template #prefix>
                  <Lock class="w-4 h-4 text-gray-400" />
                </template>
                <template #help>
                  <div class="text-xs text-gray-500 mt-2">
                    <div class="grid grid-cols-2 gap-2">
                      <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full" :class="passwordStrength.length >= 8 ? 'bg-green-500' : 'bg-gray-300'"></div>
                        <span>8+ characters</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full" :class="passwordStrength.hasUpper ? 'bg-green-500' : 'bg-gray-300'"></div>
                        <span>Uppercase</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full" :class="passwordStrength.hasLower ? 'bg-green-500' : 'bg-gray-300'"></div>
                        <span>Lowercase</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full" :class="passwordStrength.hasNumber ? 'bg-green-500' : 'bg-gray-300'"></div>
                        <span>Number</span>
                      </div>
                    </div>
                  </div>
                </template>
              </VValidatedField>

              <!-- Enhanced Password Confirmation -->
              <VValidatedField
                v-model="formData.password_confirmation"
                name="password_confirmation"
                label="Confirm Password"
                type="password"
                :validation-rules="[validationRules.required('Password confirmation is required'), validationRules.matchField('password', formData.password, 'Passwords must match')]"
                placeholder="Confirm password"
                required
              >
                <template #prefix>
                  <Lock class="w-4 h-4 text-gray-400" />
                </template>
                <template #suffix>
                  <CheckCircle v-if="formData.password_confirmation && formData.password === formData.password_confirmation" class="w-4 h-4 text-green-500" />
                </template>
              </VValidatedField>
            </div>
          </div>

          <!-- Bio Section (for updates) -->
          <div v-if="isEditing" class="space-y-6">
            <div class="border-l-4 border-purple-500 pl-4">
              <h4 class="text-md font-semibold text-gray-900 mb-1">Additional Information</h4>
              <p class="text-sm text-gray-600">Optional details about the team member</p>
            </div>
            
            <VFormField
              label="Bio"
              :error-message="getFirstError('bio')"
              help-text="Brief description about the user's background or expertise"
              :character-count="true"
              :max-length="500"
            >
              <template #prefix>
                <FileText class="w-4 h-4 text-gray-400" />
              </template>
              <template #default="{ fieldId, hasError }">
                <VTextarea
                  :id="fieldId"
                  v-model="formData.bio"
                  :rows="4"
                  :has-error="hasError"
                  :maxlength="500"
                  placeholder="Tell us about yourself, your experience, and expertise..."
                />
              </template>
            </VFormField>
          </div>

          <!-- Enhanced Form Actions -->
          <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <div class="flex items-center gap-1">
                <div class="w-2 h-2 rounded-full" :class="isFormValid ? 'bg-green-500' : 'bg-gray-300'"></div>
                <span>Form {{ isFormValid ? 'Valid' : 'Incomplete' }}</span>
              </div>
              <span class="text-gray-400">•</span>
              <span>{{ Object.keys(formData).filter(key => formData[key]).length }} of {{ Object.keys(formData).length }} fields completed</span>
            </div>
            
            <div class="flex items-center gap-3">
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
                :loading="loading"
                class="min-w-[140px]"
              >
                <template #icon>
                  <UserPlus v-if="!isEditing" class="w-4 h-4" />
                  <UserCheck v-else class="w-4 h-4" />
                </template>
                {{ isEditing ? 'Update User' : 'Create User' }}
              </VButton>
            </div>
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
import { useValidation, validationRules } from '@/composables/useValidation';
import { VButton, VCard, VFormField, VInput, VSelect, VTextarea, VFormBuilder, VValidatedField } from '@/components/ui';
import { User, Mail, Phone, Shield, Building, Lock, FileText, CheckCircle, UserPlus, UserCheck, Crown, ClipboardList, HardHat, Hammer, Building2, Settings, ShieldCheck, Briefcase, Package, Wrench, Truck } from 'lucide-vue-next';
import type { CreateUserRequest, UpdateUserRequest, UserProfile } from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';
import type { FormStep } from '@/components/ui/VFormBuilder.vue';

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
const useFormBuilder = ref(false);

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

const departmentOptions = computed(() => [
  { value: 'construction', label: 'Construction' },
  { value: 'engineering', label: 'Engineering' },
  { value: 'safety', label: 'Safety & Compliance' },
  { value: 'management', label: 'Project Management' },
  { value: 'logistics', label: 'Logistics & Supply' },
  { value: 'maintenance', label: 'Equipment Maintenance' },
  { value: 'quality', label: 'Quality Assurance' },
  { value: 'finance', label: 'Finance & Accounting' }
]);

const passwordStrength = computed(() => {
  const password = formData.value.password || '';
  return {
    length: password.length,
    hasUpper: /[A-Z]/.test(password),
    hasLower: /[a-z]/.test(password),
    hasNumber: /\d/.test(password),
    hasSpecial: /[!@#$%^&*(),.?":{}|<>]/.test(password)
  };
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
 * Toggle form builder mode
 */
const toggleFormBuilder = (): void => {
  useFormBuilder.value = !useFormBuilder.value;
};

/**
 * Handle form builder submission
 */
const handleFormBuilderSubmit = (data: any): void => {
  formData.value = { ...formData.value, ...data };
  handleSubmit();
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
 * Get role icon
 */
const getRoleIcon = (role: string) => {
  const icons = {
    admin: Crown,
    project_manager: ClipboardList,
    supervisor: HardHat,
    field_worker: Hammer
  };
  return icons[role] || Shield;
};

/**
 * Get role color
 */
const getRoleColor = (role: string): string => {
  const colors = {
    admin: 'text-red-600',
    project_manager: 'text-blue-600',
    supervisor: 'text-orange-600',
    field_worker: 'text-green-600'
  };
  return colors[role] || 'text-gray-600';
};

/**
 * Get role description
 */
const getRoleDescription = (role: string): string => {
  const descriptions = {
    admin: 'Full system access and user management',
    project_manager: 'Project oversight and team coordination',
    supervisor: 'Team leadership and task supervision',
    field_worker: 'On-site construction and task execution'
  };
  return descriptions[role] || 'Standard user access';
};

/**
 * Get department icon
 */
const getDepartmentIcon = (department: string) => {
  const icons = {
    construction: Building2,
    engineering: Settings,
    safety: ShieldCheck,
    management: Briefcase,
    logistics: Package,
    maintenance: Wrench,
    quality: CheckCircle,
    finance: Building
  };
  return icons[department] || Building;
};

/**
 * Get department description
 */
const getDepartmentDescription = (department: string): string => {
  const descriptions = {
    construction: 'On-site building and construction work',
    engineering: 'Technical design and planning',
    safety: 'Safety protocols and compliance',
    management: 'Project and team management',
    logistics: 'Supply chain and resource coordination',
    maintenance: 'Equipment and facility maintenance',
    quality: 'Quality control and assurance',
    finance: 'Financial planning and accounting'
  };
  return descriptions[department] || 'General department operations';
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
<template>
  <div class="user-profile space-y-6">
    <!-- Enhanced Profile Header -->
    <VCard variant="elevated" class="overflow-hidden">
      <!-- Background Pattern -->
      <div class="h-24 bg-gradient-to-r from-orange-500 via-orange-600 to-orange-700 relative">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute inset-0 opacity-20 bg-repeat pattern-dots"></div>
      </div>
      
      <div class="p-6 -mt-8 relative">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
          <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            <!-- Enhanced Avatar -->
            <div class="relative">
              <div class="h-24 w-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-2xl font-semibold text-white shadow-lg ring-4 ring-white">
                {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
              </div>
              <!-- Status Indicator -->
              <div class="absolute -top-1 -right-1">
                <div v-if="user?.email_verified_at" class="h-6 w-6 bg-green-500 rounded-full flex items-center justify-center ring-2 ring-white">
                  <CheckCircle class="h-3 w-3 text-white" />
                </div>
                <div v-else class="h-6 w-6 bg-yellow-500 rounded-full flex items-center justify-center ring-2 ring-white">
                  <Clock class="h-3 w-3 text-white" />
                </div>
              </div>
            </div>
            
            <!-- Enhanced User Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    {{ user?.name }}
                    <VBadge v-if="user?.role === 'admin'" variant="destructive" class="text-xs">
                      <Crown class="w-3 h-3 mr-1" />
                      Admin
                    </VBadge>
                  </h2>
                  <p class="text-gray-600 flex items-center gap-1 mt-1">
                    <Mail class="w-4 h-4" />
                    {{ user?.email }}
                  </p>
                  <p v-if="user?.phone" class="text-gray-600 flex items-center gap-1">
                    <Phone class="w-4 h-4" />
                    {{ user?.phone }}
                  </p>
                </div>
              </div>
              
              <div class="flex flex-wrap items-center gap-2">
                <VBadge :variant="getRoleVariant(user?.role)" class="flex items-center gap-1">
                  <component :is="getRoleIcon(user?.role)" class="w-3 h-3" />
                  {{ getRoleLabel(user?.role) }}
                </VBadge>
                <VBadge :variant="user?.email_verified_at ? 'success' : 'warning'" class="flex items-center gap-1">
                  <component :is="user?.email_verified_at ? CheckCircle : Clock" class="w-3 h-3" />
                  {{ user?.email_verified_at ? 'Verified' : 'Pending Verification' }}
                </VBadge>
                <VBadge v-if="user?.department" variant="outline" class="flex items-center gap-1">
                  <component :is="getDepartmentIcon(user?.department)" class="w-3 h-3" />
                  {{ formatDepartment(user?.department) }}
                </VBadge>
              </div>
            </div>
          </div>
          
          <!-- Enhanced Actions -->
          <div class="flex items-center gap-2 shrink-0">
            <VButton 
              v-if="canEditUser"
              variant="outline"
              @click="handleEdit"
            >
              <Edit class="mr-2 h-4 w-4" />
              Edit Profile
            </VButton>
            
            <!-- Quick Actions Dropdown -->
            <VButton variant="outline" @click="showQuickActions = !showQuickActions" class="px-3">
              <MoreVertical class="h-4 w-4" />
            </VButton>
          </div>
        </div>

        <!-- Quick Actions Panel -->
        <div v-if="showQuickActions" class="mt-4 p-4 bg-gray-50 rounded-lg border">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <VButton
              v-if="!user?.email_verified_at && canEditUser"
              variant="outline"
              size="sm"
              @click="handleResendVerification"
              class="justify-start"
            >
              <Mail class="mr-2 h-4 w-4" />
              Resend Verification
            </VButton>
            <VButton
              v-if="canEditUser"
              variant="outline"
              size="sm"
              @click="handleChangePassword"
              class="justify-start"
            >
              <Lock class="mr-2 h-4 w-4" />
              Change Password
            </VButton>
            <VButton
              v-if="canEditUser"
              variant="outline"
              size="sm"
              @click="handleChangeRole"
              class="justify-start"
            >
              <Shield class="mr-2 h-4 w-4" />
              Change Role
            </VButton>
            <VButton
              v-if="canDeleteUser"
              variant="destructive"
              size="sm"
              @click="handleDelete"
              class="justify-start"
            >
              <Trash class="mr-2 h-4 w-4" />
              Delete User
            </VButton>
          </div>
        </div>
      </div>
    </VCard>

    <!-- Enhanced Profile Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Personal Information with Advanced Form Fields -->
      <div class="lg:col-span-2">
        <VCard>
          <template #header>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <User class="w-5 h-5 text-gray-600" />
                <h3 class="text-lg font-semibold">Personal Information</h3>
              </div>
              <VButton v-if="canEditUser" variant="ghost" size="sm" @click="toggleEditMode">
                <Edit class="w-4 h-4 mr-1" />
                {{ editMode ? 'Cancel' : 'Edit' }}
              </VButton>
            </div>
          </template>
          
          <template #content>
            <div v-if="!editMode" class="space-y-6">
              <!-- Display Mode -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <VFormField label="Full Name" class="space-y-2">
                  <template #prefix>
                    <User class="w-4 h-4 text-gray-400" />
                  </template>
                  <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                    {{ user?.name || 'N/A' }}
                  </div>
                </VFormField>

                <VFormField label="Email Address" class="space-y-2">
                  <template #prefix>
                    <Mail class="w-4 h-4 text-gray-400" />
                  </template>
                  <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md flex items-center justify-between">
                    <span>{{ user?.email || 'N/A' }}</span>
                    <VBadge v-if="user?.email_verified_at" variant="success" size="sm">
                      <CheckCircle class="w-3 h-3 mr-1" />
                      Verified
                    </VBadge>
                  </div>
                </VFormField>

                <VFormField label="Phone Number" class="space-y-2">
                  <template #prefix>
                    <Phone class="w-4 h-4 text-gray-400" />
                  </template>
                  <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                    {{ user?.phone || 'Not provided' }}
                  </div>
                </VFormField>

                <VFormField label="Department" class="space-y-2">
                  <template #prefix>
                    <component :is="getDepartmentIcon(user?.department)" class="w-4 h-4 text-gray-400" />
                  </template>
                  <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                    {{ formatDepartment(user?.department) || 'Not assigned' }}
                  </div>
                </VFormField>

                <VFormField label="Company" class="space-y-2">
                  <template #prefix>
                    <Building class="w-4 h-4 text-gray-400" />
                  </template>
                  <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                    {{ user?.company?.name || 'N/A' }}
                  </div>
                </VFormField>

                <VFormField label="Member Since" class="space-y-2">
                  <template #prefix>
                    <Calendar class="w-4 h-4 text-gray-400" />
                  </template>
                  <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">
                    {{ formatDate(user?.created_at) }}
                  </div>
                </VFormField>
              </div>
              
              <VFormField v-if="user?.bio" label="Bio" class="space-y-2">
                <template #prefix>
                  <FileText class="w-4 h-4 text-gray-400" />
                </template>
                <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-md">
                  {{ user.bio }}
                </div>
              </VFormField>
            </div>

            <!-- Edit Mode with Advanced Form Fields -->
            <form v-else @submit.prevent="handleUpdateProfile" class="space-y-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <VValidatedField
                  v-model="editFormData.name"
                  name="name"
                  label="Full Name"
                  :validation-rules="[validationRules.required('Name is required'), validationRules.minLength(2, 'Name must be at least 2 characters')]"
                  placeholder="Enter full name"
                  required
                >
                  <template #prefix>
                    <User class="w-4 h-4 text-gray-400" />
                  </template>
                </VValidatedField>

                <VFormField label="Email Address" help-text="Contact admin to change email address">
                  <template #prefix>
                    <Mail class="w-4 h-4 text-gray-400" />
                  </template>
                  <template #default="{ fieldId }">
                    <VInput
                      :id="fieldId"
                      :value="user?.email"
                      disabled
                      class="bg-gray-100"
                    />
                  </template>
                  <template #suffix>
                    <VBadge v-if="user?.email_verified_at" variant="success" size="sm">
                      Verified
                    </VBadge>
                  </template>
                </VFormField>

                <VValidatedField
                  v-model="editFormData.phone"
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

                <VFormField label="Department" help-text="Your work department or team">
                  <template #prefix>
                    <Building class="w-4 h-4 text-gray-400" />
                  </template>
                  <template #default="{ fieldId }">
                    <VSelect
                      :id="fieldId"
                      v-model="editFormData.department"
                      :items="departmentOptions"
                      placeholder="Select department"
                      mode="custom"
                      searchable
                    >
                      <template #option="{ item }">
                        <div class="flex items-center gap-2">
                          <component :is="getDepartmentIcon(item.value)" class="w-4 h-4 text-blue-600" />
                          <span>{{ item.label }}</span>
                        </div>
                      </template>
                    </VSelect>
                  </template>
                </VFormField>
              </div>

              <VFormField
                label="Bio"
                help-text="Tell us about yourself, your experience, and expertise"
                :character-count="true"
                :max-length="500"
              >
                <template #prefix>
                  <FileText class="w-4 h-4 text-gray-400" />
                </template>
                <template #default="{ fieldId }">
                  <VTextarea
                    :id="fieldId"
                    v-model="editFormData.bio"
                    :rows="4"
                    :maxlength="500"
                    placeholder="Brief description about yourself, your background, and expertise..."
                  />
                </template>
              </VFormField>

              <!-- Form Actions -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <VButton type="button" variant="outline" @click="cancelEdit">
                  Cancel
                </VButton>
                <VButton type="submit" :loading="updateLoading">
                  <Save class="w-4 h-4 mr-2" />
                  Save Changes
                </VButton>
              </div>
            </form>
          </template>
        </VCard>
      </div>

      <!-- Enhanced Sidebar -->
      <div class="space-y-6">
        <!-- Enhanced Role Permissions -->
        <VCard>
          <template #header>
            <div class="flex items-center gap-2">
              <Shield class="w-5 h-5 text-gray-600" />
              <h3 class="text-lg font-semibold">Role Permissions</h3>
            </div>
          </template>
          
          <template #content>
            <div class="space-y-3">
              <div 
                v-for="(permission, key) in userPermissions" 
                :key="key"
                class="flex items-center justify-between p-2 rounded-lg"
                :class="permission ? 'bg-green-50' : 'bg-gray-50'"
              >
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full" :class="permission ? 'bg-green-500' : 'bg-gray-300'"></div>
                  <span class="text-sm" :class="permission ? 'text-green-900' : 'text-gray-600'">
                    {{ formatPermissionLabel(key) }}
                  </span>
                </div>
                <CheckCircle v-if="permission" class="h-4 w-4 text-green-600" />
                <X v-else class="h-4 w-4 text-gray-400" />
              </div>
            </div>
          </template>
        </VCard>

        <!-- Enhanced Activity Timeline -->
        <VCard>
          <template #header>
            <div class="flex items-center gap-2">
              <Activity class="w-5 h-5 text-gray-600" />
              <h3 class="text-lg font-semibold">Activity Timeline</h3>
            </div>
          </template>
          
          <template #content>
            <div class="space-y-4">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                  <LogIn class="w-4 h-4 text-green-600" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900">Last Login</div>
                  <div class="text-xs text-gray-500">
                    {{ formatDateTime(user?.last_login_at) || 'Never' }}
                  </div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                  <Mail class="w-4 h-4 text-blue-600" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900">Email Verified</div>
                  <div class="text-xs text-gray-500">
                    {{ formatDateTime(user?.email_verified_at) || 'Not verified' }}
                  </div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                  <Edit class="w-4 h-4 text-purple-600" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900">Profile Updated</div>
                  <div class="text-xs text-gray-500">
                    {{ formatDateTime(user?.updated_at) || 'Never' }}
                  </div>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center shrink-0">
                  <UserPlus class="w-4 h-4 text-orange-600" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900">Account Created</div>
                  <div class="text-xs text-gray-500">
                    {{ formatDateTime(user?.created_at) }}
                  </div>
                </div>
              </div>
            </div>
          </template>
        </VCard>

        <!-- User Statistics -->
        <VCard>
          <template #header>
            <div class="flex items-center gap-2">
              <BarChart3 class="w-5 h-5 text-gray-600" />
              <h3 class="text-lg font-semibold">Statistics</h3>
            </div>
          </template>
          
          <template #content>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-3 bg-blue-50 rounded-lg">
                <div class="text-lg font-semibold text-blue-900">{{ user?.projects_count || 0 }}</div>
                <div class="text-xs text-blue-600">Projects</div>
              </div>
              <div class="text-center p-3 bg-green-50 rounded-lg">
                <div class="text-lg font-semibold text-green-900">{{ user?.tasks_completed || 0 }}</div>
                <div class="text-xs text-green-600">Tasks</div>
              </div>
              <div class="text-center p-3 bg-purple-50 rounded-lg">
                <div class="text-lg font-semibold text-purple-900">{{ getDaysActive() }}</div>
                <div class="text-xs text-purple-600">Days Active</div>
              </div>
              <div class="text-center p-3 bg-orange-50 rounded-lg">
                <div class="text-lg font-semibold text-orange-900">{{ user?.login_streak || 0 }}</div>
                <div class="text-xs text-orange-600">Login Streak</div>
              </div>
            </div>
          </template>
        </VCard>
      </div>
    </div>

    <!-- Enhanced Projects Section -->
    <VCard v-if="user?.role !== 'admin'">
      <template #header>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Briefcase class="w-5 h-5 text-gray-600" />
            <h3 class="text-lg font-semibold">Assigned Projects</h3>
          </div>
          <VBadge variant="outline">{{ projects.length }} Projects</VBadge>
        </div>
      </template>
      
      <template #content>
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin w-6 h-6 border-2 border-orange-500 border-t-transparent rounded-full mx-auto"></div>
          <p class="text-gray-500 mt-2">Loading projects...</p>
        </div>
        
        <div v-else-if="!projects || projects.length === 0" class="text-center py-12">
          <Briefcase class="w-12 h-12 text-gray-300 mx-auto mb-4" />
          <h4 class="text-lg font-medium text-gray-900 mb-2">No Projects Assigned</h4>
          <p class="text-gray-600 mb-4">This user hasn't been assigned to any projects yet.</p>
        </div>
        
        <div v-else class="grid gap-4">
          <div 
            v-for="project in projects" 
            :key="project.id"
            class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <Briefcase class="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ project.name }}</div>
                <div class="text-sm text-gray-600 flex items-center gap-2">
                  <VBadge :variant="getProjectStatusVariant(project.status)" size="sm">
                    {{ project.status }}
                  </VBadge>
                  <span>•</span>
                  <span>{{ project.progress || 0 }}% complete</span>
                </div>
              </div>
            </div>
            <VButton variant="outline" size="sm" @click="viewProject(project.id)">
              <Eye class="w-4 h-4 mr-1" />
              View
            </VButton>
          </div>
        </div>
      </template>
    </VCard>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserPermissions } from '../composables/useUserPermissions';
import { useValidation, validationRules } from '@/composables/useValidation';
import { VButton, VCard, VBadge, VFormField, VInput, VSelect, VTextarea, VValidatedField } from '@/components/ui';
import { 
  Edit, Trash, CheckCircle, Clock, X, MoreVertical, Mail, Shield, Lock, User, Phone, 
  Building, Calendar, FileText, Save, Activity, LogIn, UserPlus, BarChart3, Briefcase,
  Eye, Crown, ClipboardList, HardHat, Hammer, Building2, Settings, ShieldCheck, Package
} from 'lucide-vue-next';
import { ROLE_LABELS, ROLE_BADGE_VARIANTS, ROLE_PERMISSIONS } from '../constants/users.constants';
import type { UserProfile } from '../types/users.types';

interface Props {
  user: UserProfile | null;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false
});

const emit = defineEmits<{
  edit: [];
  delete: [];
  resendVerification: [];
  changePassword: [];
  changeRole: [];
  updateProfile: [data: any];
}>();

// ==================== COMPOSABLES ====================
const router = useRouter();
const { hasPermission, canEditUser: checkCanEditUser, canDeleteUser: checkCanDeleteUser } = useUserPermissions();

// ==================== STATE ====================
const projects = ref<any[]>([]);
const editMode = ref(false);
const updateLoading = ref(false);
const showQuickActions = ref(false);

const editFormData = ref({
  name: '',
  phone: '',
  department: '',
  bio: ''
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

// ==================== COMPUTED ====================
const canEditUser = computed(() => {
  if (!props.user) return false;
  return checkCanEditUser(props.user);
});

const canDeleteUser = computed(() => {
  if (!props.user) return false;
  return checkCanDeleteUser(props.user);
});

const userPermissions = computed(() => {
  if (!props.user?.role) return {};
  return ROLE_PERMISSIONS[props.user.role] || {};
});

// ==================== METHODS ====================

/**
 * Get role label
 */
const getRoleLabel = (role?: string): string => {
  if (!role) return 'Unknown';
  return ROLE_LABELS[role as keyof typeof ROLE_LABELS] || role;
};

/**
 * Get role badge variant
 */
const getRoleVariant = (role?: string): 'default' | 'outline' | 'success' | 'warning' | 'destructive' | 'secondary' => {
  if (!role) return 'default';
  return ROLE_BADGE_VARIANTS[role as keyof typeof ROLE_BADGE_VARIANTS] || 'default';
};

/**
 * Get role icon
 */
const getRoleIcon = (role?: string) => {
  const icons = {
    admin: Crown,
    project_manager: ClipboardList,
    supervisor: HardHat,
    field_worker: Hammer
  };
  return icons[role] || Shield;
};

/**
 * Get department icon
 */
const getDepartmentIcon = (department?: string) => {
  const icons = {
    construction: Building2,
    engineering: Settings,
    safety: ShieldCheck,
    management: Briefcase,
    logistics: Package,
    maintenance: Settings,
    quality: CheckCircle,
    finance: Building
  };
  return icons[department] || Building;
};

/**
 * Format department display
 */
const formatDepartment = (department?: string): string => {
  const departmentMap = {
    construction: 'Construction',
    engineering: 'Engineering',
    safety: 'Safety & Compliance',
    management: 'Project Management',
    logistics: 'Logistics & Supply',
    maintenance: 'Equipment Maintenance',
    quality: 'Quality Assurance',
    finance: 'Finance & Accounting'
  };
  if (!department) return '';
  return departmentMap[department] || department;
};

/**
 * Format date
 */
const formatDate = (dateString?: string): string => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString();
};

/**
 * Format date and time
 */
const formatDateTime = (dateString?: string): string => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString();
};

/**
 * Format permission label
 */
const formatPermissionLabel = (key: string): string => {
  const labels: Record<string, string> = {
    canManageUsers: 'Manage Users',
    canViewUsers: 'View Users',
    canEditUser: 'Edit Users',
    canDeleteUser: 'Delete Users',
    canManageProjects: 'Manage Projects',
    canViewProjects: 'View Projects',
    canAssignTasks: 'Assign Tasks',
    canViewReports: 'View Reports',
    canManageCompany: 'Manage Company',
    canManageFinance: 'Manage Finance',
    canApproveTimesheet: 'Approve Timesheets',
    canManageEquipment: 'Manage Equipment',
    canManageSafety: 'Manage Safety',
  };
  return labels[key] || key;
};

/**
 * Get days active
 */
const getDaysActive = (): number => {
  if (!props.user?.created_at) return 0;
  const created = new Date(props.user.created_at);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - created.getTime());
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

/**
 * Get project status variant
 */
const getProjectStatusVariant = (status: string): string => {
  const variants = {
    active: 'success',
    pending: 'warning',
    completed: 'default',
    cancelled: 'destructive'
  };
  return variants[status] || 'outline';
};

/**
 * Toggle edit mode
 */
const toggleEditMode = (): void => {
  editMode.value = !editMode.value;
  if (editMode.value) {
    // Initialize edit form with current user data
    editFormData.value = {
      name: props.user?.name || '',
      phone: props.user?.phone || '',
      department: props.user?.department || '',
      bio: props.user?.bio || ''
    };
  }
};

/**
 * Cancel edit
 */
const cancelEdit = (): void => {
  editMode.value = false;
  // Reset form data
  editFormData.value = {
    name: '',
    phone: '',
    department: '',
    bio: ''
  };
};

/**
 * Handle update profile
 */
const handleUpdateProfile = async (): Promise<void> => {
  updateLoading.value = true;
  try {
    emit('updateProfile', editFormData.value);
    editMode.value = false;
  } catch (error) {
    console.error('Failed to update profile:', error);
  } finally {
    updateLoading.value = false;
  }
};

/**
 * Handle edit
 */
const handleEdit = (): void => {
  emit('edit');
};

/**
 * Handle delete
 */
const handleDelete = (): void => {
  if (confirm(`Are you sure you want to delete ${props.user?.name}? This action cannot be undone.`)) {
    emit('delete');
  }
};

/**
 * Handle resend verification
 */
const handleResendVerification = (): void => {
  emit('resendVerification');
};

/**
 * Handle change password
 */
const handleChangePassword = (): void => {
  emit('changePassword');
};

/**
 * Handle change role
 */
const handleChangeRole = (): void => {
  emit('changeRole');
};

/**
 * View project
 */
const viewProject = (projectId: string): void => {
  router.push({ name: 'projects.view', params: { id: projectId } });
};

// ==================== LIFECYCLE ====================
onMounted(() => {
  // Load user's projects if applicable
  if (props.user?.role !== 'admin') {
    // Simulated project data
    projects.value = [
      {
        id: '1',
        name: 'Downtown Office Complex',
        status: 'active',
        progress: 75
      },
      {
        id: '2', 
        name: 'Residential Tower Project',
        status: 'pending',
        progress: 25
      }
    ];
  }
});
</script>
<template>
  <MainLayout>
    <div class="max-w-2xl mx-auto">
    <VCard>
      <div class="p-6">
        <h2 class="text-2xl font-bold mb-6">Change User Role</h2>
        
        <div v-if="user" class="space-y-6">
          <!-- User Info -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-lg font-semibold text-blue-700">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ user.name }}</div>
                <div class="text-sm text-gray-600">{{ user.email }}</div>
                <div class="mt-1">
                  <VBadge :variant="getRoleVariant(user.role)">
                    Current Role: {{ getRoleLabel(user.role) }}
                  </VBadge>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Role Selection -->
          <div>
            <VLabel for="newRole" class="text-sm font-medium text-gray-700 mb-2">
              Select New Role
            </VLabel>
            <select
              id="newRole"
              v-model="newRole"
              class="w-full border border-gray-300 rounded-md px-3 py-2 bg-white text-sm"
              required
            >
              <option value="">Select a role</option>
              <option 
                v-for="role in availableRoles" 
                :key="role.value" 
                :value="role.value"
                :disabled="role.value === user.role"
              >
                {{ role.label }} {{ role.value === user.role ? '(Current)' : '' }}
              </option>
            </select>
          </div>
          
          <!-- Warning Message -->
          <div v-if="newRole" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
              <AlertTriangle class="h-5 w-5 text-yellow-600 mr-2 flex-shrink-0" />
              <div class="text-sm text-yellow-800">
                <strong>Warning:</strong> Changing user role will immediately affect their permissions and access to the system.
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <VButton variant="outline" @click="handleCancel">
              Cancel
            </VButton>
            <VButton 
              @click="handleChangeRole"
              :disabled="!newRole || newRole === user.role || loading"
            >
              {{ loading ? 'Changing...' : 'Change Role' }}
            </VButton>
          </div>
        </div>
        
        <div v-else class="text-center py-8">
          <div v-if="fetchingUser" class="text-gray-500">Loading user...</div>
          <div v-else class="text-red-500">User not found</div>
        </div>
      </div>
    </VCard>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { VCard, VButton, VLabel, VBadge } from '@/components/ui';
import { AlertTriangle } from 'lucide-vue-next';
import { UsersApi } from '@/modules/users/api/users.api'
import MainLayout from '@/layouts/MainLayout.vue'
import { useUserPermissions } from '@/modules/users/composables/useUserPermissions';
import { ROLE_LABELS, ROLE_BADGE_VARIANTS } from '@/modules/users/constants/users.constants';
import type { UserProfile } from '@/modules/users/types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

const route = useRoute();
const router = useRouter();
const { getAssignableRoles } = useUserPermissions();

const user = ref<UserProfile | null>(null);
const newRole = ref<UserRole | ''>('');
const loading = ref(false);
const fetchingUser = ref(true);

const availableRoles = computed(() => {
  const assignableRoles = getAssignableRoles();
  
  return assignableRoles.map(role => ({
    value: role,
    label: ROLE_LABELS[role] || role
  }));
});

const getRoleLabel = (role?: string): string => {
  if (!role) return 'Unknown';
  return ROLE_LABELS[role as keyof typeof ROLE_LABELS] || role;
};

const getRoleVariant = (role?: string): 'default' | 'outline' | 'success' | 'warning' | 'destructive' => {
  if (!role) return 'default';
  return ROLE_BADGE_VARIANTS[role as keyof typeof ROLE_BADGE_VARIANTS] || 'default';
};

const handleChangeRole = async () => {
  if (!user.value || !newRole.value || newRole.value === user.value.role) return;
  
  loading.value = true;
  try {
    const updatedUser = await UsersApi.updateUser(user.value.id, { role: newRole.value });
    user.value = updatedUser;
    // Show success message
    console.log('User role changed successfully');
    router.push({ name: 'users.view', params: { id: user.value.id } });
  } catch (error) {
    console.error('Failed to change user role:', error);
    // Show error message
  } finally {
    loading.value = false;
  }
};

const handleCancel = () => {
  if (user.value) {
    router.push({ name: 'users.view', params: { id: user.value.id } });
  } else {
    router.push({ name: 'users.index' });
  }
};

onMounted(async () => {
  try {
    const userId = route.params.id as string;
    user.value = await UsersApi.getUser(userId);
  } catch (error) {
    console.error('Failed to fetch user:', error);
  } finally {
    fetchingUser.value = false;
  }
});
</script>
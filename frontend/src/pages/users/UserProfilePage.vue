<template>
    <div>
    <UserProfile 
      v-if="user"
      :user="user"
      :loading="loading"
      @edit="handleEdit"
      @delete="handleDelete"
      @resendVerification="handleResendVerification"
      @changePassword="handleChangePassword"
      @changeRole="handleChangeRole"
    />
    <div v-else class="text-center py-8">
      <div v-if="fetchingUser" class="text-gray-500">Loading user profile...</div>
      <div v-else class="text-red-500">User not found</div>
    </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { UserProfile } from '@/modules/users'
import { UsersApi } from '@/modules/users/api/users.api'
import type { UserProfile as UserProfileType } from '@/modules/users/types/users.types';

const route = useRoute();
const router = useRouter();
const user = ref<UserProfileType | null>(null);
const loading = ref(false);
const fetchingUser = ref(true);

const handleEdit = () => {
  if (user.value) {
    router.push({ name: 'users.edit', params: { id: user.value.id } });
  }
};

const handleDelete = async () => {
  if (!user.value) return;
  
  loading.value = true;
  try {
    await UsersApi.deleteUser(user.value.id);
    // Show success message
    console.log('User deleted successfully');
    router.push({ name: 'users.index' });
  } catch (error) {
    console.error('Failed to delete user:', error);
    // Show error message
  } finally {
    loading.value = false;
  }
};

const handleResendVerification = async () => {
  if (!user.value) return;
  
  loading.value = true;
  try {
    await UsersApi.resendEmailVerification(user.value.id);
    // Show success message
    console.log('Verification email sent successfully');
  } catch (error) {
    console.error('Failed to resend verification:', error);
    // Show error message
  } finally {
    loading.value = false;
  }
};

const handleChangePassword = () => {
  // This could open a modal or navigate to a password change page
  console.log('Change password functionality to be implemented');
};

const handleChangeRole = () => {
  if (user.value) {
    router.push({ name: 'users.role', params: { id: user.value.id } });
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
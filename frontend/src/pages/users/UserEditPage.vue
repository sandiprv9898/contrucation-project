<template>
    <div class="max-w-4xl mx-auto">
    <UserForm 
      v-if="user"
      :user="user"
      :loading="loading"
      @submit="handleUpdateUser"
      @cancel="handleCancel"
    />
    <div v-else class="text-center py-8">
      <div v-if="fetchingUser" class="text-gray-500">Loading user...</div>
      <div v-else class="text-red-500">User not found</div>
    </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { UserForm } from '@/modules/users'
import { UsersApi } from '@/modules/users/api/users.api'
import type { UserProfile, UpdateUserRequest } from '@/modules/users/types/users.types';

const route = useRoute();
const router = useRouter();
const user = ref<UserProfile | null>(null);
const loading = ref(false);
const fetchingUser = ref(true);

const handleUpdateUser = async (userData: UpdateUserRequest) => {
  if (!user.value) return;
  
  loading.value = true;
  try {
    const updatedUser = await UsersApi.updateUser(user.value.id, userData);
    user.value = updatedUser;
    // Show success message
    console.log('User updated successfully');
    router.push({ name: 'users.view', params: { id: user.value.id } });
  } catch (error) {
    console.error('Failed to update user:', error);
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
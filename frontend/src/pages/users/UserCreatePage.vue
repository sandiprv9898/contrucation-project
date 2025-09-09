<template>
  <div class="max-w-4xl mx-auto">
    <UserForm 
      :loading="loading"
      @submit="handleCreateUser"
      @cancel="handleCancel"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { UserForm } from '@/modules/users'
import { UsersApi } from '@/modules/users/api/users.api'
import type { CreateUserRequest, UpdateUserRequest } from '@/modules/users/types/users.types';

const router = useRouter();
const loading = ref(false);

const handleCreateUser = async (userData: CreateUserRequest | UpdateUserRequest) => {
  // Type guard to ensure we have the required fields for creating a user
  const createData = userData as CreateUserRequest;
  loading.value = true;
  try {
    await UsersApi.createUser(createData);
    // Show success message (would use toast in real implementation)
    console.log('User created successfully');
    router.push({ name: 'users.index' });
  } catch (error) {
    console.error('Failed to create user:', error);
    // Show error message
  } finally {
    loading.value = false;
  }
};

const handleCancel = () => {
  router.push({ name: 'users.index' });
};
</script>
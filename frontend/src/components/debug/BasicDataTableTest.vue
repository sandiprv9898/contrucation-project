<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">Basic DataTable Test (No Icons)</h1>
    
    <div class="bg-gray-50 p-4 rounded mb-4">
      <p><strong>Loading:</strong> {{ loading }}</p>
      <p><strong>Error:</strong> {{ error || 'None' }}</p>
      <p><strong>Users count:</strong> {{ users.length }}</p>
    </div>
    
    <!-- Try VDataTable with minimal props and no icons -->
    <div v-if="users.length > 0" class="mt-6">
      <h3 class="font-medium mb-2">VDataTable Test (No Icons):</h3>
      <BasicVDataTable
        :columns="columns"
        :data="users"
        :loading="loading"
        :exportable="false"
        :show-column-toggle="false"
        :show-density-toggle="false"
        :searchable="false"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUsers } from '@/modules/users/composables/useUsers'
import BasicVDataTable from './BasicVDataTable.vue'

const { 
  users,
  loading,
  error,
  loadUsers
} = useUsers()

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role' }
]

onMounted(async () => {
  console.log('BasicDataTableTest mounted')
  try {
    await loadUsers()
    console.log('Users loaded:', users.value.length)
  } catch (err) {
    console.error('Failed to load users:', err)
  }
})
</script>
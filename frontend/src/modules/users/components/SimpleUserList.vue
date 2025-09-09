<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Simple User List Test</h2>
    
    <!-- Debug Info -->
    <div class="mb-4 p-4 bg-gray-100 border rounded text-sm">
      <div><strong>Debug:</strong></div>
      <div>Users loaded: {{ users.length }}</div>
      <div>Loading: {{ loading }}</div>
    </div>

    <!-- Simple Table -->
    <div v-if="!loading && users.length > 0" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ user.name }}</td>
            <td class="px-4 py-3 text-sm text-gray-500">{{ user.email }}</td>
            <td class="px-4 py-3 text-sm text-gray-500">{{ user.role }}</td>
            <td class="px-4 py-3 text-sm text-gray-500">
              {{ user.email_verified_at ? 'Verified' : 'Pending' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="p-8 text-center">
      <div class="animate-spin h-8 w-8 border-2 border-orange-500 border-t-transparent rounded-full mx-auto"></div>
      <p class="mt-2 text-gray-600">Loading users...</p>
    </div>

    <!-- Empty State -->
    <div v-else class="p-8 text-center text-gray-500">
      No users found
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUsers } from '../composables/useUsers'

const { users, loading, loadUsers } = useUsers()

// Load users on mount
onMounted(async () => {
  try {
    await loadUsers()
    console.log('Users loaded:', users.value.length)
  } catch (error) {
    console.error('Failed to load users:', error)
  }
})
</script>
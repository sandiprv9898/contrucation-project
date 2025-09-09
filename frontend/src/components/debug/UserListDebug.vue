<template>
  <div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">User List Debug</h1>
    
    <div class="bg-gray-50 p-4 rounded">
      <h2 class="font-semibold mb-2">Loading State:</h2>
      <p>Loading: {{ loading }}</p>
      <p>Error: {{ error }}</p>
    </div>
    
    <div class="bg-gray-50 p-4 rounded">
      <h2 class="font-semibold mb-2">Statistics:</h2>
      <pre>{{ JSON.stringify(statistics, null, 2) }}</pre>
    </div>
    
    <div class="bg-gray-50 p-4 rounded">
      <h2 class="font-semibold mb-2">Users Data ({{ users.length }} total):</h2>
      <pre>{{ JSON.stringify(users, null, 2) }}</pre>
    </div>
    
    <div class="bg-gray-50 p-4 rounded">
      <h2 class="font-semibold mb-2">Filtered Users ({{ filteredUsers.length }} total):</h2>
      <pre>{{ JSON.stringify(filteredUsers, null, 2) }}</pre>
    </div>
    
    <button 
      @click="triggerLoadUsers" 
      :disabled="loading"
      class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
    >
      {{ loading ? 'Loading...' : 'Load Users' }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUsers } from '@/modules/users/composables/useUsers'

const { 
  users,
  statistics, 
  loading,
  error,
  loadUsers,
  filteredUsers
} = useUsers()

const triggerLoadUsers = async () => {
  try {
    await loadUsers()
    console.log('Users loaded:', users.value.length)
  } catch (err) {
    console.error('Failed to load users:', err)
  }
}

onMounted(async () => {
  console.log('UserListDebug mounted, loading users...')
  await triggerLoadUsers()
})
</script>
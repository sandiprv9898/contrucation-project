<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">Minimal User Test (No Icons)</h1>
    
    <div class="bg-gray-50 p-4 rounded mb-4">
      <p><strong>Loading:</strong> {{ loading }}</p>
      <p><strong>Error:</strong> {{ error || 'None' }}</p>
      <p><strong>Users count:</strong> {{ users.length }}</p>
      <p><strong>Statistics:</strong> {{ JSON.stringify(statistics) }}</p>
    </div>
    
    <div v-if="users.length > 0">
      <h3 class="font-medium mb-2">Raw User Data:</h3>
      <div class="bg-white border rounded p-4 mb-4">
        <pre>{{ JSON.stringify(users, null, 2) }}</pre>
      </div>
      
      <h3 class="font-medium mb-2">Simple HTML Table:</h3>
      <table class="min-w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="border p-2">Name</th>
            <th class="border p-2">Email</th>
            <th class="border p-2">Role</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
            <td class="border p-2">{{ user.name }}</td>
            <td class="border p-2">{{ user.email }}</td>
            <td class="border p-2">{{ user.role }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <button 
      @click="manualLoad" 
      class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
    >
      Manual Load Test
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
  loadUsers
} = useUsers()

const manualLoad = async () => {
  try {
    console.log('Manual load triggered...')
    await loadUsers()
    console.log('Manual load completed, users:', users.value.length)
  } catch (err) {
    console.error('Manual load failed:', err)
  }
}

onMounted(async () => {
  console.log('MinimalUserTest mounted')
  await manualLoad()
})
</script>
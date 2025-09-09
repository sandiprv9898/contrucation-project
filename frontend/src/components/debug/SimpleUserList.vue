<template>
  <div class="p-6 space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-4">Simple User List Test</h1>
      
      <!-- Statistics -->
      <div class="grid grid-cols-4 gap-4 mb-6 text-sm">
        <div class="bg-blue-50 p-3 rounded">
          <div class="font-semibold text-blue-900">{{ statistics.total }}</div>
          <div class="text-blue-600">Total Users</div>
        </div>
        <div class="bg-green-50 p-3 rounded">
          <div class="font-semibold text-green-900">{{ statistics.active }}</div>
          <div class="text-green-600">Active</div>
        </div>
        <div class="bg-purple-50 p-3 rounded">
          <div class="font-semibold text-purple-900">{{ statistics.verified }}</div>
          <div class="text-purple-600">Verified</div>
        </div>
        <div class="bg-orange-50 p-3 rounded">
          <div class="font-semibold text-orange-900">{{ statistics.thisMonth }}</div>
          <div class="text-orange-600">This Month</div>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="text-center py-8">
        <div class="text-gray-500">Loading users...</div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="text-center py-8 text-red-600">
        Error: {{ error }}
      </div>

      <!-- Data table test -->
      <div v-else>
        <h3 class="font-semibold mb-4">VDataTable Test ({{ users.length }} users loaded)</h3>
        
        <!-- Basic Table Test -->
        <div class="mb-6">
          <h4 class="font-medium mb-2">Basic Table without VDataTable:</h4>
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-4 py-2 text-sm text-gray-900">{{ user.name }}</td>
                  <td class="px-4 py-2 text-sm text-gray-600">{{ user.email }}</td>
                  <td class="px-4 py-2 text-sm">
                    <span class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                      {{ user.role }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- VDataTable Test -->
        <div>
          <h4 class="font-medium mb-2">VDataTable Test:</h4>
          <VDataTable
            :columns="columns"
            :data="users"
            :loading="loading"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useUsers } from '@/modules/users/composables/useUsers'
import { VDataTable } from '@/components/ui'

const { 
  users,
  statistics, 
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
  try {
    await loadUsers()
    console.log('Users loaded successfully:', users.value.length)
  } catch (err) {
    console.error('Failed to load users:', err)
  }
})
</script>
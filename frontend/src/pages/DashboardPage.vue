<template>
  <div class="min-h-screen bg-background">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-foreground">Dashboard</h1>
          <p class="text-muted-foreground mt-2">
            Welcome back, {{ user?.name }}!
          </p>
        </div>
        
        <!-- User Info & Logout -->
        <div class="flex items-center gap-4">
          <div class="text-right">
            <p class="font-medium text-foreground">{{ user?.name }}</p>
            <p class="text-sm text-muted-foreground capitalize">{{ user?.role?.replace('_', ' ') }}</p>
          </div>
          
          <VButton
            variant="ghost"
            size="sm"
            @click="handleLogout"
            :loading="isLoading"
          >
            {{ isLoading ? 'Logging out...' : 'Logout' }}
          </VButton>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <VCard class="p-6">
          <h3 class="font-semibold text-lg mb-2">Projects</h3>
          <p class="text-muted-foreground mb-4">Manage your construction projects</p>
          <VButton variant="primary" size="sm">View Projects</VButton>
        </VCard>

        <VCard class="p-6">
          <h3 class="font-semibold text-lg mb-2">Team</h3>
          <p class="text-muted-foreground mb-4">Collaborate with your team members</p>
          <VButton variant="secondary" size="sm">View Team</VButton>
        </VCard>

        <VCard class="p-6">
          <h3 class="font-semibold text-lg mb-2">Reports</h3>
          <p class="text-muted-foreground mb-4">Track progress and performance</p>
          <VButton variant="ghost" size="sm">View Reports</VButton>
        </VCard>
      </div>

      <!-- User Information -->
      <div class="mt-8">
        <VCard class="p-6">
          <h3 class="font-semibold text-lg mb-4">User Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-muted-foreground">Email</p>
              <p class="font-medium">{{ user?.email }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Role</p>
              <p class="font-medium capitalize">{{ user?.role?.replace('_', ' ') }}</p>
            </div>
            <div v-if="user?.company">
              <p class="text-sm text-muted-foreground">Company</p>
              <p class="font-medium">{{ user.company.name }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Member Since</p>
              <p class="font-medium">{{ formatDate(user?.created_at) }}</p>
            </div>
          </div>
        </VCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuth } from '@/modules/auth'
import VButton from '@/components/ui/VButton.vue'
import VCard from '@/components/ui/VCard.vue'

const { user, logout, isLoading, initAuth } = useAuth()

const handleLogout = async () => {
  try {
    await logout()
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

const formatDate = (dateString?: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Initialize auth state on component mount
onMounted(async () => {
  await initAuth()
})
</script>
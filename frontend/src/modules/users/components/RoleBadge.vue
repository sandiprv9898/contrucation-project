<template>
  <VBadge 
    :variant="roleConfig.variant"
    :class="roleConfig.class"
  >
    <component 
      :is="roleConfig.icon" 
      v-if="showIcon && roleConfig.icon"
      class="mr-1 h-3 w-3" 
    />
    {{ roleConfig.label }}
  </VBadge>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { VBadge } from '@/components/ui';
import { Shield, Users, Eye, Wrench } from 'lucide-vue-next';
import { ROLE_LABELS } from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

interface Props {
  role: UserRole;
  showIcon?: boolean;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  showIcon: true,
  size: 'md'
});

// ==================== COMPUTED ====================
const roleConfig = computed(() => {
  const configs = {
    admin: {
      variant: 'destructive' as const,
      class: 'text-xs font-medium bg-red-100 text-red-800 border-red-200',
      icon: Shield,
      label: ROLE_LABELS.admin
    },
    project_manager: {
      variant: 'default' as const,
      class: 'text-xs font-medium bg-blue-100 text-blue-800 border-blue-200',
      icon: Users,
      label: ROLE_LABELS.project_manager
    },
    supervisor: {
      variant: 'warning' as const,
      class: 'text-xs font-medium bg-yellow-100 text-yellow-800 border-yellow-200',
      icon: Eye,
      label: ROLE_LABELS.supervisor
    },
    field_worker: {
      variant: 'success' as const,
      class: 'text-xs font-medium bg-green-100 text-green-800 border-green-200',
      icon: Wrench,
      label: ROLE_LABELS.field_worker
    }
  };
  
  return configs[props.role] || configs.field_worker;
});
</script>
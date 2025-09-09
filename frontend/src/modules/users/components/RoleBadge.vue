<template>
  <div
    :class="cn(
      'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-200',
      'shadow-sm hover:shadow-md transform hover:scale-105',
      'border border-opacity-20 backdrop-blur-sm',
      'cursor-default select-none',
      sizeClasses,
      roleConfig.classes,
      interactive && 'hover:ring-2 hover:ring-offset-1 cursor-pointer',
      disabled && 'opacity-50 cursor-not-allowed transform-none hover:scale-100',
      className
    )"
    :role="interactive ? 'button' : undefined"
    :tabindex="interactive ? 0 : undefined"
    :aria-label="`Role: ${roleConfig.label}${roleConfig.description ? `. ${roleConfig.description}` : ''}`"
    @click="interactive && !disabled && $emit('click', role)"
    @keydown="handleKeydown"
  >
    <!-- Role Icon -->
    <div 
      v-if="showIcon && roleConfig.icon" 
      :class="cn(
        'flex-shrink-0 transition-transform duration-200',
        iconSizeClasses,
        interactive && 'group-hover:scale-110'
      )"
    >
      <component 
        :is="roleConfig.icon" 
        :class="cn(
          'drop-shadow-sm',
          iconSizeClasses
        )"
      />
    </div>

    <!-- Role Label -->
    <span 
      :class="cn(
        'font-semibold tracking-wide',
        textSizeClasses
      )"
    >
      {{ roleConfig.label }}
    </span>

    <!-- Permission Count Badge -->
    <div
      v-if="showPermissionCount && permissionCount > 0"
      :class="cn(
        'flex-shrink-0 px-1.5 py-0.5 rounded-full text-[10px] font-bold',
        'bg-white/20 text-current border border-white/30'
      )"
    >
      {{ permissionCount }}
    </div>

    <!-- Premium Role Indicator -->
    <div
      v-if="roleConfig.premium"
      :class="cn(
        'flex-shrink-0 w-2 h-2 rounded-full',
        'bg-gradient-to-r from-yellow-400 to-yellow-600',
        'shadow-sm ring-1 ring-yellow-300/50'
      )"
      title="Premium Role"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Shield, Users, Eye, Wrench, Crown, Settings, UserCheck } from 'lucide-vue-next';
import { cn } from '@/utils/cn';
import { createThemeComponentClasses, themeBadgeVariants } from '@/utils/theme-variants';
import { ROLE_LABELS } from '../types/users.types';
import type { UserRole } from '@/modules/auth/types/auth.types';

interface Props {
  role: UserRole;
  showIcon?: boolean;
  size?: 'sm' | 'md' | 'lg';
  variant?: 'default' | 'outline' | 'gradient' | 'glass';
  interactive?: boolean;
  disabled?: boolean;
  showPermissionCount?: boolean;
  permissionCount?: number;
  className?: string;
}

const props = withDefaults(defineProps<Props>(), {
  showIcon: true,
  size: 'md',
  variant: 'gradient',
  interactive: false,
  disabled: false,
  showPermissionCount: false,
  permissionCount: 0
});

defineEmits<{
  click: [role: UserRole];
}>();

// ==================== COMPUTED ====================
const roleConfig = computed(() => {
  const baseConfigs = {
    admin: {
      icon: Crown,
      label: ROLE_LABELS.admin,
      description: 'Full system access and user management',
      premium: true,
      baseColor: 'red',
      gradientFrom: 'from-red-500',
      gradientTo: 'to-red-700',
      hoverRing: 'hover:ring-red-300'
    },
    project_manager: {
      icon: Users,
      label: ROLE_LABELS.project_manager,
      description: 'Project oversight and team coordination',
      premium: true,
      baseColor: 'blue',
      gradientFrom: 'from-blue-500',
      gradientTo: 'to-blue-700',
      hoverRing: 'hover:ring-blue-300'
    },
    supervisor: {
      icon: Eye,
      label: ROLE_LABELS.supervisor,
      description: 'Site supervision and quality control',
      premium: false,
      baseColor: 'orange',
      gradientFrom: 'from-orange-500',
      gradientTo: 'to-orange-700',
      hoverRing: 'hover:ring-orange-300'
    },
    field_worker: {
      icon: Wrench,
      label: ROLE_LABELS.field_worker,
      description: 'Hands-on construction and maintenance',
      premium: false,
      baseColor: 'green',
      gradientFrom: 'from-green-500',
      gradientTo: 'to-green-700',
      hoverRing: 'hover:ring-green-300'
    }
  };

  const baseConfig = baseConfigs[props.role] || baseConfigs.field_worker;
  
  // Generate variant-specific classes
  const variantClasses = {
    default: `bg-${baseConfig.baseColor}-100 text-${baseConfig.baseColor}-800 border-${baseConfig.baseColor}-300`,
    outline: `bg-transparent text-${baseConfig.baseColor}-700 border-2 border-${baseConfig.baseColor}-400 hover:bg-${baseConfig.baseColor}-50`,
    gradient: `bg-gradient-to-r ${baseConfig.gradientFrom} ${baseConfig.gradientTo} text-white border-transparent shadow-lg`,
    glass: `bg-${baseConfig.baseColor}-500/10 text-${baseConfig.baseColor}-700 border-${baseConfig.baseColor}-300/30 backdrop-blur-md`
  };

  return {
    ...baseConfig,
    classes: cn(
      variantClasses[props.variant],
      baseConfig.hoverRing
    )
  };
});

// Size-based computed classes
const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-2 py-1 text-[10px] gap-1',
    md: 'px-3 py-1.5 text-xs gap-1.5',
    lg: 'px-4 py-2 text-sm gap-2'
  };
  return sizes[props.size];
});

const iconSizeClasses = computed(() => {
  const sizes = {
    sm: 'w-3 h-3',
    md: 'w-3.5 h-3.5',
    lg: 'w-4 h-4'
  };
  return sizes[props.size];
});

const textSizeClasses = computed(() => {
  const sizes = {
    sm: 'text-[10px]',
    md: 'text-xs',
    lg: 'text-sm'
  };
  return sizes[props.size];
});

// ==================== METHODS ====================
const handleKeydown = (event: KeyboardEvent) => {
  if (!props.interactive || props.disabled) return;
  
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault();
    event.currentTarget?.dispatchEvent(new MouseEvent('click'));
  }
};
</script>
<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
        @click="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 transition-opacity" />
        
        <!-- Modal Container -->
        <Transition
          enter-active-class="transition-all duration-200"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            v-if="modelValue"
            :class="modalClass"
            @click.stop
          >
            <!-- Header -->
            <div v-if="showHeader" :class="headerClass">
              <div class="flex items-center justify-between">
                <div>
                  <!-- Title slot or prop -->
                  <slot name="title">
                    <h3 v-if="title" class="text-lg font-semibold text-gray-900">
                      {{ title }}
                    </h3>
                  </slot>
                  <!-- Subtitle slot -->
                  <slot name="subtitle">
                    <p v-if="subtitle" class="mt-1 text-sm text-gray-600">
                      {{ subtitle }}
                    </p>
                  </slot>
                </div>
                
                <!-- Close button -->
                <button
                  v-if="closable"
                  @click="close"
                  class="rounded-md p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>
            
            <!-- Body -->
            <div :class="bodyClass">
              <slot />
            </div>
            
            <!-- Footer -->
            <div v-if="showFooter" :class="footerClass">
              <slot name="footer">
                <div class="flex items-center justify-end gap-3">
                  <VButton
                    v-if="showCancelButton"
                    variant="outline"
                    @click="cancel"
                    :disabled="loading"
                  >
                    {{ cancelText }}
                  </VButton>
                  <VButton
                    v-if="showConfirmButton"
                    :variant="confirmVariant"
                    @click="confirm"
                    :loading="loading"
                  >
                    {{ confirmText }}
                  </VButton>
                </div>
              </slot>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, watch, nextTick } from 'vue';
import { VButton } from '@/components/ui';
import { X } from 'lucide-vue-next';
import { cn } from '@/utils/cn';

interface Props {
  modelValue: boolean;
  title?: string;
  subtitle?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
  variant?: 'default' | 'danger' | 'warning' | 'success';
  closable?: boolean;
  closeOnBackdrop?: boolean;
  closeOnEscape?: boolean;
  
  // Header
  showHeader?: boolean;
  
  // Footer  
  showFooter?: boolean;
  showCancelButton?: boolean;
  showConfirmButton?: boolean;
  cancelText?: string;
  confirmText?: string;
  confirmVariant?: 'default' | 'danger' | 'warning' | 'success';
  
  // State
  loading?: boolean;
  persistent?: boolean;
  
  // Styling
  modalClass?: string;
  headerClass?: string;
  bodyClass?: string;
  footerClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  variant: 'default',
  closable: true,
  closeOnBackdrop: true,
  closeOnEscape: true,
  showHeader: true,
  showFooter: true,
  showCancelButton: true,
  showConfirmButton: true,
  cancelText: 'Cancel',
  confirmText: 'Confirm',
  confirmVariant: 'default'
});

const emit = defineEmits<{
  'update:modelValue': [value: boolean];
  'close': [];
  'cancel': [];
  'confirm': [];
  'open': [];
}>();

// Computed classes
const modalClass = computed(() => {
  const base = 'relative bg-white rounded-lg shadow-xl mx-4 my-8 overflow-hidden';
  
  const sizeClasses = {
    sm: 'max-w-sm w-full',
    md: 'max-w-md w-full', 
    lg: 'max-w-2xl w-full',
    xl: 'max-w-4xl w-full',
    full: 'max-w-none w-full h-full mx-0 my-0 rounded-none'
  };
  
  const variantClasses = {
    default: 'border-t-4 border-orange-500',
    danger: 'border-t-4 border-red-500',
    warning: 'border-t-4 border-yellow-500',
    success: 'border-t-4 border-green-500'
  };
  
  return cn(
    base,
    sizeClasses[props.size],
    variantClasses[props.variant],
    props.modalClass
  );
});

const headerClass = computed(() => {
  const base = 'px-6 py-4 border-b border-gray-200';
  return cn(base, props.headerClass);
});

const bodyClass = computed(() => {
  const base = 'px-6 py-4';
  const sizeClasses = {
    full: 'flex-1 overflow-y-auto'
  };
  
  return cn(
    base,
    props.size === 'full' ? sizeClasses.full : '',
    props.bodyClass
  );
});

const footerClass = computed(() => {
  const base = 'px-6 py-4 border-t border-gray-200 bg-gray-50';
  return cn(base, props.footerClass);
});

// Methods
const close = (): void => {
  if (!props.persistent) {
    emit('update:modelValue', false);
    emit('close');
  }
};

const cancel = (): void => {
  emit('cancel');
  if (!props.persistent) {
    emit('update:modelValue', false);
  }
};

const confirm = (): void => {
  emit('confirm');
  // Let parent component control closing for confirm action
};

const handleBackdropClick = (): void => {
  if (props.closeOnBackdrop && !props.persistent) {
    close();
  }
};

const handleEscapeKey = (event: KeyboardEvent): void => {
  if (event.key === 'Escape' && props.closeOnEscape && !props.persistent) {
    close();
  }
};

// Body scroll lock
const lockBodyScroll = (): void => {
  document.body.style.overflow = 'hidden';
};

const unlockBodyScroll = (): void => {
  document.body.style.overflow = '';
};

// Watchers
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    emit('open');
    nextTick(() => {
      lockBodyScroll();
      if (props.closeOnEscape) {
        document.addEventListener('keydown', handleEscapeKey);
      }
    });
  } else {
    unlockBodyScroll();
    if (props.closeOnEscape) {
      document.removeEventListener('keydown', handleEscapeKey);
    }
  }
});

// Focus management
const focusFirstFocusableElement = (): void => {
  nextTick(() => {
    const modal = document.querySelector('[data-modal="true"]');
    if (modal) {
      const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      const firstElement = focusableElements[0] as HTMLElement;
      if (firstElement) {
        firstElement.focus();
      }
    }
  });
};

// Cleanup on unmount
import { onUnmounted } from 'vue';

onUnmounted(() => {
  unlockBodyScroll();
  if (props.closeOnEscape) {
    document.removeEventListener('keydown', handleEscapeKey);
  }
});
</script>
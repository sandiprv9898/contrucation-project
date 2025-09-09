<template>
  <div class="relative inline-block text-left" ref="dropdownRef">
    <div @click="toggleDropdown">
      <slot name="trigger" />
    </div>
    
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-2 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        :class="[
          align === 'end' ? 'right-0' : 'left-0',
          sizeClasses
        ]"
        role="menu"
        aria-orientation="vertical"
      >
        <div class="py-1" role="none">
          <slot name="content" />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

interface Props {
  align?: 'start' | 'end';
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
  align: 'end',
  size: 'md'
});

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'w-36',
    md: 'w-48',
    lg: 'w-56'
  };
  return sizes[props.size];
});

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
  isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

const handleEscape = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleEscape);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscape);
});

defineExpose({
  closeDropdown
});
</script>
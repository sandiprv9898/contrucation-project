/**
 * Construction Management Platform UI Components Library
 * 
 * Centralized export of all UI components following the project standards:
 * - TypeScript strict typing
 * - Tailwind CSS utilities only
 * - Construction industry design system
 * - Composition API pattern
 * - Field-friendly design (mobile-first, high contrast)
 * - Zero duplication through reusable components
 */

// Core Form Components
export { default as VButton } from './VButton.vue';
export { default as VInput } from './VInput.vue';
export { default as VCheckbox } from './VCheckbox.vue';
export { default as VLabel } from './VLabel.vue';
export { default as VSelect } from './VSelect.vue';
export { default as VTextarea } from './VTextarea.vue';
export { default as VFormField } from './VFormField.vue';

// Layout Components
export { default as VCard } from './VCard.vue';
export { default as VAlert } from './VAlert.vue';
export { default as VBadge } from './VBadge.vue';
export { default as VModal } from './VModal.vue';

// Menu Components
export { default as VDropdownMenu } from './VDropdownMenu.vue';
export { default as VDropdownMenuItem } from './VDropdownMenuItem.vue';

// Data Components
export { default as VDataTable } from './VDataTable.vue';
export { default as VFilterBar } from './VFilterBar.vue';

// Utility exports
export { cn } from '@/utils/cn';
export { 
  buttonVariants, 
  buttonSizes,
  alertVariants,
  badgeVariants,
  inputVariants,
  type ButtonVariant,
  type ButtonSize,
  type AlertVariant,
  type BadgeVariant,
  type InputVariant
} from '@/utils/variants';
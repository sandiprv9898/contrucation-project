# CONST-003: Build Core UI Components Library (/components/ui/)

**Task Type**: Component Development  
**Estimated Time**: 4 hours  
**Priority**: High  
**Status**: In Progress  
**Branch**: `feature/CONST-003-ui-components-library`  
**Depends On**: CONST-002

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified (N/A - UI components)
- [x] Database changes planned (N/A)
- [x] UI components identified
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Create a centralized UI components library following the Construction Management Platform design system and UI/UX standards from Docs/UIUX/ui-ux-foundation.md.

### Functional Requirements
- Centralized UI components in `/components/ui/` directory
- TypeScript interfaces for all component props and emits
- Tailwind CSS utilities only (NO inline styles or custom CSS)
- Construction industry design system compliance
- Composition API pattern throughout
- Field-friendly design (large touch targets, high contrast)
- Form components with validation states
- Consistent height standards (h-8 for form elements)

### UI Components to Build

#### Core Form Components
- **VInput**: Text inputs with validation states and construction theming
- **VButton**: Primary, secondary, ghost, destructive button variants
- **VCheckbox**: Form checkboxes with proper touch targets
- **VSelect**: Dropdown selects with search and multi-select capabilities
- **VLabel**: Form field labels with required indicators

#### Layout & Content Components  
- **VCard**: Content containers with header, content, footer slots
- **VAlert**: Success, warning, error, info message display
- **VBadge**: Status indicators and priority badges
- **VSkeleton**: Loading placeholder components
- **VProgress**: Progress bars for project/task completion

#### Utility Components
- **VAvatar**: User/contractor profile images
- **VDialog**: Modal dialogs and confirmations
- **VDropdown**: Dropdown menus with proper positioning
- **VTabs**: Tab navigation for content sections
- **VSwitch**: Toggle switches for settings

### Design System Compliance

#### Color Usage (from tailwind.config.js)
- **Primary**: `#FF6B35` (Construction Orange)
- **Secondary**: `#4A5568` (Steel Blue)
- **Success**: `#38A169` (Safety Green)
- **Warning**: `#F6AD55` (Warning Yellow)
- **Destructive**: `#E53E3E` (Danger Red)

#### Component Standards
- **Height Consistency**: All form elements use `h-8` (32px)
- **Touch Targets**: Minimum 44px for mobile interaction
- **Focus States**: Clear focus indicators for accessibility
- **Loading States**: Skeleton loaders and loading indicators
- **Error States**: Clear error messaging and styling

#### Typography Scale
- **Headers**: `text-3xl`, `text-2xl`, `text-xl`, `text-lg`
- **Body**: `text-base`, `text-sm`, `text-xs`
- **Weights**: `font-normal`, `font-medium`, `font-semibold`, `font-bold`

### Technical Specifications

#### Component Architecture
```typescript
// Base component interface
interface BaseComponent {
  class?: string;
  disabled?: boolean;
  loading?: boolean;
}

// Form component interface
interface FormComponent extends BaseComponent {
  modelValue?: any;
  name?: string;
  placeholder?: string;
  required?: boolean;
  error?: string;
}
```

#### File Structure
```
src/components/ui/
├── VInput.vue              # Text input component
├── VButton.vue             # Button variations
├── VCheckbox.vue           # Checkbox with label
├── VSelect.vue             # Dropdown select
├── VLabel.vue              # Form labels
├── VCard.vue               # Content containers
├── VAlert.vue              # Alert messages
├── VBadge.vue              # Status badges
├── VSkeleton.vue           # Loading placeholders
├── VProgress.vue           # Progress indicators
├── VAvatar.vue             # User avatars
├── VDialog.vue             # Modal dialogs
├── VDropdown.vue           # Dropdown menus
├── VTabs.vue               # Tab navigation
├── VSwitch.vue             # Toggle switches
└── index.ts                # Export all components
```

#### Component Pattern Example
```vue
<template>
  <input
    :class="cn(
      'h-8 px-3 py-2 border border-border rounded-md',
      'focus:outline-none focus:ring-2 focus:ring-primary',
      'disabled:opacity-50 disabled:cursor-not-allowed',
      error && 'border-destructive focus:ring-destructive',
      className
    )"
    :disabled="disabled"
    :placeholder="placeholder"
    :value="modelValue"
    @input="emit('update:modelValue', $event.target.value)"
  />
</template>

<script setup lang="ts">
interface Props {
  modelValue?: string;
  placeholder?: string;
  disabled?: boolean;
  error?: string;
  className?: string;
}

interface Emits {
  'update:modelValue': [value: string];
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  disabled: false
});

const emit = defineEmits<Emits>();

// cn utility function for conditional classes
import { cn } from '@/utils/cn';
</script>
```

### Test Scenarios

#### Component Functionality Tests
1. **VInput Component**
   - Text input and v-model binding works
   - Placeholder text displays correctly
   - Disabled state prevents interaction
   - Error state shows proper styling
   - Focus states work correctly

2. **VButton Component**
   - All variants render correctly (primary, secondary, ghost)
   - Loading state shows spinner
   - Disabled state prevents clicks
   - Size variants work (sm, md, lg)
   - Icons display properly with text

3. **VCard Component**
   - Header, content, footer slots work
   - Proper padding and spacing
   - Border and shadow styling
   - Responsive behavior

#### Accessibility Tests
- Keyboard navigation works
- Screen reader compatibility
- Focus indicators visible
- Color contrast meets WCAG standards
- Touch targets meet minimum size (44px)

#### Responsive Design Tests
- Components work on mobile (320px+)
- Tablet breakpoint (768px+)
- Desktop breakpoint (1024px+)
- Touch interaction on mobile devices

### Performance Impact Assessment
- **Bundle Size**: ~15-20KB additional for component library
- **Tree Shaking**: Components can be imported individually
- **Runtime Performance**: Minimal impact, components are lightweight
- **Development Experience**: Improved consistency and faster development

### Utility Functions Required
```typescript
// utils/cn.ts - Class name utility
export function cn(...classes: (string | undefined | false)[]): string {
  return classes.filter(Boolean).join(' ');
}

// utils/variants.ts - Component variant utilities
export const buttonVariants = {
  primary: 'bg-primary text-white hover:bg-primary-600',
  secondary: 'bg-secondary text-white hover:bg-secondary-600',
  ghost: 'bg-transparent hover:bg-muted',
  destructive: 'bg-destructive text-white hover:bg-destructive/90'
};
```

## Implementation Plan

### Phase 1: Utility Functions & Base Components (1 hour)
1. Create `utils/cn.ts` class name utility
2. Create `utils/variants.ts` for component variants
3. Implement VButton with all variants
4. Implement VInput with validation states

### Phase 2: Form Components (1.5 hours)
1. Implement VCheckbox with proper touch targets
2. Implement VSelect with dropdown functionality
3. Implement VLabel with required indicators
4. Create form validation utilities

### Phase 3: Layout Components (1 hour)
1. Implement VCard with slot system
2. Implement VAlert with all severity levels
3. Implement VBadge for status indicators
4. Create VSkeleton loading components

### Phase 4: Advanced Components (30 minutes)
1. Implement VProgress bars
2. Implement VAvatar with fallbacks
3. Create component export index
4. Test all components in isolation

## Acceptance Criteria
- [ ] All components use TypeScript with strict typing
- [ ] No inline styles or custom CSS classes used
- [ ] All components follow Composition API pattern
- [ ] Construction industry color scheme applied
- [ ] Form elements consistent h-8 height
- [ ] Touch targets minimum 44px on mobile
- [ ] Components exported from centralized index
- [ ] Error states and loading states implemented
- [ ] Accessibility standards met (keyboard nav, screen reader)
- [ ] All components tested and working

## Dependencies
- **Depends On**: CONST-002 (Vue.js project setup)
- **Utility Functions**: cn() class utility, variant utilities

## Next Tasks
- **CONST-004**: Authentication layout implementation using these components

## Risks & Mitigation
- **Risk**: Components not meeting accessibility standards
  - **Mitigation**: Test with keyboard navigation and screen readers
- **Risk**: Inconsistent styling across components
  - **Mitigation**: Use consistent Tailwind utility patterns
- **Risk**: Performance impact from large component library
  - **Mitigation**: Enable tree-shaking and lazy loading

---
**Created**: 2024  
**Assignee**: Development Team  
**Branch**: `feature/CONST-003-ui-components-library`
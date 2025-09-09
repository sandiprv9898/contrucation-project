// Use the existing cn utility instead of importing clsx directly
import { cn } from '@/utils/cn';

/**
 * Theme-aware button variants that use CSS custom properties
 * These variants adapt to the active theme automatically
 */
export const themeButtonVariants = {
  variant: {
    default: cn(
      'bg-[var(--color-primary-500)] text-white border border-transparent',
      'hover:bg-[var(--color-primary-600)] focus:ring-[var(--color-primary-500)]/20',
      'disabled:bg-[var(--color-gray-300)] disabled:text-[var(--color-gray-500)]',
      'dark:bg-[var(--color-primary-600)] dark:hover:bg-[var(--color-primary-700)]'
    ),
    destructive: cn(
      'bg-[var(--color-danger-500)] text-white border border-transparent',
      'hover:bg-[var(--color-danger-600)] focus:ring-[var(--color-danger-500)]/20',
      'disabled:bg-[var(--color-gray-300)] disabled:text-[var(--color-gray-500)]'
    ),
    outline: cn(
      'border border-[var(--color-primary-500)] text-[var(--color-primary-600)] bg-transparent',
      'hover:bg-[var(--color-primary-50)] focus:ring-[var(--color-primary-500)]/20',
      'disabled:border-[var(--color-gray-300)] disabled:text-[var(--color-gray-400)]',
      'dark:border-[var(--color-primary-400)] dark:text-[var(--color-primary-400)]',
      'dark:hover:bg-[var(--color-primary-950)]'
    ),
    secondary: cn(
      'bg-[var(--color-secondary-100)] text-[var(--color-secondary-900)] border border-transparent',
      'hover:bg-[var(--color-secondary-200)] focus:ring-[var(--color-secondary-500)]/20',
      'disabled:bg-[var(--color-gray-100)] disabled:text-[var(--color-gray-400)]',
      'dark:bg-[var(--color-secondary-800)] dark:text-[var(--color-secondary-100)]',
      'dark:hover:bg-[var(--color-secondary-700)]'
    ),
    ghost: cn(
      'text-[var(--color-primary-600)] bg-transparent border border-transparent',
      'hover:bg-[var(--color-primary-50)] focus:ring-[var(--color-primary-500)]/20',
      'disabled:text-[var(--color-gray-400)]',
      'dark:text-[var(--color-primary-400)] dark:hover:bg-[var(--color-primary-950)]'
    ),
    link: cn(
      'text-[var(--color-primary-600)] underline-offset-4 hover:underline bg-transparent border-none p-0 h-auto',
      'focus:ring-0 focus:ring-offset-0 focus:outline-none focus:underline',
      'disabled:text-[var(--color-gray-400)] disabled:no-underline',
      'dark:text-[var(--color-primary-400)]'
    )
  },
  size: {
    sm: 'h-8 px-3 text-xs rounded-[var(--radius-sm)]',
    md: 'h-10 px-4 text-sm rounded-[var(--radius-md)]',
    lg: 'h-12 px-6 text-base rounded-[var(--radius-lg)]',
    icon: 'h-10 w-10 rounded-[var(--radius-md)]'
  }
} as const;

/**
 * Theme-aware input variants
 */
export const themeInputVariants = {
  variant: {
    default: cn(
      'border border-[var(--color-gray-300)] bg-[var(--color-gray-50)]',
      'text-[var(--color-gray-900)] placeholder:text-[var(--color-gray-400)]',
      'focus:border-[var(--color-primary-500)] focus:ring-[var(--color-primary-500)]/20',
      'disabled:bg-[var(--color-gray-100)] disabled:text-[var(--color-gray-500)]',
      'dark:border-[var(--color-gray-600)] dark:bg-[var(--color-gray-800)]',
      'dark:text-[var(--color-gray-100)] dark:placeholder:text-[var(--color-gray-500)]'
    ),
    error: cn(
      'border border-[var(--color-danger-300)] bg-[var(--color-danger-50)]',
      'text-[var(--color-gray-900)] placeholder:text-[var(--color-gray-400)]',
      'focus:border-[var(--color-danger-500)] focus:ring-[var(--color-danger-500)]/20'
    ),
    success: cn(
      'border border-[var(--color-success-300)] bg-[var(--color-success-50)]',
      'text-[var(--color-gray-900)] placeholder:text-[var(--color-gray-400)]',
      'focus:border-[var(--color-success-500)] focus:ring-[var(--color-success-500)]/20'
    )
  },
  size: {
    sm: 'h-8 px-3 text-xs rounded-[var(--radius-sm)]',
    md: 'h-10 px-3 text-sm rounded-[var(--radius-md)]',
    lg: 'h-12 px-4 text-base rounded-[var(--radius-lg)]'
  }
} as const;

/**
 * Theme-aware alert variants
 */
export const themeAlertVariants = {
  variant: {
    default: cn(
      'bg-[var(--color-primary-50)] text-[var(--color-primary-900)]',
      'border border-[var(--color-primary-200)]',
      '[&>svg]:text-[var(--color-primary-600)]'
    ),
    destructive: cn(
      'bg-[var(--color-danger-50)] text-[var(--color-danger-900)]',
      'border border-[var(--color-danger-200)]',
      '[&>svg]:text-[var(--color-danger-600)]'
    ),
    warning: cn(
      'bg-[var(--color-warning-50)] text-[var(--color-warning-900)]',
      'border border-[var(--color-warning-200)]',
      '[&>svg]:text-[var(--color-warning-600)]'
    ),
    success: cn(
      'bg-[var(--color-success-50)] text-[var(--color-success-900)]',
      'border border-[var(--color-success-200)]',
      '[&>svg]:text-[var(--color-success-600)]'
    ),
    info: cn(
      'bg-[var(--color-secondary-50)] text-[var(--color-secondary-900)]',
      'border border-[var(--color-secondary-200)]',
      '[&>svg]:text-[var(--color-secondary-600)]'
    )
  }
} as const;

/**
 * Theme-aware badge variants
 */
export const themeBadgeVariants = {
  variant: {
    default: cn(
      'bg-[var(--color-primary-100)] text-[var(--color-primary-800)]',
      'border border-[var(--color-primary-200)]'
    ),
    secondary: cn(
      'bg-[var(--color-secondary-100)] text-[var(--color-secondary-800)]',
      'border border-[var(--color-secondary-200)]'
    ),
    destructive: cn(
      'bg-[var(--color-danger-100)] text-[var(--color-danger-800)]',
      'border border-[var(--color-danger-200)]'
    ),
    success: cn(
      'bg-[var(--color-success-100)] text-[var(--color-success-800)]',
      'border border-[var(--color-success-200)]'
    ),
    warning: cn(
      'bg-[var(--color-warning-100)] text-[var(--color-warning-800)]',
      'border border-[var(--color-warning-200)]'
    ),
    outline: cn(
      'text-[var(--color-primary-600)] border border-[var(--color-primary-300)] bg-transparent',
      'dark:text-[var(--color-primary-400)] dark:border-[var(--color-primary-600)]'
    )
  },
  size: {
    sm: 'px-2 py-0.5 text-xs rounded-[var(--radius-sm)]',
    md: 'px-3 py-1 text-sm rounded-[var(--radius-md)]',
    lg: 'px-4 py-1.5 text-base rounded-[var(--radius-lg)]'
  }
} as const;

/**
 * Theme-aware card variants
 */
export const themeCardVariants = {
  variant: {
    default: cn(
      'bg-white border border-[var(--color-gray-200)] shadow-[var(--shadow-sm)]',
      'dark:bg-[var(--color-gray-800)] dark:border-[var(--color-gray-700)]'
    ),
    elevated: cn(
      'bg-white border border-[var(--color-gray-200)] shadow-[var(--shadow-md)]',
      'dark:bg-[var(--color-gray-800)] dark:border-[var(--color-gray-700)]'
    ),
    outlined: cn(
      'bg-transparent border-2 border-[var(--color-primary-200)]',
      'dark:border-[var(--color-primary-700)]'
    ),
    ghost: cn(
      'bg-[var(--color-primary-50)]/50 border border-[var(--color-primary-100)]',
      'dark:bg-[var(--color-primary-950)]/50 dark:border-[var(--color-primary-900)]'
    )
  },
  size: {
    sm: 'p-4 rounded-[var(--radius-md)]',
    md: 'p-6 rounded-[var(--radius-lg)]',
    lg: 'p-8 rounded-[var(--radius-xl)]'
  }
} as const;

/**
 * Utility functions for working with theme variants
 */
export function getThemeVariant<T extends Record<string, Record<string, string>>>(
  variants: T,
  category: keyof T,
  value: keyof T[keyof T]
): string {
  return variants[category]?.[value] || '';
}

/**
 * Generate component classes using theme variants
 */
export function createThemeComponentClasses<
  T extends Record<string, Record<string, string>>
>(
  variants: T,
  props: Partial<{ [K in keyof T]: keyof T[K] }>,
  baseClasses: string = '',
  additionalClasses: string = ''
): string {
  const classes = [baseClasses];
  
  Object.entries(props).forEach(([category, value]) => {
    if (value && variants[category] && variants[category][value as string]) {
      classes.push(variants[category][value as string]);
    }
  });
  
  if (additionalClasses) {
    classes.push(additionalClasses);
  }
  
  return cn(...classes);
}

/**
 * Theme-aware focus ring utility
 */
export const themeFocusRing = cn(
  'focus:outline-none focus:ring-2 focus:ring-[var(--color-primary-500)]/20 focus:ring-offset-2',
  'dark:focus:ring-offset-[var(--color-gray-800)]'
);

/**
 * Theme-aware transition classes
 */
export const themeTransitions = {
  colors: 'transition-colors duration-[var(--duration-fast)] ease-[var(--easing-ease-out)]',
  all: 'transition-all duration-[var(--duration-medium)] ease-[var(--easing-ease-in-out)]',
  transform: 'transition-transform duration-[var(--duration-fast)] ease-[var(--easing-ease-out)]',
  opacity: 'transition-opacity duration-[var(--duration-fast)] ease-[var(--easing-ease-out)]'
} as const;

/**
 * Generate CSS custom property based classes
 */
export function themeColorClass(colorPath: string, property: 'bg' | 'text' | 'border' = 'bg'): string {
  return `${property}-[var(--color-${colorPath.replace('.', '-')})]`;
}

export function themeSpacingClass(size: string, property: 'p' | 'm' | 'gap' = 'p'): string {
  return `${property}-[var(--spacing-${size})]`;
}

export function themeBorderRadiusClass(size: string): string {
  return `rounded-[var(--radius-${size})]`;
}

export function themeShadowClass(size: string): string {
  return `shadow-[var(--shadow-${size})]`;
}

/**
 * Responsive theme utilities
 */
export function responsiveThemeClasses(
  baseClasses: string,
  breakpoints?: {
    sm?: string;
    md?: string;
    lg?: string;
    xl?: string;
  }
): string {
  const classes = [baseClasses];
  
  if (breakpoints) {
    Object.entries(breakpoints).forEach(([breakpoint, value]) => {
      if (value) {
        classes.push(`${breakpoint}:${value}`);
      }
    });
  }
  
  return cn(...classes);
}

// Export all variant types for TypeScript
export type ThemeButtonVariant = keyof typeof themeButtonVariants.variant;
export type ThemeButtonSize = keyof typeof themeButtonVariants.size;
export type ThemeInputVariant = keyof typeof themeInputVariants.variant;
export type ThemeInputSize = keyof typeof themeInputVariants.size;
export type ThemeAlertVariant = keyof typeof themeAlertVariants.variant;
export type ThemeBadgeVariant = keyof typeof themeBadgeVariants.variant;
export type ThemeBadgeSize = keyof typeof themeBadgeVariants.size;
export type ThemeCardVariant = keyof typeof themeCardVariants.variant;
export type ThemeCardSize = keyof typeof themeCardVariants.size;
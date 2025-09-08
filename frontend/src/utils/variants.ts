/**
 * Component variant utilities for Construction Management Platform
 * Based on the construction industry color scheme
 */

export const buttonVariants = {
  primary: 'bg-primary text-white hover:bg-primary-600 focus:ring-primary disabled:opacity-50',
  secondary: 'bg-secondary text-white hover:bg-secondary-600 focus:ring-secondary disabled:opacity-50',
  ghost: 'bg-transparent text-foreground hover:bg-muted border border-border disabled:opacity-50',
  destructive: 'bg-destructive text-white hover:bg-destructive/90 focus:ring-destructive disabled:opacity-50',
  outline: 'bg-transparent text-primary border border-primary hover:bg-primary hover:text-white disabled:opacity-50'
} as const;

export const buttonSizes = {
  sm: 'h-8 px-3 py-1 text-sm',
  md: 'h-10 px-4 py-2 text-base',
  lg: 'h-12 px-6 py-3 text-lg',
  icon: 'h-8 w-8 p-0'
} as const;

export const alertVariants = {
  default: 'bg-muted border border-border text-foreground',
  success: 'bg-green-50 border border-green-200 text-green-800',
  warning: 'bg-yellow-50 border border-yellow-200 text-yellow-800', 
  error: 'bg-red-50 border border-red-200 text-red-800',
  info: 'bg-blue-50 border border-blue-200 text-blue-800'
} as const;

export const badgeVariants = {
  default: 'bg-secondary text-white',
  success: 'bg-success text-white',
  warning: 'bg-warning text-white',
  destructive: 'bg-destructive text-white',
  outline: 'bg-transparent text-foreground border border-border',
  // Construction-specific status badges
  planning: 'bg-status-planning text-white',
  active: 'bg-status-active text-white', 
  'on-hold': 'bg-status-on-hold text-white',
  completed: 'bg-status-completed text-white',
  delayed: 'bg-status-delayed text-white',
  // Priority badges
  'priority-critical': 'bg-priority-critical text-white',
  'priority-high': 'bg-priority-high text-white',
  'priority-medium': 'bg-priority-medium text-white',
  'priority-low': 'bg-priority-low text-white'
} as const;

export const inputVariants = {
  default: 'border-border focus:border-primary focus:ring-primary',
  error: 'border-destructive focus:border-destructive focus:ring-destructive',
  success: 'border-success focus:border-success focus:ring-success'
} as const;

export type ButtonVariant = keyof typeof buttonVariants;
export type ButtonSize = keyof typeof buttonSizes;
export type AlertVariant = keyof typeof alertVariants;
export type BadgeVariant = keyof typeof badgeVariants;
export type InputVariant = keyof typeof inputVariants;
/**
 * Class name utility function for conditional class merging
 * Filters out falsy values and joins remaining classes
 */
export function cn(...classes: (string | undefined | false | null)[]): string {
  return classes.filter(Boolean).join(' ');
}

/**
 * Merge classes with proper precedence
 * Later classes override earlier ones
 */
export function mergeClasses(baseClasses: string, additionalClasses?: string): string {
  if (!additionalClasses) return baseClasses;
  return cn(baseClasses, additionalClasses);
}
import { ref, reactive, computed, watch } from 'vue';

// Theme types
export interface ThemeColors {
  primary: {
    50: string;
    100: string;
    200: string;
    300: string;
    400: string;
    500: string;
    600: string;
    700: string;
    800: string;
    900: string;
    950: string;
  };
  secondary: {
    50: string;
    100: string;
    200: string;
    300: string;
    400: string;
    500: string;
    600: string;
    700: string;
    800: string;
    900: string;
    950: string;
  };
  success: {
    50: string;
    100: string;
    500: string;
    600: string;
    700: string;
    900: string;
  };
  warning: {
    50: string;
    100: string;
    500: string;
    600: string;
    700: string;
    900: string;
  };
  danger: {
    50: string;
    100: string;
    500: string;
    600: string;
    700: string;
    900: string;
  };
  gray: {
    50: string;
    100: string;
    200: string;
    300: string;
    400: string;
    500: string;
    600: string;
    700: string;
    800: string;
    900: string;
    950: string;
  };
}

export interface ThemeConfig {
  name: string;
  colors: ThemeColors;
  spacing: {
    xs: string;
    sm: string;
    md: string;
    lg: string;
    xl: string;
  };
  borderRadius: {
    none: string;
    sm: string;
    md: string;
    lg: string;
    xl: string;
    full: string;
  };
  shadows: {
    sm: string;
    md: string;
    lg: string;
    xl: string;
  };
  typography: {
    fontFamily: {
      sans: string[];
      serif: string[];
      mono: string[];
    };
    fontSize: {
      xs: [string, { lineHeight: string }];
      sm: [string, { lineHeight: string }];
      base: [string, { lineHeight: string }];
      lg: [string, { lineHeight: string }];
      xl: [string, { lineHeight: string }];
      '2xl': [string, { lineHeight: string }];
      '3xl': [string, { lineHeight: string }];
    };
  };
  animation: {
    duration: {
      fast: string;
      medium: string;
      slow: string;
    };
    easing: {
      ease: string;
      easeIn: string;
      easeOut: string;
      easeInOut: string;
    };
  };
}

// Default Construction Theme (Orange-based)
const constructionTheme: ThemeConfig = {
  name: 'construction',
  colors: {
    primary: {
      50: '#fff7ed',
      100: '#ffedd5',
      200: '#fed7aa',
      300: '#fdba74',
      400: '#fb923c',
      500: '#f97316', // Main orange
      600: '#ea580c',
      700: '#c2410c',
      800: '#9a3412',
      900: '#7c2d12',
      950: '#431407'
    },
    secondary: {
      50: '#f8fafc',
      100: '#f1f5f9',
      200: '#e2e8f0',
      300: '#cbd5e1',
      400: '#94a3b8',
      500: '#64748b',
      600: '#475569',
      700: '#334155',
      800: '#1e293b',
      900: '#0f172a',
      950: '#020617'
    },
    success: {
      50: '#f0fdf4',
      100: '#dcfce7',
      500: '#22c55e',
      600: '#16a34a',
      700: '#15803d',
      900: '#14532d'
    },
    warning: {
      50: '#fefce8',
      100: '#fef3c7',
      500: '#eab308',
      600: '#ca8a04',
      700: '#a16207',
      900: '#713f12'
    },
    danger: {
      50: '#fef2f2',
      100: '#fee2e2',
      500: '#ef4444',
      600: '#dc2626',
      700: '#b91c1c',
      900: '#7f1d1d'
    },
    gray: {
      50: '#f9fafb',
      100: '#f3f4f6',
      200: '#e5e7eb',
      300: '#d1d5db',
      400: '#9ca3af',
      500: '#6b7280',
      600: '#4b5563',
      700: '#374151',
      800: '#1f2937',
      900: '#111827',
      950: '#030712'
    }
  },
  spacing: {
    xs: '0.5rem',
    sm: '0.75rem',
    md: '1rem',
    lg: '1.5rem',
    xl: '2rem'
  },
  borderRadius: {
    none: '0',
    sm: '0.125rem',
    md: '0.375rem',
    lg: '0.5rem',
    xl: '0.75rem',
    full: '9999px'
  },
  shadows: {
    sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
    md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
    lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
    xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)'
  },
  typography: {
    fontFamily: {
      sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      serif: ['ui-serif', 'Georgia', 'Cambria', 'Times New Roman', 'Times', 'serif'],
      mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'Liberation Mono', 'Courier New', 'monospace']
    },
    fontSize: {
      xs: ['0.75rem', { lineHeight: '1rem' }],
      sm: ['0.875rem', { lineHeight: '1.25rem' }],
      base: ['1rem', { lineHeight: '1.5rem' }],
      lg: ['1.125rem', { lineHeight: '1.75rem' }],
      xl: ['1.25rem', { lineHeight: '1.75rem' }],
      '2xl': ['1.5rem', { lineHeight: '2rem' }],
      '3xl': ['1.875rem', { lineHeight: '2.25rem' }]
    }
  },
  animation: {
    duration: {
      fast: '150ms',
      medium: '300ms',
      slow: '500ms'
    },
    easing: {
      ease: 'ease',
      easeIn: 'ease-in',
      easeOut: 'ease-out',
      easeInOut: 'ease-in-out'
    }
  }
};

// Corporate Theme (Blue-based)
const corporateTheme: ThemeConfig = {
  ...constructionTheme,
  name: 'corporate',
  colors: {
    ...constructionTheme.colors,
    primary: {
      50: '#eff6ff',
      100: '#dbeafe',
      200: '#bfdbfe',
      300: '#93c5fd',
      400: '#60a5fa',
      500: '#3b82f6',
      600: '#2563eb',
      700: '#1d4ed8',
      800: '#1e40af',
      900: '#1e3a8a',
      950: '#172554'
    }
  }
};

// High Contrast Theme (Accessibility focused)
const highContrastTheme: ThemeConfig = {
  ...constructionTheme,
  name: 'high-contrast',
  colors: {
    ...constructionTheme.colors,
    primary: {
      50: '#ffffff',
      100: '#f0f0f0',
      200: '#e0e0e0',
      300: '#c0c0c0',
      400: '#a0a0a0',
      500: '#808080',
      600: '#606060',
      700: '#404040',
      800: '#202020',
      900: '#000000',
      950: '#000000'
    },
    gray: {
      50: '#ffffff',
      100: '#f5f5f5',
      200: '#e5e5e5',
      300: '#d4d4d4',
      400: '#a3a3a3',
      500: '#737373',
      600: '#525252',
      700: '#404040',
      800: '#262626',
      900: '#171717',
      950: '#0a0a0a'
    }
  }
};

// Dark Theme
const darkTheme: ThemeConfig = {
  ...constructionTheme,
  name: 'dark',
  colors: {
    ...constructionTheme.colors,
    primary: {
      50: '#1a1a1a',
      100: '#2a2a2a',
      200: '#3a3a3a',
      300: '#4a4a4a',
      400: '#5a5a5a',
      500: '#f97316', // Keep orange primary
      600: '#ea580c',
      700: '#c2410c',
      800: '#9a3412',
      900: '#7c2d12',
      950: '#431407'
    },
    gray: {
      50: '#1a1a1a',
      100: '#2a2a2a',
      200: '#3a3a3a',
      300: '#4a4a4a',
      400: '#6a6a6a',
      500: '#8a8a8a',
      600: '#aaaaaa',
      700: '#cccccc',
      800: '#e0e0e0',
      900: '#f0f0f0',
      950: '#ffffff'
    }
  }
};

// Available themes
const themes: Record<string, ThemeConfig> = {
  construction: constructionTheme,
  corporate: corporateTheme,
  'high-contrast': highContrastTheme,
  dark: darkTheme
};

// Theme state
const currentTheme = ref<string>('construction');
const customTheme = reactive<Partial<ThemeConfig>>({});
const isDarkMode = ref(false);

// Composable
export function useTheme() {
  // Computed active theme
  const activeTheme = computed(() => {
    const base = themes[currentTheme.value] || themes.construction;
    return { ...base, ...customTheme };
  });

  // Theme management methods
  const setTheme = (themeName: string) => {
    if (themes[themeName]) {
      currentTheme.value = themeName;
      applyThemeToDocument();
      saveThemePreference();
    }
  };

  const getTheme = () => activeTheme.value;

  const getAvailableThemes = () => Object.keys(themes);

  const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    if (isDarkMode.value) {
      setTheme('dark');
    } else {
      setTheme('construction');
    }
  };

  // Custom theme methods
  const updateThemeColor = (colorPath: string, value: string) => {
    const paths = colorPath.split('.');
    if (paths.length === 3) {
      const [category, color, shade] = paths;
      if (!customTheme.colors) customTheme.colors = {} as ThemeColors;
      if (!customTheme.colors[category as keyof ThemeColors]) {
        customTheme.colors[category as keyof ThemeColors] = {} as any;
      }
      (customTheme.colors[category as keyof ThemeColors] as any)[shade] = value;
      applyThemeToDocument();
    }
  };

  const resetCustomTheme = () => {
    Object.keys(customTheme).forEach(key => delete (customTheme as any)[key]);
    applyThemeToDocument();
  };

  // CSS Variables generation
  const generateCSSVariables = (theme: ThemeConfig): Record<string, string> => {
    const variables: Record<string, string> = {};

    // Colors
    Object.entries(theme.colors).forEach(([category, shades]) => {
      Object.entries(shades).forEach(([shade, value]) => {
        variables[`--color-${category}-${shade}`] = value as string;
      });
    });

    // Spacing
    Object.entries(theme.spacing).forEach(([size, value]) => {
      variables[`--spacing-${size}`] = value;
    });

    // Border Radius
    Object.entries(theme.borderRadius).forEach(([size, value]) => {
      variables[`--radius-${size}`] = value;
    });

    // Shadows
    Object.entries(theme.shadows).forEach(([size, value]) => {
      variables[`--shadow-${size}`] = value;
    });

    // Typography
    Object.entries(theme.typography.fontSize).forEach(([size, [fontSize, { lineHeight }]]) => {
      variables[`--text-${size}`] = fontSize;
      variables[`--leading-${size}`] = lineHeight;
    });

    // Animation
    Object.entries(theme.animation.duration).forEach(([speed, value]) => {
      variables[`--duration-${speed}`] = value;
    });

    Object.entries(theme.animation.easing).forEach(([type, value]) => {
      variables[`--easing-${type}`] = value;
    });

    return variables;
  };

  // Apply theme to document
  const applyThemeToDocument = () => {
    const theme = activeTheme.value;
    const variables = generateCSSVariables(theme);
    const root = document.documentElement;

    Object.entries(variables).forEach(([property, value]) => {
      root.style.setProperty(property, value);
    });

    // Add theme class to body
    document.body.className = document.body.className
      .split(' ')
      .filter(cls => !cls.startsWith('theme-'))
      .concat(`theme-${theme.name}`)
      .join(' ');
  };

  // Utility functions
  const getColorValue = (colorPath: string): string => {
    const theme = activeTheme.value;
    const paths = colorPath.split('.');
    let value: any = theme.colors;

    for (const path of paths) {
      value = value?.[path];
    }

    return value || '';
  };

  const getSpacingValue = (size: keyof ThemeConfig['spacing']): string => {
    return activeTheme.value.spacing[size] || '0';
  };

  const getBorderRadiusValue = (size: keyof ThemeConfig['borderRadius']): string => {
    return activeTheme.value.borderRadius[size] || '0';
  };

  const getShadowValue = (size: keyof ThemeConfig['shadows']): string => {
    return activeTheme.value.shadows[size] || 'none';
  };

  // Persistence
  const saveThemePreference = () => {
    try {
      localStorage.setItem('theme-preference', JSON.stringify({
        theme: currentTheme.value,
        customTheme: customTheme,
        isDarkMode: isDarkMode.value
      }));
    } catch (error) {
      console.warn('Failed to save theme preference:', error);
    }
  };

  const loadThemePreference = () => {
    try {
      const saved = localStorage.getItem('theme-preference');
      if (saved) {
        const preference = JSON.parse(saved);
        currentTheme.value = preference.theme || 'construction';
        Object.assign(customTheme, preference.customTheme || {});
        isDarkMode.value = preference.isDarkMode || false;
        applyThemeToDocument();
      }
    } catch (error) {
      console.warn('Failed to load theme preference:', error);
      setTheme('construction'); // Fallback
    }
  };

  // System preference detection
  const detectSystemPreference = () => {
    if (typeof window !== 'undefined') {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      isDarkMode.value = mediaQuery.matches;
      
      mediaQuery.addEventListener('change', (e) => {
        if (!localStorage.getItem('theme-preference')) {
          isDarkMode.value = e.matches;
          setTheme(e.matches ? 'dark' : 'construction');
        }
      });
    }
  };

  // Watch for changes
  watch(currentTheme, () => {
    applyThemeToDocument();
    saveThemePreference();
  }, { immediate: true });

  watch(customTheme, () => {
    applyThemeToDocument();
    saveThemePreference();
  }, { deep: true });

  return {
    // State
    currentTheme: computed(() => currentTheme.value),
    activeTheme,
    isDarkMode: computed(() => isDarkMode.value),
    customTheme,

    // Methods
    setTheme,
    getTheme,
    getAvailableThemes,
    toggleDarkMode,
    
    // Customization
    updateThemeColor,
    resetCustomTheme,
    
    // Utilities
    getColorValue,
    getSpacingValue,
    getBorderRadiusValue,
    getShadowValue,
    generateCSSVariables,
    
    // Persistence
    saveThemePreference,
    loadThemePreference,
    detectSystemPreference,
    
    // Application
    applyThemeToDocument
  };
}

// Auto-initialize theme system
export function initializeTheme() {
  const theme = useTheme();
  
  // Load saved preferences or detect system preference
  theme.loadThemePreference();
  theme.detectSystemPreference();
  
  return theme;
}
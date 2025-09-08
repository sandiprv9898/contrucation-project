/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Modern Stripe-inspired palette
        primary: {
          DEFAULT: '#635BFF', // Stripe Purple
          50: '#F8F7FF',
          100: '#F1EFFF', 
          200: '#E4E0FF',
          300: '#D1CCFF',
          400: '#B8B0FF',
          500: '#635BFF',
          600: '#5548E8',
          700: '#4338CA',
          800: '#3730A3',
          900: '#312E81'
        },
        secondary: {
          DEFAULT: '#0F172A', // Slate Dark
          50: '#F8FAFC',
          100: '#F1F5F9',
          200: '#E2E8F0',
          300: '#CBD5E1',
          400: '#94A3B8',
          500: '#64748B',
          600: '#475569',
          700: '#334155',
          800: '#1E293B',
          900: '#0F172A'
        },
        accent: '#00D924', // Success Green
        success: '#10B981',
        warning: '#F59E0B',
        destructive: '#EF4444',
        info: '#3B82F6',
        
        // Neutral grays (Stripe-inspired)
        gray: {
          50: '#FAFBFC',
          100: '#F6F9FC', 
          200: '#E6EBF1',
          300: '#CED4DA',
          400: '#8E9AA8',
          500: '#6B7C95',
          600: '#525F7F',
          700: '#3C4257',
          800: '#24292E',
          900: '#0F172A'
        },
        
        // UI colors
        background: '#FAFBFC',
        foreground: '#0F172A',
        card: '#FFFFFF',
        border: '#E6EBF1',
        input: '#FFFFFF',
        ring: '#635BFF',
        
        muted: {
          DEFAULT: '#F6F9FC',
          foreground: '#6B7C95'
        },
        
        // Construction-specific status colors
        'status-planning': '#3B82F6',
        'status-active': '#10B981',
        'status-on-hold': '#F59E0B',
        'status-completed': '#6B7C95',
        'status-delayed': '#EF4444',
        
        // Priority colors  
        'priority-critical': '#EF4444',
        'priority-high': '#F59E0B',
        'priority-medium': '#3B82F6',
        'priority-low': '#10B981'
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem'
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
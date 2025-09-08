/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#FF6B35', // Construction Orange
          50: '#FFF4F1',
          100: '#FFE6DC',
          200: '#FFCCB8',
          300: '#FFB394',
          400: '#FF9970',
          500: '#FF6B35',
          600: '#E55A2B',
          700: '#CC4E21',
          800: '#B33F17',
          900: '#99350E'
        },
        secondary: {
          DEFAULT: '#4A5568', // Steel Blue
          50: '#F7FAFC',
          100: '#EDF2F7',
          200: '#E2E8F0',
          300: '#CBD5E0',
          400: '#A0AEC0',
          500: '#4A5568',
          600: '#2D3748',
          700: '#1A202C',
          800: '#171923',
          900: '#0F1419'
        },
        accent: '#38A169', // Safety Green
        success: '#38A169',
        warning: '#F6AD55',
        destructive: '#E53E3E',
        muted: {
          DEFAULT: '#F7FAFC',
          foreground: '#718096'
        },
        background: '#FFFFFF',
        foreground: '#1A202C',
        card: '#FFFFFF',
        border: '#E2E8F0',
        // Status Colors
        'status-planning': '#3182CE',
        'status-active': '#38A169',
        'status-on-hold': '#F6AD55',
        'status-completed': '#4A5568',
        'status-delayed': '#E53E3E',
        // Priority Colors  
        'priority-critical': '#E53E3E',
        'priority-high': '#FF6B35',
        'priority-medium': '#F6AD55',
        'priority-low': '#38A169'
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
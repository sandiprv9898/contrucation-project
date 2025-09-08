# CONST-002: Setup Vue.js Project with TypeScript, Vite, and Tailwind CSS

**Task Type**: Infrastructure Setup  
**Estimated Time**: 3 hours  
**Priority**: High  
**Status**: Pending  
**Branch**: `feature/CONST-002-vue-project-setup`  
**Depends On**: CONST-001

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified (Development server setup)
- [x] Database changes planned (N/A)
- [x] UI components identified (Base setup requirements)
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Setup the Vue.js 3.4 project foundation with TypeScript, Vite dev server, and Tailwind CSS configured for the Construction Management Platform.

### Functional Requirements
- Vue.js 3.4+ with Composition API
- TypeScript strict mode enabled
- Vite dev server on port 3073
- Tailwind CSS with construction industry color scheme
- Pinia for state management
- Vue Router for navigation
- Lucide Vue for icons
- ESLint and Prettier for code quality

### Technical Specifications

#### Package Dependencies
```json
{
  "dependencies": {
    "vue": "^3.4.0",
    "@vue/typescript": "latest",
    "vue-router": "^4.2.0",
    "pinia": "^2.1.0",
    "lucide-vue-next": "^0.300.0"
  },
  "devDependencies": {
    "@vitejs/plugin-vue": "latest",
    "vite": "latest",
    "typescript": "^5.0.0",
    "tailwindcss": "^3.3.0",
    "@typescript-eslint/eslint-plugin": "latest",
    "@typescript-eslint/parser": "latest",
    "eslint": "latest",
    "eslint-plugin-vue": "latest",
    "prettier": "latest",
    "vitest": "latest",
    "@vue/test-utils": "latest"
  }
}
```

#### Vite Configuration
```typescript
// vite.config.ts
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3073,
    host: true
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    }
  }
})
```

#### TypeScript Configuration
```json
// tsconfig.json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "preserve",
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true,
    "baseUrl": ".",
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["src/**/*.ts", "src/**/*.d.ts", "src/**/*.tsx", "src/**/*.vue"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

#### Tailwind Configuration
```javascript
// tailwind.config.js
module.exports = {
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
          500: '#FF6B35',
          600: '#E55A2B',
          700: '#CC4E21'
        },
        secondary: {
          DEFAULT: '#4A5568', // Steel Blue
          50: '#F7FAFC',
          100: '#EDF2F7',
          500: '#4A5568',
          600: '#2D3748',
          700: '#1A202C'
        },
        accent: '#38A169', // Safety Green
        success: '#38A169',
        warning: '#F6AD55',
        destructive: '#E53E3E'
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
```

### Project Structure Setup
```
frontend/
├── public/
├── src/
│   ├── assets/
│   ├── components/
│   │   └── ui/                 # Centralized UI components
│   ├── composables/           # Vue composables
│   ├── pages/                 # Page components
│   ├── router/                # Vue Router config
│   ├── services/              # API services
│   │   └── api/
│   ├── stores/                # Pinia stores
│   ├── types/                 # TypeScript definitions
│   │   └── models/
│   ├── utils/                 # Utility functions
│   ├── App.vue
│   └── main.ts
├── tests/
│   ├── unit/
│   └── e2e/
├── docs/
├── index.html
├── package.json
├── tailwind.config.js
├── tsconfig.json
├── vite.config.ts
├── .eslintrc.js
├── .prettierrc
└── README.md
```

### Development Scripts
```json
{
  "scripts": {
    "dev": "vite --port 3073",
    "build": "vue-tsc && vite build",
    "preview": "vite preview --port 3073",
    "type-check": "vue-tsc --noEmit",
    "lint": "eslint . --ext .vue,.js,.jsx,.cjs,.mjs,.ts,.tsx,.cts,.mts --fix",
    "format": "prettier --write src/",
    "test:unit": "vitest",
    "test:coverage": "vitest --coverage"
  }
}
```

## API Endpoints Identified
- **Development Server**: `http://localhost:3073`
- **HMR WebSocket**: Hot module replacement
- **Static Assets**: Vite asset serving

## Test Scenarios
1. **Project Setup**
   - `npm run dev` starts on port 3073
   - TypeScript compilation works
   - Tailwind styles apply correctly
   - Hot reload functions

2. **Build Process**
   - `npm run build` succeeds
   - Type checking passes
   - Bundle optimization works
   - Preview mode functions

3. **Development Tools**
   - ESLint catches errors
   - Prettier formats code
   - Vue DevTools integration
   - Source maps work correctly

## Performance Impact Assessment
- **Bundle Size**: Base Vue.js + dependencies (~200KB gzipped)
- **Build Time**: Fast with Vite HMR
- **Development Server**: Sub-100ms startup on port 3073
- **Hot Reload**: <50ms update time

## Implementation Steps
1. Initialize Vite Vue.js project with TypeScript
2. Configure Tailwind CSS with construction theme
3. Setup folder structure per project standards
4. Configure ESLint, Prettier, and TypeScript strict mode
5. Setup Pinia and Vue Router
6. Create base App.vue and main.ts
7. Verify dev server runs on port 3073
8. Test build and type checking

## Acceptance Criteria
- [ ] Project runs on `npm run dev` at port 3073
- [ ] TypeScript strict mode enabled and working
- [ ] Tailwind CSS construction theme applied
- [ ] All development scripts functional
- [ ] Project structure matches standards
- [ ] ESLint and Prettier configured
- [ ] Build process succeeds
- [ ] No console errors on startup

## Dependencies
- **Depends On**: CONST-001 (Task documentation)

## Next Tasks
- **CONST-003**: Build core UI components library

---
**Created**: 2024  
**Assignee**: Development Team
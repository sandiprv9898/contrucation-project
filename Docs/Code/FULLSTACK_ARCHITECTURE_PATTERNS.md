# Full-Stack Architecture & Coding Patterns

## Overview
This document outlines the comprehensive architecture patterns and coding standards used in the Construction Management Platform:
- **Backend**: Laravel 11.x with Domain-Driven Design
- **Frontend**: Vue.js 3.4 + TypeScript + Pinia + Tailwind CSS

---

## 🏗️ Backend Architecture (Laravel 11.x)

### Domain-Driven Design Structure
```
app/
├── Domain/                     # Business logic and domain models
│   └── {Domain}/
│       ├── Models/            # Eloquent models
│       ├── Services/          # Business logic services
│       ├── DTOs/             # Data Transfer Objects
│       ├── Repositories/     # Repository pattern interfaces & implementations
│       ├── Events/           # Domain events
│       ├── Jobs/            # Background jobs
│       └── Filters/         # Query filters
├── Http/                     # HTTP layer (Controllers, Requests, Resources)
│   ├── Controllers/Api/     # API controllers
│   ├── Requests/           # Form request validation
│   ├── Resources/          # API resource transformers
│   └── Traits/            # Reusable traits
```

### Backend Patterns Summary
- **Controller Pattern**: Dependency injection, consistent error handling, transaction management
- **Service Layer**: Business logic abstraction with DTOs
- **Repository Pattern**: Data access abstraction with interfaces
- **API Resources**: Consistent response transformation
- **Traits**: ApiResponseTrait, FilterableTrait for code reuse
- **Caching Strategy**: Systematic cache keys with invalidation patterns
- **Security**: Authorization checks, rate limiting, CSRF protection

---

## 🎨 Frontend Architecture (Vue.js 3.4 + TypeScript)

### Project Structure
```
src/
├── components/              # Reusable UI components
│   └── ui/                 # Base UI components (buttons, inputs, etc.)
├── composables/            # Reusable composition functions
├── layouts/                # Page layouts
├── modules/                # Feature-based organization
│   └── {feature}/
│       ├── api/           # API service layer
│       ├── components/    # Feature-specific components
│       ├── stores/        # Pinia stores
│       ├── types/         # TypeScript type definitions
│       └── utils/         # Feature utilities
├── pages/                  # Route components
├── router/                 # Vue Router configuration
├── services/               # Shared services
├── stores/                 # Global Pinia stores
├── types/                  # Global TypeScript types
└── utils/                  # Shared utilities
```

---

## 🗄️ State Management (Pinia)

### Store Pattern
```typescript
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { StateType, ApiResponse } from './types'

export const useFeatureStore = defineStore('feature', () => {
  // State
  const state = ref<StateType>({
    data: null,
    isLoading: false,
    isSaving: false,
    error: null,
    hasUnsavedChanges: false,
    lastUpdated: null
  })

  // Computed
  const isLoaded = computed(() => state.value.data !== null)
  const hasError = computed(() => state.value.error !== null)

  // Actions
  async function loadData() {
    if (state.value.isLoading) return
    
    try {
      state.value.isLoading = true
      state.value.error = null

      const response = await FeatureApi.getData()
      state.value.data = response.data
      state.value.lastUpdated = new Date().toISOString()

    } catch (error) {
      console.error('Failed to load data:', error)
      state.value.error = error instanceof Error ? error.message : 'Failed to load'
    } finally {
      state.value.isLoading = false
    }
  }

  async function saveData(data: Partial<StateType>) {
    try {
      state.value.isSaving = true
      const response = await FeatureApi.updateData(data)
      
      // Update local state
      state.value.data = { ...state.value.data, ...response.data }
      state.value.hasUnsavedChanges = false
      
    } catch (error) {
      throw error
    } finally {
      state.value.isSaving = false
    }
  }

  return {
    // State
    state: computed(() => state.value),
    
    // Computed
    isLoaded,
    hasError,
    
    // Actions
    loadData,
    saveData
  }
})
```

### State Management Best Practices
1. **Reactive State**: Use `ref()` and `reactive()` appropriately
2. **Computed Properties**: Derive state with `computed()`
3. **Error Handling**: Consistent error state management
4. **Loading States**: Track loading and saving states
5. **Cache Management**: Implement intelligent caching with timestamps
6. **Optimistic Updates**: Update UI immediately, rollback on error

---

## 🌐 API Layer

### API Client Pattern
```typescript
import axios, { type AxiosInstance, type AxiosResponse } from 'axios'
import { API_CONFIG } from '../constants/api'
import { TokenManager } from '../utils/tokenManager'

class ApiClient {
  private client: AxiosInstance

  constructor() {
    this.client = axios.create({
      baseURL: API_CONFIG.BASE_URL,
      withCredentials: true,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      timeout: API_CONFIG.TIMEOUT,
    })

    this.setupInterceptors()
  }

  private setupInterceptors(): void {
    // Request interceptor for auth tokens
    this.client.interceptors.request.use((config: any) => {
      const token = TokenManager.getToken()
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
      return config
    })

    // Response interceptor for error handling
    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          // Handle unauthorized access
          TokenManager.removeToken()
          // Redirect to login
        }
        return Promise.reject(error)
      }
    )
  }

  // Generic CRUD methods
  public async get<T>(url: string, params?: Record<string, any>): Promise<T> {
    const response = await this.client.get<T>(url, { params })
    return response.data
  }

  public async post<T>(url: string, data?: any): Promise<T> {
    const response = await this.client.post<T>(url, data)
    return response.data
  }

  // ... other HTTP methods
}

export const apiClient = new ApiClient()
```

### API Service Pattern
```typescript
import { apiClient } from '@/modules/shared/api/client'
import type { EntityResponse, EntityRequest } from './types'

export class EntityApi {
  private static readonly BASE_PATH = '/api/v1/entities'

  /**
   * Get all entities with filtering and pagination
   */
  static async getEntities(params?: {
    page?: number
    per_page?: number
    search?: string
    status?: string
  }): Promise<EntityResponse> {
    return apiClient.get<EntityResponse>(this.BASE_PATH, params)
  }

  /**
   * Get single entity by ID
   */
  static async getEntity(id: string): Promise<{ data: Entity }> {
    return apiClient.get<{ data: Entity }>(`${this.BASE_PATH}/${id}`)
  }

  /**
   * Create new entity
   */
  static async createEntity(data: EntityRequest): Promise<{ data: Entity }> {
    return apiClient.post<{ data: Entity }>(this.BASE_PATH, data)
  }

  /**
   * Update existing entity
   */
  static async updateEntity(id: string, data: Partial<EntityRequest>): Promise<{ data: Entity }> {
    return apiClient.put<{ data: Entity }>(`${this.BASE_PATH}/${id}`, data)
  }

  /**
   * Delete entity
   */
  static async deleteEntity(id: string): Promise<{ success: boolean }> {
    return apiClient.delete<{ success: boolean }>(`${this.BASE_PATH}/${id}`)
  }
}
```

---

## 🧩 Component Architecture

### Base Component Structure
```vue
<template>
  <div class="component-wrapper">
    <!-- Loading State -->
    <div v-if="isLoading" class="loading-spinner">
      Loading...
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-message">
      {{ error }}
    </div>

    <!-- Main Content -->
    <div v-else class="content">
      <!-- Component content -->
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import type { ComponentProps } from './types'

// Props with validation
interface Props {
  entityId?: string
  mode?: 'view' | 'edit' | 'create'
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'view',
  disabled: false
})

// Emits
const emit = defineEmits<{
  save: [data: any]
  cancel: []
  error: [error: string]
}>()

// State
const isLoading = ref(false)
const error = ref<string | null>(null)
const formData = ref<any>({})

// Computed
const isEditable = computed(() => props.mode !== 'view' && !props.disabled)
const hasChanges = computed(() => {
  // Compare formData with original
  return JSON.stringify(formData.value) !== JSON.stringify(originalData.value)
})

// Methods
const handleSave = async () => {
  if (!hasChanges.value) return
  
  try {
    isLoading.value = true
    // Save logic
    emit('save', formData.value)
  } catch (err) {
    const errorMessage = err instanceof Error ? err.message : 'Save failed'
    error.value = errorMessage
    emit('error', errorMessage)
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(async () => {
  if (props.entityId) {
    await loadEntity()
  }
})

// Watchers
watch(() => props.entityId, async (newId) => {
  if (newId) {
    await loadEntity()
  }
})
</script>

<style scoped>
.component-wrapper {
  @apply space-y-4;
}

.loading-spinner {
  @apply flex items-center justify-center py-8;
}

.error-message {
  @apply bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded;
}
</style>
```

### Component Best Practices
1. **TypeScript**: Use strict typing for props, emits, and data
2. **Composition API**: Prefer composition API over options API
3. **Props Validation**: Define interfaces and default values
4. **State Management**: Use local state for component-specific data
5. **Error Handling**: Handle and display errors gracefully
6. **Loading States**: Show loading indicators for async operations
7. **Accessibility**: Include ARIA attributes and keyboard navigation

---

## 🔧 Composables Pattern

### Validation Composable
```typescript
import { ref, reactive, computed } from 'vue'

export interface ValidationRule {
  validate: (value: any) => string | null
  message?: string
  trigger?: 'change' | 'blur' | 'submit'
}

export function useValidation(schema: Record<string, ValidationRule[]>) {
  const errors = reactive<Record<string, string>>({})
  const touched = reactive<Record<string, boolean>>({})
  
  const isValid = computed(() => Object.keys(errors).length === 0)
  
  const validateField = (field: string, value: any): string | null => {
    const rules = schema[field] || []
    
    for (const rule of rules) {
      const error = rule.validate(value)
      if (error) {
        errors[field] = error
        return error
      }
    }
    
    delete errors[field]
    return null
  }
  
  const validateForm = (formData: Record<string, any>): boolean => {
    Object.keys(schema).forEach(field => {
      validateField(field, formData[field])
    })
    return isValid.value
  }
  
  return {
    errors: computed(() => errors),
    touched: computed(() => touched),
    isValid,
    validateField,
    validateForm
  }
}
```

### API Composable
```typescript
import { ref, computed } from 'vue'
import type { ApiResponse } from '@/types'

export function useApi<T>(apiCall: () => Promise<T>) {
  const data = ref<T | null>(null)
  const error = ref<string | null>(null)
  const isLoading = ref(false)
  
  const isLoaded = computed(() => data.value !== null)
  const hasError = computed(() => error.value !== null)
  
  const execute = async (): Promise<T | null> => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await apiCall()
      data.value = response
      return response
      
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Request failed'
      error.value = message
      console.error('API call failed:', err)
      return null
    } finally {
      isLoading.value = false
    }
  }
  
  const reset = () => {
    data.value = null
    error.value = null
    isLoading.value = false
  }
  
  return {
    data: computed(() => data.value),
    error: computed(() => error.value),
    isLoading: computed(() => isLoading.value),
    isLoaded,
    hasError,
    execute,
    reset
  }
}
```

---

## 📝 TypeScript Integration

### Type Definitions
```typescript
// Base types
export interface BaseEntity {
  id: string
  created_at: string
  updated_at: string
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
  meta?: {
    current_page: number
    per_page: number
    total: number
    last_page: number
  }
}

// Feature-specific types
export interface User extends BaseEntity {
  name: string
  email: string
  role: UserRole
  avatar_url?: string
  company?: Company
}

export type UserRole = 'admin' | 'project_manager' | 'supervisor' | 'field_worker'

export interface Company extends BaseEntity {
  name: string
  industry: string
  size: string
  address: string
}

// Form types
export interface UserForm {
  name: string
  email: string
  role: UserRole
  company_id?: string
  phone?: string
  department?: string
}

// Store state types
export interface UserStoreState {
  users: User[]
  currentUser: User | null
  isLoading: boolean
  isSaving: boolean
  error: string | null
  hasUnsavedChanges: boolean
}
```

### Type Safety Best Practices
1. **Strict Mode**: Enable strict TypeScript compilation
2. **Interface Definitions**: Define clear interfaces for all data structures
3. **Generic Types**: Use generics for reusable components and composables
4. **Union Types**: Use union types for controlled string values
5. **Optional Properties**: Mark optional properties appropriately
6. **Type Guards**: Implement type guards for runtime type checking

---

## 🎨 Styling Architecture (Tailwind CSS)

### Component Styling Pattern
```vue
<template>
  <div :class="containerClasses">
    <header :class="headerClasses">
      <h2 :class="titleClasses">
        {{ title }}
      </h2>
    </header>
    
    <main :class="contentClasses">
      <!-- Content -->
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  variant?: 'default' | 'compact' | 'expanded'
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md'
})

// Computed classes with variants
const containerClasses = computed(() => [
  'rounded-lg border bg-white shadow-sm',
  {
    'p-4': props.size === 'sm',
    'p-6': props.size === 'md',
    'p-8': props.size === 'lg'
  }
])

const headerClasses = computed(() => [
  'border-b pb-4 mb-4',
  {
    'hidden': props.variant === 'compact'
  }
])

const titleClasses = computed(() => [
  'font-semibold text-gray-900',
  {
    'text-lg': props.size === 'sm',
    'text-xl': props.size === 'md',
    'text-2xl': props.size === 'lg'
  }
])
</script>
```

### Design System
```typescript
// Tailwind config extensions
export const theme = {
  extend: {
    colors: {
      primary: {
        50: '#f0f9ff',
        500: '#3b82f6',
        900: '#1e3a8a',
      },
      gray: {
        50: '#f9fafb',
        100: '#f3f4f6',
        500: '#6b7280',
        900: '#111827',
      }
    },
    spacing: {
      '18': '4.5rem',
      '88': '22rem',
    },
    animation: {
      'fade-in': 'fadeIn 0.5s ease-in-out',
      'slide-up': 'slideUp 0.3s ease-out',
    }
  }
}

// Utility classes
export const buttonVariants = {
  primary: 'bg-primary-500 hover:bg-primary-600 text-white',
  secondary: 'bg-gray-100 hover:bg-gray-200 text-gray-900',
  outline: 'border border-primary-500 text-primary-500 hover:bg-primary-50'
}
```

---

## 🔄 Data Flow & Communication

### Parent-Child Communication
```vue
<!-- Parent Component -->
<template>
  <ChildComponent
    :data="parentData"
    @update="handleUpdate"
    @save="handleSave"
  />
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ChildComponent from './ChildComponent.vue'

const parentData = ref(initialData)

const handleUpdate = (newData: any) => {
  parentData.value = { ...parentData.value, ...newData }
}

const handleSave = async (data: any) => {
  try {
    await saveToApi(data)
    // Update parent state
  } catch (error) {
    // Handle error
  }
}
</script>

<!-- Child Component -->
<template>
  <form @submit.prevent="handleSubmit">
    <input 
      v-model="localData.name"
      @input="notifyUpdate"
    />
    <button type="submit">Save</button>
  </form>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface Props {
  data: any
}

const props = defineProps<Props>()

const emit = defineEmits<{
  update: [data: any]
  save: [data: any]
}>()

const localData = ref({ ...props.data })

const notifyUpdate = () => {
  emit('update', localData.value)
}

const handleSubmit = () => {
  emit('save', localData.value)
}

// Sync prop changes to local data
watch(() => props.data, (newData) => {
  localData.value = { ...newData }
}, { deep: true })
</script>
```

### Store Integration
```vue
<template>
  <div>
    <div v-if="store.isLoading">Loading...</div>
    <div v-else-if="store.hasError">{{ store.error }}</div>
    <div v-else>
      <!-- Data display -->
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useEntityStore } from '@/modules/entities/stores/entity.store'

const store = useEntityStore()

onMounted(async () => {
  if (!store.isLoaded) {
    await store.loadEntities()
  }
})

const handleSave = async (data: any) => {
  try {
    await store.saveEntity(data)
    // Success handling
  } catch (error) {
    // Error handling
  }
}
</script>
```

---

## 🔍 Testing Strategies

### Component Testing
```typescript
import { mount } from '@vue/test-utils'
import { describe, it, expect, beforeEach } from 'vitest'
import EntityForm from '../EntityForm.vue'

describe('EntityForm', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(EntityForm, {
      props: {
        entity: mockEntity,
        mode: 'edit'
      }
    })
  })

  it('renders form fields correctly', () => {
    expect(wrapper.find('[data-testid="entity-name"]').exists()).toBe(true)
    expect(wrapper.find('[data-testid="entity-description"]').exists()).toBe(true)
  })

  it('emits save event with form data', async () => {
    await wrapper.find('form').trigger('submit.prevent')
    
    expect(wrapper.emitted().save).toBeTruthy()
    expect(wrapper.emitted().save[0]).toEqual([mockEntity])
  })

  it('validates required fields', async () => {
    await wrapper.find('[data-testid="entity-name"]').setValue('')
    await wrapper.find('form').trigger('submit.prevent')
    
    expect(wrapper.find('.error-message').text()).toBe('Name is required')
  })
})
```

### Store Testing
```typescript
import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, beforeEach } from 'vitest'
import { useEntityStore } from '../entity.store'

describe('Entity Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('loads entities successfully', async () => {
    const store = useEntityStore()
    
    await store.loadEntities()
    
    expect(store.entities.length).toBeGreaterThan(0)
    expect(store.isLoading).toBe(false)
  })

  it('handles loading errors', async () => {
    const store = useEntityStore()
    
    // Mock API failure
    vi.mocked(EntityApi.getEntities).mockRejectedValue(new Error('API Error'))
    
    await store.loadEntities()
    
    expect(store.error).toBe('API Error')
    expect(store.isLoading).toBe(false)
  })
})
```

---

## 🚀 Performance Optimization

### Vue 3 Performance Patterns
```vue
<template>
  <!-- Use v-show for frequent toggles -->
  <div v-show="isVisible" class="expensive-component">
    <!-- Keep-alive for cached components -->
    <KeepAlive>
      <component :is="currentComponent" />
    </KeepAlive>
  </div>

  <!-- Lazy loading with Suspense -->
  <Suspense>
    <template #default>
      <AsyncComponent />
    </template>
    <template #fallback>
      <LoadingSkeleton />
    </template>
  </Suspense>
</template>

<script setup lang="ts">
import { defineAsyncComponent, shallowRef } from 'vue'

// Lazy load heavy components
const AsyncComponent = defineAsyncComponent(() => import('./HeavyComponent.vue'))

// Use shallowRef for large objects
const largeData = shallowRef(initialData)

// Memoize expensive computations
const expensiveComputed = computed(() => {
  return heavyCalculation(props.data)
})
</script>
```

### Bundle Optimization
```typescript
// vite.config.ts
export default defineConfig({
  build: {
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'vue-router', 'pinia'],
          ui: ['@headlessui/vue', '@heroicons/vue']
        }
      }
    }
  },
  optimizeDeps: {
    include: ['vue', 'vue-router', 'pinia']
  }
})
```

---

## 🛡️ Security Best Practices

### Frontend Security
1. **Input Sanitization**: Sanitize user inputs before display
2. **XSS Prevention**: Use v-text instead of v-html when possible
3. **CSRF Protection**: Include CSRF tokens in API requests
4. **Authentication**: Implement proper token management
5. **Authorization**: Check permissions before rendering UI elements
6. **Content Security Policy**: Configure CSP headers

### Authentication Pattern
```typescript
// Token management
export class TokenManager {
  private static readonly TOKEN_KEY = 'auth_token'
  
  static setToken(token: string): void {
    localStorage.setItem(this.TOKEN_KEY, token)
  }
  
  static getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY)
  }
  
  static removeToken(): void {
    localStorage.removeItem(this.TOKEN_KEY)
  }
  
  static isAuthenticated(): boolean {
    const token = this.getToken()
    if (!token) return false
    
    // Validate token expiration
    try {
      const payload = JSON.parse(atob(token.split('.')[1]))
      return payload.exp > Date.now() / 1000
    } catch {
      return false
    }
  }
}
```

---

## 📁 File Organization Best Practices

### Module-Based Organization
```
src/modules/settings/
├── api/
│   └── settings.api.ts         # API service layer
├── components/
│   ├── SystemSettings.vue     # Feature components
│   ├── CompanySettings.vue
│   └── forms/
│       └── SettingsForm.vue   # Sub-components
├── stores/
│   └── settings.store.ts      # Pinia store
├── types/
│   └── settings.types.ts      # TypeScript definitions
├── utils/
│   └── validation.ts          # Feature utilities
└── index.ts                   # Module exports
```

### Naming Conventions
- **Components**: PascalCase (UserProfile.vue)
- **Stores**: camelCase (useUserStore)
- **Types**: PascalCase interfaces (User, ApiResponse)
- **Files**: kebab-case (user-profile.vue)
- **Variables**: camelCase (userName, isLoading)
- **Constants**: SCREAMING_SNAKE_CASE (API_BASE_URL)

---

## 🔧 Development Tools & Configuration

### Essential Dev Tools
```json
{
  "devDependencies": {
    "@vitejs/plugin-vue": "^4.0.0",
    "@vue/test-utils": "^2.3.0",
    "typescript": "^5.0.0",
    "vite": "^4.0.0",
    "vitest": "^0.32.0",
    "tailwindcss": "^3.3.0",
    "eslint": "^8.45.0",
    "prettier": "^2.8.0"
  }
}
```

### ESLint Configuration
```javascript
// .eslintrc.js
module.exports = {
  extends: [
    '@vue/eslint-config-typescript/recommended',
    '@vue/eslint-config-prettier'
  ],
  rules: {
    '@typescript-eslint/no-unused-vars': 'error',
    '@typescript-eslint/explicit-function-return-type': 'warn',
    'vue/component-name-in-template-casing': ['error', 'PascalCase']
  }
}
```

---

## 🚀 Deployment & Build

### Environment Configuration
```typescript
// config/environment.ts
export const config = {
  API_BASE_URL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8001/api/v1',
  APP_NAME: import.meta.env.VITE_APP_NAME || 'Construction Platform',
  NODE_ENV: import.meta.env.NODE_ENV || 'development',
  DEBUG: import.meta.env.VITE_DEBUG === 'true'
}
```

### Build Optimization
```typescript
// vite.config.ts
export default defineConfig({
  plugins: [vue()],
  build: {
    target: 'es2015',
    minify: 'terser',
    sourcemap: process.env.NODE_ENV === 'development',
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return 'vendor'
          }
        }
      }
    }
  }
})
```

---

## 🎯 Integration Patterns

### Backend-Frontend Communication
```typescript
// Consistent API response handling
interface ApiResponse<T> {
  success: boolean
  message: string
  data: T
  meta?: PaginationMeta
}

// Error handling integration
export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public response?: any
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

// Type-safe API integration
export async function handleApiResponse<T>(
  apiCall: Promise<ApiResponse<T>>
): Promise<T> {
  try {
    const response = await apiCall
    if (!response.success) {
      throw new ApiError(response.message, 400, response)
    }
    return response.data
  } catch (error) {
    if (error instanceof ApiError) {
      throw error
    }
    throw new ApiError('Request failed', 500, error)
  }
}
```

---

This comprehensive architecture ensures maintainability, scalability, and consistency across the entire full-stack application. Both backend and frontend follow established patterns and best practices for their respective technologies while maintaining seamless integration.
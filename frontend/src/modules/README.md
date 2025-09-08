# Modular Architecture

This directory contains the modular architecture for the Construction Management Platform frontend application.

## Architecture Overview

The application is organized into feature-based modules to promote:
- **Maintainability**: Clear separation of concerns
- **Reusability**: Shared utilities and components
- **Testability**: Isolated modules for easier testing
- **Scalability**: Easy to add new features and modules

## Directory Structure

```
src/modules/
├── shared/           # Shared utilities and services
│   ├── api/         # Generic API client and configuration
│   ├── constants/   # Application-wide constants
│   ├── types/       # Shared TypeScript types
│   ├── utils/       # Utility functions
│   └── index.ts     # Module exports
│
└── auth/            # Authentication module
    ├── api/         # Authentication API services
    ├── stores/      # Pinia stores for auth state
    ├── composables/ # Vue composables for auth logic
    ├── types/       # Authentication-specific types
    ├── constants/   # Authentication constants
    ├── utils/       # Authentication utilities
    └── index.ts     # Module exports
```

## Shared Module (`/shared`)

Contains common utilities and services used across multiple modules.

### Key Components

- **API Client** (`api/client.ts`): Centralized Axios configuration with interceptors
- **Token Manager** (`utils/tokenManager.ts`): Secure token storage and management
- **API Constants** (`constants/api.ts`): API endpoints and configuration

### Usage

```typescript
import { apiClient, TokenManager, API_CONFIG } from '@/modules/shared'
```

## Authentication Module (`/auth`)

Complete authentication system with modular components.

### Key Components

- **AuthApi** (`api/auth.api.ts`): Authentication API service
- **useAuthStore** (`stores/auth.store.ts`): Pinia store for auth state
- **useAuth** (`composables/useAuth.ts`): Main authentication composable
- **useAuthValidation** (`composables/useAuthValidation.ts`): Form validation composables
- **AuthValidator** (`utils/validation.ts`): Validation utility class

### Usage

```typescript
import { useAuth, useLoginValidation, AuthApi } from '@/modules/auth'

// In a Vue component
const { login, logout, user, isAuthenticated } = useAuth()
const { validate, errors } = useLoginValidation()
```

## Module Patterns

### 1. API Layer Pattern

Each module contains an API layer that encapsulates all server communication:

```typescript
// auth/api/auth.api.ts
export class AuthApi {
  static async login(credentials: LoginRequest): Promise<AuthResponse> {
    return apiClient.post<AuthResponse>(API_CONFIG.ENDPOINTS.AUTH.LOGIN, credentials)
  }
}
```

### 2. Store Pattern

Pinia stores manage module state with computed properties and actions:

```typescript
// auth/stores/auth.store.ts
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isAuthenticated = computed(() => !!user.value)
  
  const login = async (credentials: LoginRequest) => {
    // Implementation
  }
  
  return { user, isAuthenticated, login }
})
```

### 3. Composable Pattern

Composables provide reusable logic and integrate multiple concerns:

```typescript
// auth/composables/useAuth.ts
export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()
  
  const login = async (credentials: LoginRequest) => {
    await authStore.login(credentials)
    await router.push('/dashboard')
  }
  
  return { login, user: authStore.user }
}
```

### 4. Utility Pattern

Utility classes provide pure functions for specific tasks:

```typescript
// auth/utils/validation.ts
export class AuthValidator {
  static validateEmail(email: string): { isValid: boolean; error?: string } {
    // Implementation
  }
}
```

### 5. Constants Pattern

Constants are organized by domain and exported as const assertions:

```typescript
// auth/constants/auth.constants.ts
export const AUTH_ROUTES = {
  LOGIN: '/auth/login',
  REGISTER: '/auth/register',
} as const
```

## Benefits

### 1. Separation of Concerns
- API logic separate from UI logic
- Validation logic separate from form handling
- State management isolated in stores

### 2. Reusability
- Composables can be used across components
- Utility functions are pure and reusable
- API services can be used in different contexts

### 3. Type Safety
- Strong TypeScript typing throughout
- Interface definitions in dedicated type files
- Generic types for API responses

### 4. Testability
- Pure functions easy to unit test
- Composables can be tested in isolation
- API services can be mocked easily

### 5. Maintainability
- Clear file organization
- Single responsibility principle
- Easy to locate and modify code

## Adding New Modules

When adding a new module (e.g., `projects`):

1. Create the module directory: `src/modules/projects/`
2. Add the standard subdirectories: `api/`, `stores/`, `composables/`, `types/`, `constants/`, `utils/`
3. Create an `index.ts` file to export public APIs
4. Follow the established patterns for each layer
5. Update shared types and constants if needed

Example:
```
src/modules/projects/
├── api/projects.api.ts
├── stores/projects.store.ts
├── composables/useProjects.ts
├── types/projects.types.ts
├── constants/projects.constants.ts
└── index.ts
```

## Best Practices

1. **Import from module index**: Always import from the module's index file
2. **Use TypeScript strictly**: Define interfaces for all data structures
3. **Follow naming conventions**: Use consistent naming patterns
4. **Keep composables focused**: Each composable should have a single responsibility
5. **Document complex logic**: Add comments for business logic
6. **Use const assertions**: For constants and configuration objects
7. **Handle errors gracefully**: Provide meaningful error messages
8. **Test thoroughly**: Write tests for utilities and composables
# CONST-004: Create Authentication Layout with Marketing Section

**Task Type**: Frontend Development  
**Estimated Time**: 2.5 hours  
**Priority**: High  
**Status**: In Progress  
**Branch**: `feature/CONST-004-authentication-layout`  
**Depends On**: CONST-003, CONST-009

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified
- [x] Database changes planned (N/A - using backend from CONST-009)
- [x] UI components identified
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Create a complete authentication system connecting Vue.js frontend to Laravel backend with a two-section layout: marketing content on the left, authentication forms on the right, following the Construction Management Platform design system.

### Functional Requirements
- Two-column responsive authentication layout
- Marketing content section with construction industry messaging
- Login, Register, and Forgot Password forms
- Integration with Laravel Sanctum API from CONST-009
- Pinia store for authentication state management
- Form validation with real-time feedback
- Error handling for API responses
- Responsive design for mobile and desktop
- Route protection for authenticated users

### API Integration Requirements

#### Backend API Endpoints (from CONST-009)
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User authentication  
- `POST /api/v1/auth/logout` - User logout
- `POST /api/v1/auth/forgot-password` - Password reset request
- `GET /api/v1/auth/me` - Current user profile

#### Authentication Flow
```typescript
interface LoginCredentials {
  email: string;
  password: string;
}

interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role?: 'admin' | 'project_manager' | 'supervisor' | 'field_worker';
  company_id?: string;
}

interface User {
  id: string;
  name: string;
  email: string;
  role: string;
  avatar_url?: string;
  company?: {
    id: string;
    name: string;
    industry: string;
  };
}

interface AuthResponse {
  message: string;
  user: User;
  token: string;
}
```

### UI/UX Requirements

#### Layout Structure
```
┌─────────────────────────────────────────────────────────────┐
│                Authentication Layout                        │
├─────────────────────────┬───────────────────────────────────┤
│     Marketing Section   │        Auth Forms Section        │
│                        │                                   │
│  • Hero messaging      │  • Login Form                     │
│  • Feature highlights  │  • Register Form                  │
│  • Industry imagery    │  • Forgot Password Form           │
│  • Social proof        │  • Form validation                │
│                        │  • Error messages                 │
└─────────────────────────┴───────────────────────────────────┘
```

#### Marketing Section Content
- **Hero Message**: "Streamline Your Construction Projects"
- **Tagline**: "Professional project management tools designed for the construction industry"
- **Key Features**:
  - Project Timeline Management
  - Team Collaboration Tools
  - Budget & Resource Tracking
  - Mobile-First Design for Field Workers
- **Industry Statistics**: Success metrics and testimonials
- **Construction imagery**: Professional, modern construction scenes

#### Form Components (Using components from CONST-003)
- **VInput**: Email and password fields with validation states
- **VButton**: Primary submit buttons with loading states
- **VCheckbox**: Remember me and terms acceptance
- **VAlert**: Success and error message display
- **VLabel**: Form field labels with required indicators

### Technical Specifications

#### File Structure
```
frontend/src/
├── pages/
│   └── auth/
│       ├── AuthLayout.vue        # Main two-column layout
│       ├── LoginPage.vue         # Login form
│       ├── RegisterPage.vue      # Registration form
│       └── ForgotPasswordPage.vue # Password reset form
├── components/
│   └── auth/
│       ├── MarketingSection.vue  # Left marketing content
│       ├── LoginForm.vue         # Login form component
│       ├── RegisterForm.vue      # Registration form component
│       └── ForgotPasswordForm.vue # Password reset form component
├── stores/
│   ├── auth.ts                   # Pinia authentication store
│   └── api.ts                    # API service configuration
├── services/
│   └── authService.ts            # Authentication API calls
└── router/
    └── auth.ts                   # Authentication routes
```

#### Pinia Auth Store Structure
```typescript
interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

interface AuthStore {
  // State
  state: AuthState;
  
  // Getters
  currentUser: ComputedRef<User | null>;
  isLoggedIn: ComputedRef<boolean>;
  userRole: ComputedRef<string | null>;
  
  // Actions
  login(credentials: LoginCredentials): Promise<AuthResponse>;
  register(userData: RegisterData): Promise<AuthResponse>;
  logout(): Promise<void>;
  forgotPassword(email: string): Promise<void>;
  getCurrentUser(): Promise<User>;
  clearError(): void;
}
```

#### API Service Configuration
```typescript
// Axios configuration for Laravel Sanctum
const apiClient = axios.create({
  baseURL: 'http://localhost:3071/api/v1',
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Request interceptor for auth token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### Responsive Design Requirements

#### Desktop Layout (≥1024px)
- Two-column layout: 50/50 split
- Marketing section with full-height background image
- Auth forms centered in right column
- Maximum form width: 400px

#### Tablet Layout (768px - 1023px)
- Two-column layout: 40/60 split
- Condensed marketing content
- Forms maintain readability

#### Mobile Layout (<768px)
- Single column layout
- Marketing section collapsed to header
- Full-width forms with proper spacing
- Touch-friendly button sizes (minimum 44px)

### Validation Rules

#### Login Form Validation
```typescript
const loginValidation = {
  email: {
    required: true,
    email: true,
    message: 'Please enter a valid email address'
  },
  password: {
    required: true,
    minLength: 1,
    message: 'Password is required'
  }
};
```

#### Registration Form Validation
```typescript
const registerValidation = {
  name: {
    required: true,
    minLength: 2,
    message: 'Name must be at least 2 characters'
  },
  email: {
    required: true,
    email: true,
    unique: true, // Check against backend
    message: 'Please enter a valid email address'
  },
  password: {
    required: true,
    minLength: 8,
    pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
    message: 'Password must be at least 8 characters with uppercase, lowercase, and number'
  },
  password_confirmation: {
    required: true,
    sameAs: 'password',
    message: 'Passwords must match'
  }
};
```

### Performance Requirements
- **Page Load Time**: <2s on 3G connection
- **Form Validation**: Real-time validation with <100ms response
- **API Response Handling**: Loading states and error recovery
- **Image Optimization**: Marketing images optimized for web
- **Bundle Size Impact**: <50KB additional bundle size

### Security Requirements
- **Token Storage**: Secure token storage in localStorage
- **CSRF Protection**: Laravel Sanctum CSRF token handling
- **Input Sanitization**: All form inputs validated and sanitized
- **Error Messages**: Generic error messages to prevent information disclosure
- **Route Protection**: Redirect authenticated users away from auth pages

### Test Scenarios

#### Authentication Flow Tests
1. **User Registration**
   - Valid registration creates user account and logs in
   - Duplicate email shows appropriate error
   - Invalid form data shows validation errors
   - Network errors are handled gracefully

2. **User Login**
   - Valid credentials log user in and redirect to dashboard
   - Invalid credentials show error message
   - Loading states display during API calls
   - Token is properly stored and used for subsequent requests

3. **Password Reset**
   - Valid email triggers password reset process
   - Invalid email shows appropriate error
   - Success message displayed after request

4. **Responsive Design**
   - Layout adapts properly on mobile devices
   - Touch targets meet minimum size requirements
   - Forms remain usable on small screens

#### Error Handling Tests
- Network connectivity issues
- API server errors (500, 503)
- Validation errors from backend
- Token expiration handling

## Implementation Plan

### Phase 1: Layout and Marketing Section (45 minutes)
1. Create AuthLayout.vue with two-column responsive layout
2. Implement MarketingSection.vue with construction industry content
3. Add hero messaging, feature highlights, and imagery
4. Ensure responsive behavior on all screen sizes

### Phase 2: Authentication Forms (1 hour)
1. Create LoginForm.vue with email/password fields
2. Create RegisterForm.vue with full registration fields
3. Create ForgotPasswordForm.vue for password reset
4. Implement client-side validation for all forms

### Phase 3: API Integration and State Management (45 minutes)
1. Setup axios configuration for Laravel Sanctum
2. Create authService.ts with API methods
3. Implement Pinia auth store with state management
4. Add token handling and request interceptors

### Phase 4: Testing and Refinement (30 minutes)
1. Test complete authentication flow with backend
2. Verify responsive design on multiple devices
3. Test error handling and validation
4. Performance optimization and final adjustments

## Acceptance Criteria
- [ ] Two-column responsive authentication layout implemented
- [ ] Marketing section with construction industry content
- [ ] Login, Register, and Forgot Password forms functional
- [ ] Integration with Laravel backend API working
- [ ] Pinia authentication store managing state
- [ ] Form validation with real-time feedback
- [ ] Error handling for API responses implemented
- [ ] Responsive design working on mobile and desktop
- [ ] Authentication token properly stored and used
- [ ] Route protection for authenticated users

## Dependencies
- **Depends On**: CONST-003 (UI components library), CONST-009 (Laravel backend)
- **Required**: Vue Router, Pinia, Axios, Form validation library

## Next Tasks
- **CONST-005**: Implement dashboard layout and navigation
- **CONST-006**: Add user profile management

## Risks & Mitigation
- **Risk**: CORS issues with Laravel Sanctum
  - **Mitigation**: Proper CORS configuration and Sanctum setup
- **Risk**: Form validation complexity
  - **Mitigation**: Use proven validation library and patterns
- **Risk**: Mobile responsive layout issues
  - **Mitigation**: Mobile-first approach and thorough testing

---
**Created**: 2024  
**Assignee**: Development Team  
**Branch**: `feature/CONST-004-authentication-layout`
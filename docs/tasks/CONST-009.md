# CONST-009: Setup Laravel Backend with Domain-Driven Design

**Task Type**: Backend Infrastructure  
**Estimated Time**: 3 hours  
**Priority**: High  
**Status**: In Progress  
**Branch**: `feature/CONST-009-laravel-backend-setup`  
**Depends On**: CONST-003

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified
- [x] Database changes planned
- [x] UI components identified (N/A - backend)
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Setup Laravel 11.x backend following Domain-Driven Design architecture with proper project structure, authentication, and API foundation as specified in claude.md.

### Functional Requirements
- Laravel 11.x with PHP 8.3+ support
- Domain-Driven Design architecture (User, Project, Task, Finance domains)
- Repository Pattern implementation (NO direct Eloquent in controllers)
- Service Layer for all business logic
- API Resources for all responses
- Form Requests for validation
- Laravel Sanctum for API authentication
- PostgreSQL database configuration
- Redis for caching and session management
- Laravel Horizon for queue management
- Port configuration (API: 3071, PostgreSQL: 3075, Redis: 3077, Horizon: 3079)

### API Endpoints to Implement

#### Authentication Endpoints
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User authentication  
- `POST /api/v1/auth/logout` - User logout
- `POST /api/v1/auth/forgot-password` - Password reset request
- `GET /api/v1/auth/me` - Current user profile

#### User Management
- `GET /api/v1/users` - List users
- `GET /api/v1/users/{user}` - Get user details
- `PUT /api/v1/users/{user}` - Update user profile

### Database Schema Planning

#### Users Table (PostgreSQL)
```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'field_worker',
    company_id UUID,
    avatar_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP NULL
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_company_id ON users(company_id);
CREATE INDEX idx_users_role ON users(role);
```

#### Companies Table
```sql
CREATE TABLE companies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    industry VARCHAR(100) DEFAULT 'construction',
    size VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP NULL
);
```

### Technical Specifications

#### Domain Structure (Following claude.md)
```
backend/app/Domain/
├── User/                    # User Domain
│   ├── Models/
│   │   ├── User.php
│   │   └── Company.php
│   ├── Services/
│   │   ├── UserService.php
│   │   └── AuthService.php
│   ├── Repositories/
│   │   ├── UserRepositoryInterface.php
│   │   └── UserRepository.php
│   ├── DTOs/
│   │   ├── CreateUserDTO.php
│   │   └── UpdateUserDTO.php
│   ├── Events/
│   │   └── UserRegistered.php
│   └── Jobs/
│       └── SendWelcomeEmail.php
├── Project/                 # Project Domain (future)
├── Task/                    # Task Domain (future)
└── Finance/                 # Finance Domain (future)
```

#### HTTP Layer Structure
```
backend/app/Http/
├── Controllers/Api/
│   └── AuthController.php
├── Middleware/
│   └── EnsureApiToken.php
├── Requests/
│   ├── RegisterRequest.php
│   ├── LoginRequest.php
│   └── ForgotPasswordRequest.php
└── Resources/
    └── UserResource.php
```

#### Service Implementation Example
```php
// app/Domain/User/Services/AuthService.php
class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Hash $hash
    ) {}

    public function register(CreateUserDTO $userData): User
    {
        return DB::transaction(function () use ($userData) {
            $user = $this->userRepository->create([
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => $this->hash->make($userData->password),
                'role' => $userData->role,
                'company_id' => $userData->companyId
            ]);

            event(new UserRegistered($user));
            SendWelcomeEmail::dispatch($user);

            return $user;
        });
    }
}
```

### Environment Configuration

#### Laravel Environment (.env)
```env
APP_NAME="Construction Management Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:3071

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=3075
DB_DATABASE=construction_platform
DB_USERNAME=postgres
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=3077

SANCTUM_STATEFUL_DOMAINS=localhost:3073
```

### Performance Considerations
- **Repository Pattern**: Prevents N+1 queries through proper eager loading
- **Redis Caching**: Aggressive caching for user sessions and frequently accessed data
- **Database Indexing**: Proper indexing on email, company_id, and role fields
- **Queue Management**: Background jobs for email sending and heavy operations
- **API Resources**: Consistent response formatting with optimized data structure

### Test Scenarios

#### Authentication Tests
1. **User Registration**
   - Valid data creates user and company association
   - Duplicate email returns validation error
   - Password is properly hashed
   - Welcome email job is dispatched

2. **User Login**
   - Valid credentials return access token
   - Invalid credentials return error
   - Rate limiting prevents brute force attacks
   - Token includes proper user data

3. **Password Reset**
   - Valid email sends reset token
   - Invalid email returns appropriate error
   - Reset token has proper expiration

#### Repository Pattern Tests
1. **User Repository**
   - Find by email works correctly
   - Create user with proper validation
   - Update user maintains data integrity
   - Soft delete preserves relationships

### Security Implementation
- **Password Hashing**: Bcrypt with appropriate cost factor
- **API Rate Limiting**: Throttle authentication endpoints
- **Input Sanitization**: All form requests validate and sanitize
- **CORS Configuration**: Proper CORS for frontend on port 3073
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **Authorization**: Role-based access control

### Installation Steps

#### Phase 1: Laravel Installation (45 minutes)
1. Install Laravel 11.x with Composer
2. Configure environment variables
3. Setup PostgreSQL connection (port 3075)
4. Configure Redis connection (port 3077)
5. Install required packages (Sanctum, Horizon)

#### Phase 2: Domain Architecture (1 hour)
1. Create Domain folder structure
2. Implement User domain (Models, Services, Repositories)
3. Setup Service Provider bindings
4. Create base interfaces and abstracts

#### Phase 3: API Layer (1 hour)
1. Create authentication controllers
2. Implement Form Requests for validation
3. Create API Resources for responses
4. Setup API routes with proper middleware

#### Phase 4: Database & Testing (15 minutes)
1. Create and run migrations
2. Setup database seeders
3. Run authentication tests
4. Verify API endpoints work correctly

## Acceptance Criteria
- [ ] Laravel 11.x installed and configured on port 3071
- [ ] Domain-Driven Design structure implemented
- [ ] Repository Pattern used throughout (no direct Eloquent in controllers)
- [ ] Service Layer handles all business logic
- [ ] API Resources used for all responses
- [ ] Form Requests handle all validation
- [ ] PostgreSQL configured on port 3075
- [ ] Redis configured on port 3077
- [ ] Laravel Sanctum authentication working
- [ ] All API endpoints return proper JSON responses
- [ ] Database migrations created and tested
- [ ] Authentication tests passing

## Dependencies
- **Depends On**: CONST-003 (Frontend components ready)
- **Required**: Composer, PHP 8.3+, PostgreSQL, Redis

## Next Tasks
- **CONST-004**: Frontend authentication implementation using these APIs

## Risks & Mitigation
- **Risk**: Domain structure complexity
  - **Mitigation**: Start with User domain, expand gradually
- **Risk**: Database connection issues
  - **Mitigation**: Use Laravel Sail for consistent environment
- **Risk**: Authentication token security
  - **Mitigation**: Proper Sanctum configuration with CORS

---
**Created**: 2024  
**Assignee**: Development Team  
**Branch**: `feature/CONST-009-laravel-backend-setup`
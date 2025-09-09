# CONST-005: User Management Module with Role-Based Access Control

**Task Type**: Feature Development - Frontend & Backend Integration  
**Estimated Time**: 4 hours  
**Priority**: High  
**Status**: Completed  
**Branch**: `feature/CONST-005-user-management-module`  
**Depends On**: CONST-004 (Authentication system completed)

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified  
- [x] Database changes planned
- [x] UI components identified
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Create a comprehensive User Management Module with role-based access control (RBAC) for the Construction Management Platform. This module will allow administrators to manage users, assign roles, and control permissions across the system.

### Functional Requirements

#### User Management Features
- **User List**: Paginated data table with sorting, filtering, and search
- **User Profile**: Detailed view/edit for user information
- **Role Management**: Assign and manage user roles (admin, project_manager, supervisor, field_worker)
- **Permission System**: Role-based permissions for different system features
- **User Creation**: Admin ability to create new users
- **User Status**: Enable/disable users, email verification status
- **Avatar Management**: Upload and manage user profile pictures

#### Role-Based Access Control (RBAC)
- **Admin**: Full system access, user management, company settings
- **Project Manager**: Project oversight, team management, reporting
- **Supervisor**: Field supervision, task assignment, worker management  
- **Field Worker**: Task execution, progress reporting, basic project access

### API Endpoints to Implement

#### Backend (Laravel) - Already exists, need to verify/enhance
```php
// User Management Endpoints
GET    /api/v1/users                    # List users with pagination/filters
POST   /api/v1/users                    # Create new user (admin only)
GET    /api/v1/users/{user}            # Get user details
PUT    /api/v1/users/{user}            # Update user profile
DELETE /api/v1/users/{user}            # Soft delete user (admin only)
POST   /api/v1/users/{user}/avatar     # Upload user avatar
POST   /api/v1/users/{user}/resend     # Resend email verification

// Current User Endpoints
GET    /api/v1/auth/me                 # Get current user profile
PUT    /api/v1/auth/me/profile         # Update current user profile
PUT    /api/v1/auth/me/password        # Change current user password
```

#### Frontend Module Structure
```
src/modules/users/
├── api/users.api.ts              # API service layer
├── stores/users.store.ts         # Pinia store for user management
├── composables/
│   ├── useUsers.ts              # Main user management composable
│   ├── useUserPermissions.ts    # Role-based permission checking
│   └── useUserValidation.ts     # User form validation
├── types/users.types.ts         # User management TypeScript types
├── constants/users.constants.ts # User role definitions and permissions
├── components/
│   ├── UserList.vue            # Main user list with data table
│   ├── UserForm.vue            # User create/edit form
│   ├── UserProfile.vue         # User profile display
│   ├── RoleBadge.vue          # Role display component
│   └── PermissionGuard.vue     # Permission-based rendering
└── index.ts                    # Module exports
```

### Database Changes (Backend - Already exists)
The User model already exists with proper role structure:
```sql
-- Users table already has:
role VARCHAR(50) NOT NULL DEFAULT 'field_worker'
company_id UUID (for multi-tenant support)
```

### UI Components Required

#### Data Table Implementation (Following claude.md standards)
```typescript
// ✅ GOOD - Proper data table with all requirements
const columns: ColumnDef[] = [
  {
    key: 'name',
    label: 'Name',
    sortable: true,
    width: '200px'
  },
  {
    key: 'email', 
    label: 'Email',
    sortable: true,
    width: '250px'
  },
  {
    key: 'role',
    label: 'Role', 
    sortable: true,
    cell: ({ row }) => (
      <RoleBadge role={row.role} />
    )
  },
  {
    key: 'company',
    label: 'Company',
    sortable: true,
    cell: ({ row }) => row.company?.name || 'N/A'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: true,
    cell: ({ row }) => (
      <Badge variant={row.email_verified_at ? 'success' : 'warning'}>
        {row.email_verified_at ? 'Verified' : 'Pending'}
      </Badge>
    )
  },
  {
    key: 'created_at',
    label: 'Created',
    sortable: true,
    cell: ({ row }) => new Date(row.created_at).toLocaleDateString()
  },
  {
    key: 'actions',
    label: '',
    width: '100px',
    cell: ({ row }) => (
      <UserActionsDropdown user={row} />
    )
  }
];
```

#### Required UI Components (using /components/ui/)
- **DataTable**: Main user listing (MANDATORY per claude.md)
- **Dialog**: User create/edit forms
- **Badge**: Role and status display
- **DropdownMenu**: User actions menu
- **Avatar**: User profile pictures
- **Select**: Role selection dropdown
- **Input**: Form fields with validation
- **Button**: Action buttons

### Role Permission Mapping

```typescript
export const ROLE_PERMISSIONS: Record<UserRole, RolePermissions> = {
  admin: {
    canManageUsers: true,
    canViewUsers: true, 
    canEditUser: true,
    canDeleteUser: true,
    canManageProjects: true,
    canViewProjects: true,
    canAssignTasks: true,
    canViewReports: true,
    canManageCompany: true,
  },
  project_manager: {
    canManageUsers: false,
    canViewUsers: true,
    canEditUser: false, 
    canDeleteUser: false,
    canManageProjects: true,
    canViewProjects: true,
    canAssignTasks: true,
    canViewReports: true,
    canManageCompany: false,
  },
  // ... supervisor and field_worker roles
}
```

### Performance Considerations

#### Backend Optimizations
- **Eager Loading**: Load company relationships to prevent N+1 queries
- **Database Indexing**: Index on role, company_id, email for fast filtering
- **Caching**: Cache user permissions and role data
- **Pagination**: Default 50 users per page with configurable options

#### Frontend Optimizations  
- **Virtual Scrolling**: For large user lists (if needed)
- **Debounced Search**: 300ms delay for search input
- **Lazy Loading**: Route-level code splitting
- **Computed Properties**: Efficient permission checking

### Test Scenarios

#### Backend Tests (Laravel)
1. **User CRUD Operations**
   - Admin can create users with different roles
   - Admin can update user profiles and roles
   - Admin can soft delete users
   - Users can update their own profiles
   - Users cannot edit other users

2. **Permission System**
   - Role-based access to user endpoints
   - Proper authorization checks
   - Company-based user filtering

3. **API Response Format**
   - Consistent API resource formatting
   - Proper pagination meta data
   - Error handling for invalid requests

#### Frontend Tests (Vue.js)
1. **User List Component**
   - Displays users in data table format
   - Sorting works on all columns
   - Filtering and search functionality
   - Pagination controls work correctly

2. **Permission System**
   - Role-based UI element visibility
   - Proper form field access control
   - Action button permissions

3. **User Forms**
   - Validation rules work correctly
   - Form submission handles success/error
   - Role selection updates properly

### Security Implementation

#### Backend Security
- **Authorization Policies**: Role-based access to user endpoints
- **Input Validation**: Comprehensive Form Request validation
- **Rate Limiting**: Protect user creation and update endpoints
- **Company Isolation**: Users only see users in their company
- **Password Hashing**: Secure password storage with bcrypt

#### Frontend Security
- **Permission Guards**: Component-level permission checking
- **Form Validation**: Client-side input validation
- **Secure File Upload**: Avatar upload with type/size restrictions
- **XSS Prevention**: Proper output escaping

## Implementation Plan

### Phase 1: Frontend Module Setup (30 minutes)
1. Create user module directory structure
2. Define TypeScript interfaces and types
3. Create user API service layer
4. Setup Pinia store for user state management

### Phase 2: User List Implementation (1.5 hours)
1. Create UserList.vue with DataTable component
2. Implement sorting, filtering, and pagination
3. Add inline filters for role and company
4. Create user action dropdown menu

### Phase 3: User Forms (1 hour) 
1. Create UserForm.vue for create/edit operations
2. Implement form validation and error handling
3. Add role selection and company assignment
4. Handle avatar upload functionality

### Phase 4: Permission System (1 hour)
1. Create permission checking composables
2. Implement PermissionGuard component
3. Add role-based UI element visibility
4. Create RoleBadge display component

### Phase 5: Integration & Testing (30 minutes)
1. Connect to existing auth system
2. Test all CRUD operations
3. Verify permission system works
4. Ensure responsive design compliance

## Acceptance Criteria

- [x] User list displays in DataTable format with all required columns
- [x] All columns are sortable (mandatory per claude.md)
- [x] Inline filters for search, role, and status work correctly
- [x] Pagination shows 50 users per page with configurable options
- [x] Role-based permissions control UI element visibility
- [x] Admin users can create, edit, and delete users
- [x] Users can update their own profiles
- [x] Avatar upload and display works correctly
- [x] Form validation prevents invalid submissions
- [x] Company-based user isolation works properly
- [x] Responsive design works on mobile and desktop
- [x] TypeScript strict mode compliance
- [x] No inline styles, only Tailwind utilities
- [x] All UI components use /components/ui/ library

## Dependencies

- **Depends On**: CONST-004 (Authentication system)
- **Required**: Backend User domain (already exists)
- **UI Components**: DataTable, Dialog, Badge, DropdownMenu, Avatar

## Next Tasks

- **CONST-006**: Project Management Module (uses user assignment)
- **CONST-007**: Task Assignment Module (requires user roles)

## Risks & Mitigation

- **Risk**: Complex permission system
  - **Mitigation**: Start with basic RBAC, expand gradually
- **Risk**: Large user lists performance
  - **Mitigation**: Implement pagination, search, and filtering first
- **Risk**: UI component complexity
  - **Mitigation**: Use existing UI components from /components/ui/

---
**Created**: 2024  
**Last Updated**: 2024  
**Assignee**: Development Team  
**Branch**: `feature/CONST-005-user-management-module`
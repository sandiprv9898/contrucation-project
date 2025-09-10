# CONST-006: System Settings Module with Configuration Management

**Task Type**: Feature Development - Frontend Module  
**Estimated Time**: 6 hours  
**Priority**: Medium  
**Status**: Completed  
**Branch**: `feature/CONST-006-system-settings-module`  
**Depends On**: CONST-005 (User Management Module completed)

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified  
- [x] Database changes planned
- [x] UI components identified
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Create a comprehensive System Settings Module that allows administrators to configure various aspects of the Construction Management Platform. This module will provide centralized configuration management for company settings, system preferences, notifications, security policies, and backup configurations.

### Functional Requirements

#### Settings Categories
1. **Company/Organization Settings**
   - Company profile (name, logo, contact information)
   - Branding customization (colors, themes)
   - Legal and compliance information
   - Financial preferences (currency, tax settings)

2. **System Preferences** 
   - Language and localization settings
   - Date/time formats and timezone configuration
   - Measurement system (imperial/metric)
   - UI preferences and default values

3. **Notification Settings**
   - Email, SMS, and push notification preferences
   - Event-specific notification rules
   - Notification frequency and timing controls
   - Channel configuration and management

4. **Security Settings**
   - Password policy enforcement
   - Two-factor authentication configuration
   - Session management and timeout settings
   - IP access restrictions and API security

5. **Backup & Maintenance**
   - Automatic backup scheduling
   - Storage configuration (local/cloud)
   - Maintenance mode controls
   - System monitoring and health checks

### API Endpoints to Implement

#### Backend (Laravel) - New endpoints needed
```php
// Settings Management Endpoints
GET    /api/v1/settings                    # Get all settings (categorized)
PUT    /api/v1/settings/{category}         # Update settings by category
POST   /api/v1/settings/import            # Import settings from file
GET    /api/v1/settings/export            # Export settings to file
GET    /api/v1/settings/permissions       # Get setting permissions by role
GET    /api/v1/settings/validations       # Get validation rules

// Company Settings
PUT    /api/v1/settings/company           # Update company settings
POST   /api/v1/settings/company/logo     # Upload company logo

// System Health & Maintenance
GET    /api/v1/settings/system/health     # System health check
POST   /api/v1/settings/system/maintenance # Toggle maintenance mode
```

#### Frontend Module Structure
```
src/modules/settings/
├── api/settings.api.ts              # API service layer
├── stores/settings.store.ts         # Pinia store for settings management
├── composables/
│   ├── useSettings.ts              # Main settings management composable
│   └── useSettingsValidation.ts    # Settings form validation
├── types/settings.types.ts         # Settings TypeScript interfaces
├── constants/settings.constants.ts # Default values and configurations
├── components/
│   ├── SettingsLayout.vue         # Main settings container with navigation
│   ├── CompanySettings.vue        # Company/organization configuration
│   ├── SystemPreferences.vue      # System preferences panel
│   ├── NotificationSettings.vue   # Notification configuration
│   ├── SecuritySettings.vue       # Security policies and authentication
│   ├── BackupSettings.vue         # Backup and maintenance settings
│   └── SettingsCard.vue          # Reusable settings section component
└── index.ts                       # Module exports
```

### Database Changes (Backend)

#### New Tables Required
```sql
-- Settings table for storing configuration
CREATE TABLE settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    category VARCHAR(50) NOT NULL,
    key VARCHAR(100) NOT NULL,
    value JSONB NOT NULL,
    company_id UUID REFERENCES companies(id),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_by UUID REFERENCES users(id),
    
    UNIQUE(category, key, company_id),
    INDEX idx_settings_category (category),
    INDEX idx_settings_company (company_id)
);

-- Settings audit log for change tracking
CREATE TABLE settings_audit_log (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    setting_id UUID REFERENCES settings(id),
    category VARCHAR(50) NOT NULL,
    key VARCHAR(100) NOT NULL,
    old_value JSONB,
    new_value JSONB NOT NULL,
    changed_by UUID REFERENCES users(id),
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);
```

### UI Components Required

#### Settings Navigation Layout
```typescript
// ✅ GOOD - Proper settings layout with sidebar navigation
const settingsNavigation = [
  {
    category: 'company',
    label: 'Company',
    icon: Building2,
    component: CompanySettings,
    requiredRole: 'admin'
  },
  {
    category: 'system', 
    label: 'System',
    icon: Settings,
    component: SystemPreferences,
    requiredRole: 'admin'
  },
  {
    category: 'notifications',
    label: 'Notifications', 
    icon: Bell,
    component: NotificationSettings,
    requiredRole: 'project_manager'
  },
  {
    category: 'security',
    label: 'Security',
    icon: Shield,
    component: SecuritySettings,
    requiredRole: 'admin'
  },
  {
    category: 'backup',
    label: 'Backup',
    icon: HardDrive,
    component: BackupSettings,
    requiredRole: 'admin'
  }
];
```

#### Required UI Components (using /components/ui/)
- **Tabs**: Settings category navigation
- **Card**: Section containers for grouped settings
- **Switch**: Boolean toggle settings
- **Select**: Dropdown selections (timezone, language, etc.)
- **Input**: Text and number input fields
- **Button**: Save, reset, and action buttons
- **ColorPicker**: Branding color selection
- **FileUpload**: Logo and document uploads
- **Badge**: Status indicators
- **Alert**: Success/error messages
- **Dialog**: Confirmation dialogs for destructive actions

### Settings Permission System

```typescript
export const SETTINGS_PERMISSIONS: Record<SettingCategory, SettingsPermission[]> = {
  company: [
    {
      setting_key: 'company.profile',
      required_role: 'admin',
      can_read: true,
      can_write: true
    },
    {
      setting_key: 'company.branding',
      required_role: 'admin', 
      can_read: true,
      can_write: true
    }
  ],
  system: [
    {
      setting_key: 'system.localization',
      required_role: 'project_manager',
      can_read: true,
      can_write: false
    }
  ],
  security: [
    {
      setting_key: 'security.password_policy',
      required_role: 'admin',
      can_read: true,
      can_write: true
    }
  ]
  // ... other categories
};
```

### Performance Considerations

#### Backend Optimizations
- **Caching**: Cache settings in Redis with 1-hour TTL
- **Company Isolation**: Scope all settings to company context
- **Bulk Operations**: Allow bulk setting updates in single transaction
- **Validation Caching**: Cache validation rules to prevent repeated DB queries

#### Frontend Optimizations
- **Lazy Loading**: Load setting categories on demand
- **Debounced Saves**: Auto-save with 2-second debounce for form changes
- **Optimistic Updates**: Update UI immediately, rollback on error
- **State Persistence**: Preserve unsaved changes during navigation

### Test Scenarios

#### Backend Tests (Laravel)
1. **Settings CRUD Operations**
   - Admin can read/write all settings categories
   - Project managers have limited access to certain settings
   - Settings are properly scoped to company
   - Audit log records all changes

2. **Permission System**
   - Role-based access control for each setting category
   - Company isolation prevents cross-tenant access
   - Proper authorization checks on all endpoints

3. **Validation System**
   - Settings validation rules prevent invalid configurations
   - Dependencies between settings are properly enforced
   - File uploads are validated for type and size

#### Frontend Tests (Vue.js)
1. **Settings Navigation**
   - Sidebar navigation works correctly
   - Role-based visibility of setting categories
   - Deep linking to specific settings sections

2. **Form Interactions**
   - All form controls work properly
   - Real-time validation provides immediate feedback
   - Auto-save functionality works as expected
   - Unsaved changes warning appears on navigation

3. **Permission Integration**
   - Settings forms respect role-based permissions
   - Read-only mode for insufficient permissions
   - Proper error handling for unauthorized actions

### Security Implementation

#### Backend Security
- **Authorization Policies**: Role-based access to settings endpoints
- **Input Validation**: Comprehensive validation for all setting types
- **Audit Logging**: Track all setting changes with user context
- **Company Isolation**: Ensure settings cannot leak between companies
- **File Security**: Validate uploaded files and scan for malware

#### Frontend Security
- **Permission Guards**: Component-level permission checking
- **Input Sanitization**: Prevent XSS in user-provided settings
- **Secure File Upload**: Validate file types and sizes before upload
- **Form Validation**: Client-side validation with server-side verification

## Implementation Plan

### Phase 1: Backend Foundation (1.5 hours)
1. Create settings database tables and migrations
2. Implement Settings model with company scoping
3. Create settings API endpoints with proper authorization
4. Add validation rules and error handling

### Phase 2: Frontend Module Setup (1 hour)
1. Create settings module directory structure
2. Define TypeScript interfaces and types
3. Create settings API service layer
4. Setup Pinia store for settings state management

### Phase 3: Main Settings Layout (1 hour)
1. Create SettingsLayout.vue with sidebar navigation
2. Implement responsive design for mobile/tablet
3. Add permission-based navigation filtering
4. Create breadcrumb navigation system

### Phase 4: Individual Settings Pages (2 hours)
1. Implement CompanySettings.vue with company profile forms
2. Create SystemPreferences.vue for localization and UI settings
3. Build NotificationSettings.vue for notification configuration
4. Add SecuritySettings.vue for security policy management
5. Implement BackupSettings.vue for backup configuration

### Phase 5: Advanced Features (30 minutes)
1. Add settings import/export functionality
2. Implement auto-save with unsaved changes tracking
3. Create settings validation and error handling
4. Add confirmation dialogs for destructive actions

## Acceptance Criteria

- [x] Settings are organized into clear categories with intuitive navigation
- [x] All settings forms use proper validation with real-time feedback
- [x] Role-based permissions control access to different setting categories
- [x] Company logo upload and branding customization works correctly
- [x] Auto-save functionality prevents data loss during form editing
- [x] Settings import/export allows configuration backup/restore
- [x] Responsive design works properly on mobile and desktop devices
- [x] All settings are properly scoped to company context
- [x] Audit logging tracks all setting changes with user attribution
- [x] TypeScript strict mode compliance throughout the module
- [x] No inline styles, only Tailwind utility classes used
- [x] All UI components use /components/ui/ library consistently

## Dependencies

- **Depends On**: CONST-005 (User Management Module)
- **Required**: Authentication system for permission checking
- **UI Components**: Card, Switch, Select, Input, Button, ColorPicker, FileUpload

## Next Tasks

- **CONST-007**: Project Management Module (may use company settings)
- **CONST-008**: Notification System (uses notification settings)

## Risks & Mitigation

- **Risk**: Complex permission system across different settings
  - **Mitigation**: Start with basic role-based permissions, expand gradually
- **Risk**: Large number of settings affecting performance
  - **Mitigation**: Implement proper caching and lazy loading strategies  
- **Risk**: Settings validation complexity
  - **Mitigation**: Use centralized validation system with clear error messages
- **Risk**: File upload security concerns
  - **Mitigation**: Implement proper file validation and virus scanning

---
**Created**: September 10, 2025  
**Last Updated**: September 10, 2025  
**Completed**: September 10, 2025  
**Assignee**: Development Team  
**Branch**: `feature/CONST-006-system-settings-module`
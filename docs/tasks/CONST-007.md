# CONST-007: Company Settings Module Enhancement & Profile Management

**Task Type**: Feature Development - Company Profile Module  
**Estimated Time**: 4 hours  
**Priority**: Medium  
**Status**: Pending Approval  
**Branch**: `feature/CONST-007-company-settings-module`  
**Depends On**: CONST-006 (System Settings Module completed)

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified  
- [x] Database changes planned
- [x] UI components identified
- [x] Test scenarios defined
- [x] Performance impact assessed

## Requirements Documentation

### Objective
Enhance the existing Company Settings functionality within the System Settings Module to create a comprehensive Company Profile Management system. This will allow administrators to configure company information, branding, contact details, and business preferences for the Construction Management Platform.

### Functional Requirements

#### Company Profile Management
1. **Basic Company Information**
   - Company name, registration number, tax ID
   - Business type and industry classification
   - Founded date and company size
   - Official business address and contact information

2. **Branding & Customization** 
   - Company logo upload and management
   - Primary and secondary brand colors
   - Custom favicon and loading screen
   - Email signature templates

3. **Contact & Communication**
   - Primary contact information (phone, email, website)
   - Social media links and professional profiles
   - Emergency contact details
   - Preferred communication channels

4. **Business Preferences**
   - Default currency and tax rates
   - Operating hours and time zones
   - Preferred measurement units (metric/imperial)
   - Industry-specific settings

5. **Legal & Compliance**
   - Business licenses and certifications
   - Insurance information
   - Terms of service and privacy policy links
   - Regulatory compliance settings

### Current State Analysis

Based on the existing settings system, the company category already includes:
- Basic company information (name, email, phone, address)
- Logo upload functionality
- Primary and secondary colors
- Currency and tax rate settings
- Timezone configuration

### Enhancements Needed

1. **Expand Company Information Fields**
   - Add business registration details
   - Company size and industry type
   - Founded date and business description

2. **Enhanced Logo Management**
   - Multiple logo formats (light/dark themes)
   - Logo resizing and cropping tools
   - Favicon generation from logo

3. **Advanced Branding**
   - Color palette management (beyond primary/secondary)
   - Custom CSS injection capabilities
   - Theme previews and customization

4. **Business Profile**
   - Company portfolio and project galleries
   - Team member highlights
   - Certifications and awards showcase

## API Endpoints Analysis

### Existing Endpoints (Already Implemented)
```php
// Basic company settings
PUT    /api/v1/settings/company           # Update company settings
POST   /api/v1/settings/company/logo     # Upload company logo
POST   /api/v1/settings/company/reset    # Reset to defaults
```

### New Endpoints Needed
```php
// Enhanced company profile
GET    /api/v1/company/profile           # Get complete company profile
PUT    /api/v1/company/profile           # Update company profile
POST   /api/v1/company/logo/favicon      # Generate favicon from logo
POST   /api/v1/company/branding/preview  # Preview branding changes
GET    /api/v1/company/industry-types    # Get industry classification options
POST   /api/v1/company/portfolio         # Manage company portfolio items
```

## Database Changes Required

### Existing Tables
The `settings` table already handles basic company settings through the settings system.

### New Tables Needed
```sql
-- Company profiles table (extended information)
CREATE TABLE company_profiles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id) ON DELETE CASCADE,
    business_registration VARCHAR(100),
    tax_identification VARCHAR(100),
    industry_type VARCHAR(50),
    company_size ENUM('startup', 'small', 'medium', 'large', 'enterprise'),
    founded_date DATE,
    description TEXT,
    website VARCHAR(255),
    social_media JSONB,
    certifications JSONB,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    
    UNIQUE(company_id)
);

-- Company branding assets
CREATE TABLE company_branding (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id) ON DELETE CASCADE,
    asset_type VARCHAR(50) NOT NULL, -- logo, favicon, banner, etc.
    asset_variant VARCHAR(50), -- light, dark, color, mono
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER,
    mime_type VARCHAR(100),
    dimensions JSONB, -- {width: 200, height: 100}
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    
    INDEX idx_company_branding_company (company_id),
    INDEX idx_company_branding_type (asset_type, asset_variant)
);

-- Company portfolio/showcase items
CREATE TABLE company_portfolio (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50), -- project, certification, award, team
    image_path VARCHAR(500),
    external_url VARCHAR(500),
    display_order INTEGER DEFAULT 0,
    is_featured BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    metadata JSONB,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    
    INDEX idx_company_portfolio_company (company_id),
    INDEX idx_company_portfolio_category (category),
    INDEX idx_company_portfolio_featured (is_featured, display_order)
);
```

## Frontend Module Structure

### Enhanced Components
```
src/modules/settings/components/
├── CompanySettings.vue              # Enhanced existing component
├── company/
│   ├── CompanyProfileForm.vue       # Basic company information
│   ├── CompanyBrandingPanel.vue     # Logo, colors, themes
│   ├── CompanyContactForm.vue       # Contact and social media
│   ├── CompanyPreferencesForm.vue   # Business preferences
│   ├── CompanyLegalForm.vue         # Legal and compliance
│   ├── CompanyPortfolioManager.vue  # Portfolio/showcase management
│   ├── LogoUploadWidget.vue         # Advanced logo management
│   ├── ColorPaletteEditor.vue       # Advanced color management
│   └── BrandingPreview.vue          # Real-time branding preview
```

### New Service Files
```
src/modules/company/
├── api/
│   ├── company.api.ts               # Company profile API
│   ├── branding.api.ts              # Branding and assets API
│   └── portfolio.api.ts             # Portfolio management API
├── types/
│   ├── company.types.ts             # Company profile interfaces
│   ├── branding.types.ts            # Branding and theme types
│   └── portfolio.types.ts           # Portfolio item types
├── stores/
│   ├── company.store.ts             # Company profile state
│   └── branding.store.ts            # Branding customization state
├── composables/
│   ├── useCompanyProfile.ts         # Company profile management
│   ├── useBranding.ts               # Branding customization
│   └── useLogoUpload.ts             # Advanced logo upload
└── constants/
    ├── industry-types.ts            # Industry classification
    └── branding-defaults.ts         # Default branding options
```

## Implementation Plan

### Phase 1: Backend Foundation (1.5 hours)
1. **Database Setup**
   - Create company_profiles migration
   - Create company_branding migration  
   - Create company_portfolio migration
   - Add indexes and relationships

2. **Models and Relationships**
   - Create CompanyProfile model with relationships
   - Create CompanyBranding model with file management
   - Create CompanyPortfolio model
   - Update Company model relationships

3. **API Controllers**
   - Create CompanyProfileController
   - Create CompanyBrandingController
   - Enhance existing SettingController company methods

### Phase 2: Enhanced Settings Integration (1 hour)
1. **Service Layer Enhancement**
   - Extend SettingService for company profile
   - Create CompanyProfileService
   - Create BrandingService with asset management

2. **Validation and Security**
   - Create form request classes for company data
   - Add file upload validation for branding assets
   - Implement permission-based access control

### Phase 3: Frontend Enhancement (1.5 hours)
1. **Component Development**
   - Enhance existing CompanySettings.vue with tabs
   - Create sub-components for different sections
   - Implement advanced logo upload with preview
   - Create color palette management interface

2. **State Management**
   - Extend settings store for company profile
   - Create branding store for theme management
   - Implement real-time preview functionality

3. **API Integration**
   - Create company profile API service
   - Implement file upload for branding assets
   - Add form validation and error handling

### Phase 4: Advanced Features (1 hour)
1. **Branding System**
   - Logo variant management (light/dark themes)
   - Real-time branding preview
   - Custom CSS injection capability
   - Favicon generation from logo

2. **Portfolio Management**
   - Company showcase/portfolio CRUD
   - Image gallery for projects
   - Drag-and-drop reordering
   - Featured items management

3. **Integration Testing**
   - Test all CRUD operations
   - Verify file upload functionality  
   - Test branding preview system
   - Validate permission controls

## Acceptance Criteria

### Core Functionality
- [ ] Company profile information can be updated and persists correctly
- [ ] Logo upload supports multiple formats (PNG, JPG, SVG) with size validation
- [ ] Color palette changes are reflected in real-time preview
- [ ] All form fields have proper validation with error messaging
- [ ] File uploads are secure and properly validated
- [ ] Company branding is applied consistently across the application

### Advanced Features  
- [ ] Multiple logo variants (light/dark) can be uploaded and managed
- [ ] Favicon is automatically generated from uploaded logo
- [ ] Portfolio items can be added, edited, reordered, and deleted
- [ ] Real-time preview shows branding changes before saving
- [ ] Social media links and contact information are properly formatted
- [ ] Industry type selection from predefined options

### Technical Requirements
- [ ] All database operations use proper transactions
- [ ] File uploads are stored securely with proper permissions
- [ ] API responses follow consistent format and include validation errors
- [ ] Frontend state is properly managed and synchronized
- [ ] All components use TypeScript with proper type definitions
- [ ] UI follows the established design system and patterns

### Security & Performance
- [ ] File uploads are validated for type, size, and content
- [ ] User permissions are properly enforced for company settings
- [ ] Image optimization is applied for uploaded assets
- [ ] Caching is implemented for frequently accessed company data
- [ ] All sensitive company information is properly protected

## Dependencies

- **Depends On**: CONST-006 (System Settings Module) - Uses existing settings infrastructure
- **Required**: File upload system for branding assets
- **UI Components**: FileUpload, ColorPicker, ImageCropper, TabNavigation
- **Backend Services**: File storage, image processing, caching system

## Next Tasks

- **CONST-008**: Project Management Module (will use company branding)
- **CONST-009**: User Profile Management (may reference company information)

## Risks & Mitigation

### Technical Risks
- **Risk**: Large file uploads affecting performance
  - **Mitigation**: Implement file size limits, image compression, and background processing
- **Risk**: Complex branding preview system
  - **Mitigation**: Start with basic preview, expand incrementally
- **Risk**: File storage and security concerns
  - **Mitigation**: Use secure file storage with proper validation and scanning

### Business Risks
- **Risk**: Too many customization options overwhelming users
  - **Mitigation**: Provide sensible defaults and progressive disclosure
- **Risk**: Branding changes affecting application consistency
  - **Mitigation**: Implement preview system and rollback capabilities

## Estimated Development Breakdown

| Phase | Task | Time | Dependencies |
|-------|------|------|--------------|
| 1 | Database setup and models | 0.5h | None |
| 1 | API controllers and routes | 1.0h | Models |
| 2 | Service layer and validation | 1.0h | Controllers |
| 3 | Frontend components | 1.0h | API |
| 3 | State management | 0.5h | Components |
| 4 | Advanced branding features | 0.5h | Basic functionality |
| 4 | Portfolio management | 0.5h | Branding system |
| 4 | Testing and integration | 0.5h | All features |

**Total Estimated Time**: 4.5 hours

---
**Created**: September 10, 2025  
**Status**: Pending Approval  
**Assignee**: Development Team  
**Branch**: `feature/CONST-007-company-settings-module`

## Approval Request

This task builds upon the successfully completed CONST-006 System Settings Module by enhancing the company settings functionality. The implementation will:

1. **Extend existing infrastructure** - Leverages the current settings system
2. **Add comprehensive company profile management** - Beyond basic settings
3. **Implement advanced branding system** - Logo variants, color management, previews
4. **Create portfolio/showcase functionality** - Company presentation capabilities
5. **Ensure security and performance** - Proper file handling and validation

**Please review and approve to proceed with implementation.**
# CONST-007: Company Settings Module Enhancement & Profile Management

**Task Type**: Feature Development - Company Profile Module  
**Estimated Time**: 4 hours  
**Priority**: Medium  
**Status**: ✅ COMPLETED  
**Branch**: `feature/CONST-007-company-settings-module`  
**Depends On**: CONST-006 (System Settings Module completed)

## Task Analysis Checklist

- [x] Requirements documented
- [x] API endpoints identified  
- [x] Database changes planned
- [x] UI components identified
- [x] Test scenarios defined
- [x] Performance impact assessed
- [x] ✅ IMPLEMENTATION COMPLETED

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
- [x] ✅ Company profile information can be updated and persists correctly
- [x] ✅ Logo upload supports multiple formats (PNG, JPG, SVG) with size validation
- [x] ✅ Color palette changes are reflected in real-time preview
- [x] ✅ All form fields have proper validation with error messaging
- [x] ✅ File uploads are secure and properly validated
- [x] ✅ Company branding is applied consistently across the application

### Advanced Features  
- [x] ✅ Multiple logo variants (light/dark) can be uploaded and managed
- [x] ✅ Favicon is automatically generated from uploaded logo
- [x] ✅ Portfolio items can be added, edited, reordered, and deleted
- [x] ✅ Real-time preview shows branding changes before saving
- [x] ✅ Social media links and contact information are properly formatted
- [x] ✅ Industry type selection from predefined options

### Technical Requirements
- [x] ✅ All database operations use proper transactions
- [x] ✅ File uploads are stored securely with proper permissions
- [x] ✅ API responses follow consistent format and include validation errors
- [x] ✅ Frontend state is properly managed and synchronized
- [x] ✅ All components use TypeScript with proper type definitions
- [x] ✅ UI follows the established design system and patterns

### Security & Performance
- [x] ✅ File uploads are validated for type, size, and content
- [x] ✅ User permissions are properly enforced for company settings
- [x] ✅ Image optimization is applied for uploaded assets
- [x] ✅ Caching is implemented for frequently accessed company data
- [x] ✅ All sensitive company information is properly protected

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

## ✅ IMPLEMENTATION COMPLETED

**Task completed on**: September 10, 2025  
**Implementation Duration**: 4 hours (as estimated)  
**Final Status**: ✅ FULLY IMPLEMENTED AND FUNCTIONAL

### What Was Delivered

#### 🗄️ Backend Implementation
- **Database Schema**: Created 3 new tables (`company_profiles`, `company_branding`, `company_portfolio`)
- **Domain Models**: Implemented `CompanyProfile`, `CompanyBranding`, `CompanyPortfolio` models with relationships
- **API Controllers**: Built `CompanyProfileController`, `CompanyBrandingController`, `CompanyPortfolioController`
- **API Resources**: Created transformation resources for consistent API responses
- **Request Validation**: Implemented form request classes for data validation
- **Route Registration**: All company management routes properly registered

#### 🎨 Frontend Implementation  
- **Main Interface**: Replaced placeholder "Coming soon..." with fully functional Company Settings interface
- **Tab-Based Layout**: Organized settings into 5 main categories (Basic, Branding, Contact, Portfolio, Legal)
- **Component Architecture**: Built 9 specialized components following the design system
- **File Upload System**: Logo upload with multiple variants (light/dark themes)
- **Color Management**: Advanced color palette picker with real-time preview
- **Portfolio Management**: Complete CRUD for company showcase items
- **Auto-Save**: Implemented debounced auto-save with manual save options
- **Real-Time UI**: Instant feedback for changes with proper loading states

#### 🔧 Enhanced Features
- **Multi-Logo Support**: Primary logo, light/dark variants, favicon generation
- **Brand Guidelines**: Company statement, values, logo usage guidelines  
- **Social Media Integration**: LinkedIn, Twitter, Facebook, Instagram profiles
- **Certifications Management**: Dynamic certification/license tracking
- **Typography Controls**: Primary and secondary font selection
- **Industry Classification**: Construction-specific industry types
- **Company Sizing**: Startup to Enterprise classification system

#### 🛡️ Security & Quality
- **File Validation**: Secure upload with type, size, and content validation
- **Permission System**: Role-based access control for settings management
- **Transaction Safety**: Database operations wrapped in transactions
- **Input Sanitization**: All user inputs properly validated and sanitized
- **Error Handling**: Comprehensive error handling with user-friendly messages
- **Caching Strategy**: Optimized performance with intelligent caching

### Live Interface Location
**URL**: `http://localhost:3073/admin/company`  
**Access**: Available to admin and project_manager roles

The Company Settings page now provides a comprehensive interface for managing:
- Company profile and business information
- Brand assets (logos, colors, typography)
- Social media and contact details
- Portfolio/showcase management
- Legal compliance and certifications

All planned features have been successfully implemented and are fully functional. The interface follows the project's design system with construction-industry specific patterns and mobile-friendly design for field use.
# Dynamic Company Settings System

A flexible, configurable company settings system that can be customized without modifying core components.

## 🚀 Features

- **Dynamic Configuration**: Define forms through configuration files
- **Environment-Specific Settings**: Different configs for dev/prod
- **Runtime Customization**: Override settings via localStorage
- **Type-Safe**: Full TypeScript support
- **Field Validation**: Built-in validation system
- **Auto-Save**: Configurable auto-save functionality
- **Progress Tracking**: Visual progress indicators
- **Responsive Design**: Works on all screen sizes
- **Permission System**: Role-based access control

## 📁 Project Structure

```
src/modules/settings/
├── components/
│   ├── DynamicField.vue           # Renders individual form fields
│   ├── DynamicCompanySettings.vue # Main dynamic settings component
│   └── CompanySettings.vue        # Original static component (legacy)
├── config/
│   ├── company-settings.config.ts # Main configuration file
│   └── environments/
│       ├── production.config.ts   # Production overrides
│       └── development.config.ts  # Development overrides
└── README.md                      # This file
```

## 🎛️ Configuration System

### Basic Configuration

Edit `config/company-settings.config.ts` to customize the form:

```typescript
export const defaultCompanySettingsConfig: CompanySettingsConfig = {
  title: "Company Settings",
  subtitle: "Manage your company profile, branding, and showcase",
  
  tabs: [
    {
      id: 'basic',
      name: 'Basic Info',
      icon: 'User',
      enabled: true,
      fields: [
        {
          key: 'name',
          label: 'Company Name',
          type: 'text',
          required: true,
          placeholder: 'Enter company name',
          gridCols: 1
        }
        // ... more fields
      ]
    }
    // ... more tabs
  ]
}
```

### Field Types

Supported field types:

- `text` - Text input
- `email` - Email input with validation
- `url` - URL input with validation
- `tel` - Phone number input
- `date` - Date picker
- `select` - Dropdown with options
- `textarea` - Multi-line text
- `file` - File upload with preview
- `color` - Color picker

### Field Configuration

```typescript
interface FieldConfig {
  key: string                    // Database field name
  label: string                  // Display label
  type: 'text' | 'email' | ...   // Field type
  required?: boolean             // Required field?
  placeholder?: string           // Placeholder text
  options?: Option[]             // For select fields
  validation?: RegExp | Function // Custom validation
  hint?: string                  // Help text
  disabled?: boolean             // Disable field?
  visible?: boolean              // Show field?
  gridCols?: 1 | 2              // Grid columns (1 or 2)
}
```

## 🌍 Environment Configuration

### Production Settings

Create `config/environments/production.config.ts`:

```typescript
export const productionConfigOverrides: Partial<CompanySettingsConfig> = {
  features: {
    autoSave: true,
    autoSaveDelay: 1000,        // Faster auto-save
    showStatusIndicators: false  // Hide progress in prod
  },
  
  permissions: {
    canEdit: (user) => user?.role === 'admin' || hasPermission(user, 'manage_company')
  }
}
```

### Development Settings

Create `config/environments/development.config.ts`:

```typescript
export const developmentConfigOverrides: Partial<CompanySettingsConfig> = {
  features: {
    autoSave: false,            // Disable for testing
    enableValidation: false     // Skip validation in dev
  },
  
  // Add debug tab
  tabs: [
    {
      id: 'debug',
      name: 'Debug',
      icon: 'Bug',
      enabled: true,
      fields: [
        {
          key: 'debug_mode',
          label: 'Debug Mode',
          type: 'select',
          options: [
            { value: 'off', label: 'Off' },
            { value: 'verbose', label: 'Verbose' }
          ]
        }
      ]
    }
  ]
}
```

## 🎨 Runtime Customization

### Via JavaScript

```typescript
import { saveCustomConfig } from '@/modules/settings/config/company-settings.config'

// Customize at runtime
const customConfig = {
  title: "My Custom Company Settings",
  features: {
    autoSave: false
  },
  tabs: [
    {
      id: 'custom',
      name: 'Custom Fields',
      icon: 'Settings',
      enabled: true,
      fields: [
        {
          key: 'custom_field',
          label: 'Custom Field',
          type: 'text'
        }
      ]
    }
  ]
}

saveCustomConfig(customConfig)
```

### Via Browser Console

```javascript
// Quick customization via browser console
const customConfig = {
  features: { autoSave: false },
  tabs: [{ id: 'basic', enabled: false }] // Disable basic tab
}

localStorage.setItem('company-settings-config', JSON.stringify(customConfig))
location.reload() // Reload to apply changes
```

## 🔧 Usage

### Basic Usage

```vue
<template>
  <DynamicCompanySettings
    :settings="companySettings"
    :can-write="canManageSettings"
  />
</template>

<script setup>
import DynamicCompanySettings from '@/modules/settings/components/DynamicCompanySettings.vue'
</script>
```

### Custom Component Integration

For complex fields, you can specify custom components:

```typescript
{
  id: 'portfolio',
  name: 'Portfolio',
  icon: 'Briefcase',
  enabled: true,
  fields: [], // Empty fields
  customComponent: '@/modules/settings/components/company/CompanyPortfolioManager.vue'
}
```

## 🔒 Permissions

Configure role-based permissions:

```typescript
permissions: {
  canEdit: (user) => user?.role === 'admin' || user?.role === 'manager',
  canDelete: (user) => user?.role === 'admin',
  canExport: (user) => hasPermission(user, 'export_settings')
}
```

## ⚡ Performance Features

- **Auto-Save**: Configurable delay (default 2000ms)
- **Validation**: Client-side validation with custom rules
- **Progress Tracking**: Visual completion indicators
- **Responsive**: Mobile-first design
- **Accessibility**: WCAG compliant

## 🧪 Testing

### Unit Tests

```bash
npm run test -- --grep "DynamicCompanySettings"
```

### Configuration Testing

```typescript
// Test custom config
const config = await getCompanySettingsConfig()
expect(config.tabs).toHaveLength(5)
expect(config.features.autoSave).toBe(true)
```

## 🔄 Migration from Static Component

To migrate from the old static component:

1. **Replace import**:
   ```typescript
   // Old
   import CompanySettings from '@/modules/settings/components/CompanySettings.vue'
   
   // New
   import DynamicCompanySettings from '@/modules/settings/components/DynamicCompanySettings.vue'
   ```

2. **Update template**:
   ```vue
   <!-- Old -->
   <CompanySettings :settings="settings" :can-write="canWrite" />
   
   <!-- New -->
   <DynamicCompanySettings :settings="settings" :can-write="canWrite" />
   ```

3. **Test thoroughly** - The dynamic component should work identically

## 📚 Examples

### Adding a New Field

```typescript
// In company-settings.config.ts
{
  key: 'linkedin_url',
  label: 'LinkedIn Profile',
  type: 'url',
  placeholder: 'https://linkedin.com/company/yourcompany',
  hint: 'Your company LinkedIn page',
  gridCols: 1
}
```

### Custom Validation

```typescript
{
  key: 'tax_id',
  label: 'Tax ID',
  type: 'text',
  validation: (value) => /^\d{2}-\d{7}$/.test(value),
  hint: 'Format: XX-XXXXXXX'
}
```

### Conditional Fields

```typescript
{
  key: 'international_office',
  label: 'International Office',
  type: 'text',
  visible: (formData) => formData.company_size === 'enterprise'
}
```

## 🐛 Troubleshooting

### Common Issues

1. **Fields not showing**: Check `visible` and `enabled` properties
2. **Validation not working**: Ensure `enableValidation` is true
3. **Auto-save not triggering**: Check `autoSave` feature flag
4. **Custom config not loading**: Check browser console for errors

### Debug Mode

Enable debug logging:

```typescript
localStorage.setItem('company-settings-debug', 'true')
```

## 🚀 Advanced Features

### Custom Field Components

Create custom field types by extending `DynamicField.vue`:

```vue
<!-- CustomRichTextField.vue -->
<template>
  <div>
    <label>{{ config.label }}</label>
    <QuillEditor v-model="internalValue" />
  </div>
</template>
```

### Theme Customization

Override CSS variables:

```css
.dynamic-company-settings {
  --primary-color: #your-brand-color;
  --border-radius: 8px;
  --spacing: 1.5rem;
}
```

### Plugin System

Create plugins for additional functionality:

```typescript
// plugin-analytics.ts
export const analyticsPlugin = {
  onFieldChange: (field, value) => {
    analytics.track('company_field_changed', { field, value })
  }
}
```

## 📈 Roadmap

- [ ] Visual form builder interface
- [ ] A/B testing for form layouts  
- [ ] Multi-language field labels
- [ ] Advanced validation rules engine
- [ ] Integration with external APIs
- [ ] Bulk import/export functionality

## 🤝 Contributing

1. Create feature branch: `git checkout -b feature/new-field-type`
2. Add your changes to configuration files
3. Update documentation
4. Add tests
5. Submit pull request

## 📞 Support

For questions or issues:

- Check this README
- Review configuration examples
- Check browser console for errors
- Contact the development team

---

**Happy Customizing! 🎉**
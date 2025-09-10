// Core settings interface types for construction management platform

export type SettingCategory = 'company' | 'system' | 'notifications' | 'security' | 'backup';

export type SettingType = 'string' | 'number' | 'boolean' | 'select' | 'multiselect' | 'file' | 'color' | 'datetime' | 'json';

export type NotificationChannel = 'email' | 'sms' | 'push' | 'in_app';

export type TimeZone = string;

export type Language = 'en' | 'es' | 'fr' | 'de' | 'pt' | 'it' | 'ru' | 'zh' | 'ja' | 'ko';

export type Currency = 'USD' | 'EUR' | 'GBP' | 'CAD' | 'AUD' | 'JPY' | 'CNY' | 'INR' | 'BRL' | 'MXN';

export type MeasurementSystem = 'imperial' | 'metric';

export type BackupFrequency = 'daily' | 'weekly' | 'monthly' | 'custom';

export type BackupStorage = 'local' | 's3' | 'google_cloud' | 'azure' | 'ftp';

// Company/Organization Settings
export interface CompanySettings {
  // Basic Info
  name: string;
  legal_name?: string;
  industry: string;
  logo_url?: string;
  website?: string;
  
  // Contact Information
  email: string;
  phone?: string;
  fax?: string;
  
  // Address
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state_province: string;
  postal_code: string;
  country: string;
  
  // Financial
  tax_id?: string;
  currency: Currency;
  fiscal_year_start: string; // MM-DD format
  
  // Branding
  primary_color: string;
  secondary_color: string;
  accent_color: string;
  
  // Legal & Compliance
  privacy_policy_url?: string;
  terms_of_service_url?: string;
  data_retention_days: number;
}

// System Preferences
export interface SystemPreferences {
  // Localization
  language: Language;
  timezone: TimeZone;
  date_format: string; // e.g., 'MM/dd/yyyy', 'dd/MM/yyyy'
  time_format: '12h' | '24h';
  measurement_system: MeasurementSystem;
  
  // UI Preferences
  theme: 'light' | 'dark' | 'auto';
  sidebar_collapsed: boolean;
  items_per_page: number;
  auto_refresh_interval: number; // seconds
  
  // System Behavior
  session_timeout: number; // minutes
  auto_save_interval: number; // seconds
  enable_sound_notifications: boolean;
  show_tooltips: boolean;
}

// Notification Settings
export interface NotificationSettings {
  // Global settings
  enabled: boolean;
  quiet_hours_start?: string; // HH:mm format
  quiet_hours_end?: string;
  
  // Channel preferences
  channels: {
    email: {
      enabled: boolean;
      address?: string;
    };
    sms: {
      enabled: boolean;
      phone_number?: string;
    };
    push: {
      enabled: boolean;
    };
    in_app: {
      enabled: boolean;
    };
  };
  
  // Event-specific notifications
  events: {
    user_registration: NotificationChannel[];
    project_created: NotificationChannel[];
    project_updated: NotificationChannel[];
    task_assigned: NotificationChannel[];
    task_completed: NotificationChannel[];
    deadline_approaching: NotificationChannel[];
    system_maintenance: NotificationChannel[];
    backup_completed: NotificationChannel[];
    security_alert: NotificationChannel[];
  };
  
  // Frequency controls
  digest_frequency: 'real_time' | 'hourly' | 'daily' | 'weekly' | 'never';
  max_daily_notifications: number;
}

// Security Settings
export interface SecuritySettings {
  // Password Policy
  password_policy: {
    min_length: number;
    require_uppercase: boolean;
    require_lowercase: boolean;
    require_numbers: boolean;
    require_special_chars: boolean;
    expiry_days: number;
    prevent_reuse_count: number;
  };
  
  // Authentication
  two_factor_auth: {
    enabled: boolean;
    required_for_admins: boolean;
    backup_codes_enabled: boolean;
  };
  
  // Session Management
  max_concurrent_sessions: number;
  idle_session_timeout: number; // minutes
  force_logout_inactive_users: boolean;
  
  // Access Control
  ip_whitelist: string[];
  failed_login_attempts_limit: number;
  lockout_duration: number; // minutes
  
  // Data Protection
  encryption_at_rest: boolean;
  audit_log_retention_days: number;
  data_anonymization_enabled: boolean;
  
  // API Security
  api_rate_limiting: {
    enabled: boolean;
    requests_per_minute: number;
    burst_limit: number;
  };
}

// Backup Settings
export interface BackupSettings {
  // Backup Configuration
  enabled: boolean;
  frequency: BackupFrequency;
  custom_schedule?: string; // cron expression for custom frequency
  
  // Storage Configuration
  storage_type: BackupStorage;
  storage_config: {
    // Local storage
    local_path?: string;
    
    // Cloud storage (S3, Google Cloud, Azure)
    bucket_name?: string;
    region?: string;
    access_key?: string;
    secret_key?: string;
    
    // FTP
    ftp_host?: string;
    ftp_username?: string;
    ftp_password?: string;
    ftp_path?: string;
  };
  
  // Backup Options
  include_files: boolean;
  include_database: boolean;
  compress_backups: boolean;
  encrypt_backups: boolean;
  retention_days: number;
  
  // Maintenance
  maintenance_mode: {
    enabled: boolean;
    message: string;
    allowed_ips: string[];
  };
  
  // Monitoring
  send_completion_notifications: boolean;
  send_failure_notifications: boolean;
  health_check_url?: string;
}

// Complete settings structure
export interface SystemSettings {
  company: CompanySettings;
  system: SystemPreferences;
  notifications: NotificationSettings;
  security: SecuritySettings;
  backup: BackupSettings;
  updated_at: string;
  updated_by: string;
}

// Settings update requests
export interface SettingsUpdateRequest {
  category: SettingCategory;
  settings: Partial<CompanySettings | SystemPreferences | NotificationSettings | SecuritySettings | BackupSettings>;
}

// Settings validation
export interface SettingValidation {
  key: string;
  type: SettingType;
  required: boolean;
  min_value?: number;
  max_value?: number;
  min_length?: number;
  max_length?: number;
  pattern?: string;
  options?: string[];
  depends_on?: string;
  description: string;
}

// Settings permissions
export interface SettingsPermission {
  category: SettingCategory;
  setting_key: string;
  required_role: 'admin' | 'project_manager' | 'supervisor' | 'field_worker';
  can_read: boolean;
  can_write: boolean;
}

// Settings audit log
export interface SettingsAuditLog {
  id: string;
  category: SettingCategory;
  setting_key: string;
  old_value: any;
  new_value: any;
  changed_by: string;
  changed_at: string;
  ip_address: string;
  user_agent: string;
}

// Settings export/import
export interface SettingsExport {
  version: string;
  exported_at: string;
  exported_by: string;
  settings: Partial<SystemSettings>;
  checksum: string;
}

export interface SettingsImport {
  file: File;
  overwrite_existing: boolean;
  categories: SettingCategory[];
}

// API Response types
export interface SettingsResponse {
  data: SystemSettings;
  permissions: SettingsPermission[];
  validations: SettingValidation[];
}

export interface SettingsUpdateResponse {
  success: boolean;
  updated_settings: Partial<SystemSettings>;
  validation_errors?: Record<string, string[]>;
  message: string;
}

// Settings state for store
export interface SettingsState {
  settings: SystemSettings | null;
  permissions: SettingsPermission[];
  validations: SettingValidation[];
  isLoading: boolean;
  isSaving: boolean;
  error: string | null;
  hasUnsavedChanges: boolean;
  lastUpdated: string | null;
}

// Component-specific data structures for forms
export interface CompanySettingsData {
  company_name: string
  company_legal_name?: string
  company_registration?: string
  tax_id?: string
  industry?: string
  company_size?: string
  website?: string
  description?: string
  logo_url?: string
  primary_color?: string
  secondary_color?: string
  address_line_1?: string
  address_line_2?: string
  city?: string
  state?: string
  postal_code?: string
  country?: string
  phone?: string
  email?: string
  currency?: string
  timezone?: string
  date_format?: string
  time_format?: string
  fiscal_year_start?: string
  language?: string
}

export interface SystemSettingsData {
  app_name: string
  app_version: string
  maintenance_mode: 'disabled' | 'enabled' | 'scheduled'
  debug_mode: 'true' | 'false'
  cache_driver: 'file' | 'redis' | 'memcached'
  session_lifetime: number
  queue_driver: 'sync' | 'redis' | 'database'
  max_upload_size: number
  log_channel: 'single' | 'daily' | 'stack'
  log_level: 'emergency' | 'alert' | 'critical' | 'error' | 'warning' | 'notice' | 'info' | 'debug'
  log_max_files: number
  audit_retention_days: number
}

export interface NotificationSettingsData {
  email_driver: 'smtp' | 'mailgun' | 'ses' | 'sendmail'
  smtp_host: string
  smtp_port: number
  smtp_username: string
  smtp_encryption: 'tls' | 'ssl' | 'none'
  from_email: string
  from_name: string
  notify_task_assignment: boolean
  notify_project_updates: boolean
  notify_deadline_reminders: boolean
  notify_system_maintenance: boolean
  notify_security_alerts: boolean
  deadline_reminder_days: number
  overdue_reminder_frequency: 'daily' | 'weekly' | 'disabled'
  digest_frequency: 'daily' | 'weekly' | 'monthly' | 'disabled'
  digest_time: string
}

export interface SecuritySettingsData {
  password_min_length: number
  password_expiry_days: number
  max_login_attempts: number
  lockout_duration: number
  session_timeout: number
  concurrent_sessions: number
  require_uppercase: boolean
  require_numbers: boolean
  require_special_chars: boolean
  prevent_password_reuse: boolean
  password_history_limit: number
  enable_2fa: boolean
  force_2fa_admin: boolean
  totp_issuer: string
  backup_codes_count: number
  enable_ip_whitelist: boolean
  enable_rate_limiting: boolean
  enable_security_headers: boolean
  rate_limit_requests: number
  audit_log_retention: number
}

export interface BackupSettingsData {
  enable_backups: boolean
  include_files: boolean
  compress_backups: boolean
  backup_frequency: 'hourly' | 'daily' | 'weekly' | 'monthly'
  backup_time: string
  backup_retention_days: number
  max_backup_size: number
  storage_driver: 'local' | 's3' | 'gcs' | 'azure' | 'ftp'
  s3_bucket: string
  s3_region: string
  s3_access_key: string
  s3_secret_key: string
  local_backup_path: string
  enable_point_in_time_recovery: boolean
  enable_integrity_checks: boolean
  notify_recovery_mode: boolean
  recovery_window_hours: number
  max_recovery_attempts: number
}
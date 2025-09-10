/**
 * Locale-aware formatters for dates, numbers, currencies, etc.
 */

import { getLocaleConfig } from './supported-locales'

export class LocaleFormatter {
  private locale: string
  private localeConfig: any

  constructor(locale: string) {
    this.locale = locale
    this.localeConfig = getLocaleConfig(locale)
  }

  /**
   * Format date according to locale preferences
   */
  formatDate(date: Date | string | number, options?: Intl.DateTimeFormatOptions): string {
    const dateObj = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date
    
    if (!this.localeConfig) {
      return new Intl.DateTimeFormat(this.locale, options).format(dateObj)
    }

    const defaultOptions: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    }

    return new Intl.DateTimeFormat(this.locale, { ...defaultOptions, ...options }).format(dateObj)
  }

  /**
   * Format time according to locale preferences
   */
  formatTime(date: Date | string | number, options?: Intl.DateTimeFormatOptions): string {
    const dateObj = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date
    
    const defaultOptions: Intl.DateTimeFormatOptions = {
      hour: '2-digit',
      minute: '2-digit',
      hour12: this.localeConfig?.timeFormat === '12h'
    }

    return new Intl.DateTimeFormat(this.locale, { ...defaultOptions, ...options }).format(dateObj)
  }

  /**
   * Format date and time together
   */
  formatDateTime(date: Date | string | number, options?: Intl.DateTimeFormatOptions): string {
    const dateObj = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date
    
    const defaultOptions: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      hour12: this.localeConfig?.timeFormat === '12h'
    }

    return new Intl.DateTimeFormat(this.locale, { ...defaultOptions, ...options }).format(dateObj)
  }

  /**
   * Format relative time (e.g., "2 hours ago")
   */
  formatRelativeTime(date: Date | string | number): string {
    const dateObj = typeof date === 'string' || typeof date === 'number' ? new Date(date) : date
    const now = new Date()
    const diffInSeconds = Math.floor((now.getTime() - dateObj.getTime()) / 1000)

    const rtf = new Intl.RelativeTimeFormat(this.locale, { numeric: 'auto' })

    if (diffInSeconds < 60) {
      return rtf.format(-diffInSeconds, 'second')
    } else if (diffInSeconds < 3600) {
      return rtf.format(-Math.floor(diffInSeconds / 60), 'minute')
    } else if (diffInSeconds < 86400) {
      return rtf.format(-Math.floor(diffInSeconds / 3600), 'hour')
    } else if (diffInSeconds < 2592000) {
      return rtf.format(-Math.floor(diffInSeconds / 86400), 'day')
    } else if (diffInSeconds < 31536000) {
      return rtf.format(-Math.floor(diffInSeconds / 2592000), 'month')
    } else {
      return rtf.format(-Math.floor(diffInSeconds / 31536000), 'year')
    }
  }

  /**
   * Format number according to locale preferences
   */
  formatNumber(num: number, options?: Intl.NumberFormatOptions): string {
    return new Intl.NumberFormat(this.locale, options).format(num)
  }

  /**
   * Format currency
   */
  formatCurrency(amount: number, currency?: string, options?: Intl.NumberFormatOptions): string {
    const currencyCode = currency || this.localeConfig?.currency || 'USD'
    
    const defaultOptions: Intl.NumberFormatOptions = {
      style: 'currency',
      currency: currencyCode
    }

    return new Intl.NumberFormat(this.locale, { ...defaultOptions, ...options }).format(amount)
  }

  /**
   * Format percentage
   */
  formatPercentage(num: number, options?: Intl.NumberFormatOptions): string {
    const defaultOptions: Intl.NumberFormatOptions = {
      style: 'percent',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }

    return new Intl.NumberFormat(this.locale, { ...defaultOptions, ...options }).format(num / 100)
  }

  /**
   * Format file size
   */
  formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes'

    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))

    const size = parseFloat((bytes / Math.pow(k, i)).toFixed(2))
    return `${this.formatNumber(size)} ${sizes[i]}`
  }

  /**
   * Format phone number according to locale
   */
  formatPhoneNumber(phoneNumber: string): string {
    if (!this.localeConfig?.phoneFormat) return phoneNumber

    // Simple formatting - in real app you'd use a proper phone formatting library
    const cleaned = phoneNumber.replace(/\D/g, '')
    const format = this.localeConfig.phoneFormat

    // This is a simplified implementation
    let formatted = format
    for (let i = 0; i < cleaned.length && formatted.includes('X'); i++) {
      formatted = formatted.replace('X', cleaned[i])
    }

    return formatted.replace(/X/g, '')
  }

  /**
   * Format address according to locale preferences
   */
  formatAddress(address: {
    line1?: string
    line2?: string
    city?: string
    state?: string
    zipCode?: string
    country?: string
  }): string[] {
    if (!this.localeConfig?.addressFormat) {
      return [address.line1, address.line2, address.city, address.state, address.zipCode, address.country]
        .filter(Boolean) as string[]
    }

    return this.localeConfig.addressFormat
      .map((field: string) => address[field as keyof typeof address])
      .filter(Boolean)
  }
}

/**
 * Global formatter functions
 */
export const createFormatter = (locale: string) => new LocaleFormatter(locale)

export const formatters = {
  date: (date: Date | string | number, locale: string, options?: Intl.DateTimeFormatOptions) => 
    new LocaleFormatter(locale).formatDate(date, options),
  
  time: (date: Date | string | number, locale: string, options?: Intl.DateTimeFormatOptions) => 
    new LocaleFormatter(locale).formatTime(date, options),
  
  dateTime: (date: Date | string | number, locale: string, options?: Intl.DateTimeFormatOptions) => 
    new LocaleFormatter(locale).formatDateTime(date, options),
  
  relativeTime: (date: Date | string | number, locale: string) => 
    new LocaleFormatter(locale).formatRelativeTime(date),
  
  number: (num: number, locale: string, options?: Intl.NumberFormatOptions) => 
    new LocaleFormatter(locale).formatNumber(num, options),
  
  currency: (amount: number, locale: string, currency?: string, options?: Intl.NumberFormatOptions) => 
    new LocaleFormatter(locale).formatCurrency(amount, currency, options),
  
  percentage: (num: number, locale: string, options?: Intl.NumberFormatOptions) => 
    new LocaleFormatter(locale).formatPercentage(num, options),
  
  fileSize: (bytes: number, locale: string) => 
    new LocaleFormatter(locale).formatFileSize(bytes),
  
  phoneNumber: (phone: string, locale: string) => 
    new LocaleFormatter(locale).formatPhoneNumber(phone),
  
  address: (address: any, locale: string) => 
    new LocaleFormatter(locale).formatAddress(address)
}
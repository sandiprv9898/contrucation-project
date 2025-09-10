/**
 * Supported Locales Configuration
 * 
 * This file defines all supported languages and their regional settings
 */

export interface LocaleConfig {
  code: string
  name: string
  nativeName: string
  flag: string
  rtl: boolean
  dateFormat: string
  timeFormat: '12h' | '24h'
  currency: string
  numberFormat: {
    decimal: string
    thousands: string
    precision: number
  }
  phoneFormat: string
  addressFormat: string[]
  enabled: boolean
}

export const supportedLocales: LocaleConfig[] = [
  {
    code: 'en',
    name: 'English',
    nativeName: 'English',
    flag: '🇺🇸',
    rtl: false,
    dateFormat: 'MM/DD/YYYY',
    timeFormat: '12h',
    currency: 'USD',
    numberFormat: {
      decimal: '.',
      thousands: ',',
      precision: 2
    },
    phoneFormat: '+1 (XXX) XXX-XXXX',
    addressFormat: ['line1', 'line2', 'city', 'state', 'zipCode', 'country'],
    enabled: true
  },
  {
    code: 'es',
    name: 'Spanish',
    nativeName: 'Español',
    flag: '🇪🇸',
    rtl: false,
    dateFormat: 'DD/MM/YYYY',
    timeFormat: '24h',
    currency: 'EUR',
    numberFormat: {
      decimal: ',',
      thousands: '.',
      precision: 2
    },
    phoneFormat: '+34 XXX XXX XXX',
    addressFormat: ['line1', 'line2', 'zipCode', 'city', 'state', 'country'],
    enabled: true
  },
  {
    code: 'fr',
    name: 'French',
    nativeName: 'Français',
    flag: '🇫🇷',
    rtl: false,
    dateFormat: 'DD/MM/YYYY',
    timeFormat: '24h',
    currency: 'EUR',
    numberFormat: {
      decimal: ',',
      thousands: ' ',
      precision: 2
    },
    phoneFormat: '+33 X XX XX XX XX',
    addressFormat: ['line1', 'line2', 'zipCode', 'city', 'country'],
    enabled: true
  },
  {
    code: 'de',
    name: 'German',
    nativeName: 'Deutsch',
    flag: '🇩🇪',
    rtl: false,
    dateFormat: 'DD.MM.YYYY',
    timeFormat: '24h',
    currency: 'EUR',
    numberFormat: {
      decimal: ',',
      thousands: '.',
      precision: 2
    },
    phoneFormat: '+49 XXX XXXXXXXX',
    addressFormat: ['line1', 'line2', 'zipCode', 'city', 'country'],
    enabled: true
  },
  {
    code: 'ar',
    name: 'Arabic',
    nativeName: 'العربية',
    flag: '🇸🇦',
    rtl: true,
    dateFormat: 'DD/MM/YYYY',
    timeFormat: '12h',
    currency: 'SAR',
    numberFormat: {
      decimal: '.',
      thousands: ',',
      precision: 2
    },
    phoneFormat: '+966 X XXXX XXXX',
    addressFormat: ['line1', 'line2', 'city', 'state', 'zipCode', 'country'],
    enabled: true
  },
  {
    code: 'pt',
    name: 'Portuguese',
    nativeName: 'Português',
    flag: '🇧🇷',
    rtl: false,
    dateFormat: 'DD/MM/YYYY',
    timeFormat: '24h',
    currency: 'BRL',
    numberFormat: {
      decimal: ',',
      thousands: '.',
      precision: 2
    },
    phoneFormat: '+55 (XX) XXXXX-XXXX',
    addressFormat: ['line1', 'line2', 'city', 'state', 'zipCode', 'country'],
    enabled: false // Can be enabled later
  },
  {
    code: 'zh',
    name: 'Chinese (Simplified)',
    nativeName: '简体中文',
    flag: '🇨🇳',
    rtl: false,
    dateFormat: 'YYYY/MM/DD',
    timeFormat: '24h',
    currency: 'CNY',
    numberFormat: {
      decimal: '.',
      thousands: ',',
      precision: 2
    },
    phoneFormat: '+86 XXX XXXX XXXX',
    addressFormat: ['country', 'state', 'city', 'line1', 'line2', 'zipCode'],
    enabled: false // Can be enabled later
  },
  {
    code: 'ru',
    name: 'Russian',
    nativeName: 'Русский',
    flag: '🇷🇺',
    rtl: false,
    dateFormat: 'DD.MM.YYYY',
    timeFormat: '24h',
    currency: 'RUB',
    numberFormat: {
      decimal: ',',
      thousands: ' ',
      precision: 2
    },
    phoneFormat: '+7 (XXX) XXX-XX-XX',
    addressFormat: ['zipCode', 'state', 'city', 'line1', 'line2', 'country'],
    enabled: false // Can be enabled later
  }
]

export const defaultLocale = 'en'

export const enabledLocales = supportedLocales.filter(locale => locale.enabled)

export const getLocaleConfig = (code: string): LocaleConfig | undefined => {
  return supportedLocales.find(locale => locale.code === code)
}

export const isRTLLocale = (code: string): boolean => {
  const locale = getLocaleConfig(code)
  return locale?.rtl || false
}

export const getLocaleDirection = (code: string): 'ltr' | 'rtl' => {
  return isRTLLocale(code) ? 'rtl' : 'ltr'
}

export const rtlLocales = supportedLocales.filter(locale => locale.rtl).map(locale => locale.code)
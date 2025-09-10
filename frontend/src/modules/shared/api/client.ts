import axios, { type AxiosInstance, type AxiosResponse, type AxiosError } from 'axios'
import { API_CONFIG } from '../constants/api'
import { TokenManager } from '../utils/tokenManager'

class ApiClient {
  private client: AxiosInstance

  constructor() {
    this.client = axios.create({
      baseURL: API_CONFIG.BASE_URL,
      withCredentials: true,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      timeout: API_CONFIG.TIMEOUT,
    })

    this.setupInterceptors()
  }

  private setupInterceptors(): void {
    // Request interceptor
    this.client.interceptors.request.use(
      (config: any) => {
        const token = TokenManager.getToken()
        if (token) {
          config.headers.Authorization = `Bearer ${token}`
        }
        
        // Add CSRF token for all requests when authenticated
        const csrfToken = this.getCSRFTokenFromCookie()
        if (csrfToken) {
          config.headers['X-XSRF-TOKEN'] = csrfToken
          console.log('[API] Adding CSRF token to request:', csrfToken.substring(0, 20) + '...')
        } else if (token) {
          // If we have a token but no CSRF cookie, we need to get one
          console.warn('[API] No CSRF token found in cookie for authenticated request')
        }
        
        return config
      },
      (error: AxiosError) => Promise.reject(error)
    )

    // Response interceptor
    this.client.interceptors.response.use(
      (response: AxiosResponse) => response,
      (error: AxiosError) => {
        if (error.response?.status === 401) {
          const token = TokenManager.getToken()
          
          // For demo tokens, let APIs handle fallbacks instead of logging out
          if (token && token.startsWith('demo-token')) {
            console.warn('[AUTH] 401 response with demo token - letting API handle fallback')
            return Promise.reject(error)
          }
          
          // Check if user is authenticated - only auto-logout if they had a valid session
          if (token) {
            console.warn('[AUTH] 401 Unauthorized - clearing authentication and redirecting to login')
            
            // Clear token from storage
            TokenManager.removeToken()
            
            // Clear auth store state
            import('@/modules/auth').then(({ useAuthStore }) => {
              const authStore = useAuthStore()
              authStore.clearAuthData()
            }).catch(console.error)
            
            // Redirect to login page
            import('@/router').then(({ default: router }) => {
              router.push('/auth/login')
            }).catch(console.error)
          } else {
            // No token means user wasn't authenticated anyway - let API methods handle fallbacks
            console.warn('[AUTH] 401 response for unauthenticated user - letting API handle fallback')
          }
        }
        return Promise.reject(error)
      }
    )
  }

  public getInstance(): AxiosInstance {
    return this.client
  }

  // Generic API methods with enhanced parameter handling
  public async get<T>(url: string, params?: Record<string, unknown>, config?: Record<string, unknown>): Promise<T> {
    // Clean and process parameters for better API compatibility
    const processedParams = this.processApiParameters(params)
    
    const requestConfig = {
      params: processedParams,
      ...config
    }
    
    console.log(`🌐 [API CLIENT] GET ${url}`, {
      params: processedParams,
      originalParams: params
    })
    
    const response = await this.client.get<T>(url, requestConfig)
    return response.data
  }

  /**
   * Process API parameters to ensure compatibility with Laravel backend
   */
  private processApiParameters(params?: Record<string, unknown>): Record<string, string> {
    if (!params) return {}
    
    const processed: Record<string, string> = {}
    
    for (const [key, value] of Object.entries(params)) {
      // Skip undefined, null, and empty string values
      if (value === undefined || value === null || value === '') {
        continue
      }
      
      // Handle array parameters (convert to comma-separated string)
      if (Array.isArray(value)) {
        if (value.length > 0) {
          processed[key] = value.join(',')
        }
      }
      // Handle boolean parameters (convert to string)
      else if (typeof value === 'boolean') {
        processed[key] = value ? '1' : '0'
      }
      // Handle date parameters (ensure proper format)
      else if (value instanceof Date) {
        processed[key] = value.toISOString().split('T')[0]
      }
      // Handle string parameters (trim whitespace)
      else if (typeof value === 'string') {
        const trimmed = value.trim()
        if (trimmed) {
          processed[key] = trimmed
        }
      }
      // Handle numeric parameters (convert to string)
      else if (typeof value === 'number') {
        processed[key] = value.toString()
      }
      // Skip complex objects to avoid serialization issues
      else if (typeof value === 'object') {
        console.warn(`[API CLIENT] Skipping complex object parameter: ${key}`)
        continue
      }
      // Convert other types to string
      else {
        processed[key] = String(value)
      }
    }
    
    return processed
  }

  public async post<T>(url: string, data?: unknown): Promise<T> {
    // Get CSRF cookie for authenticated requests
    const token = TokenManager.getToken()
    if (token && !this.getCSRFTokenFromCookie()) {
      console.log('[API] Getting CSRF cookie for POST request:', url)
      await this.getCsrfCookie()
    }
    
    const response = await this.client.post<T>(url, data)
    return response.data
  }

  // Get CSRF cookie for Sanctum SPA authentication
  private async getCsrfCookie(): Promise<void> {
    try {
      const csrfUrl = `${API_CONFIG.BASE_URL.replace('/api/v1', '')}/sanctum/csrf-cookie`
      console.log('[AUTH] Requesting CSRF cookie from:', csrfUrl)
      
      // Call CSRF endpoint directly without baseURL prefix
      const response = await axios.get(csrfUrl, {
        withCredentials: true,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      })
      
      console.log('[AUTH] CSRF cookie response status:', response.status)
      
      // Check if the CSRF token is now available in cookies
      const token = this.getCSRFTokenFromCookie()
      console.log('[AUTH] CSRF token after request:', token ? token.substring(0, 20) + '...' : 'Not found')
    } catch (error) {
      console.error('[AUTH] Failed to get CSRF cookie:', error)
    }
  }

  // Extract CSRF token from cookie
  private getCSRFTokenFromCookie(): string | null {
    try {
      const name = 'XSRF-TOKEN'
      const value = `; ${document.cookie}`
      const parts = value.split(`; ${name}=`)
      if (parts.length === 2) {
        const cookieValue = parts.pop()?.split(';').shift()
        if (cookieValue) {
          // Decode the URL-encoded cookie value
          return decodeURIComponent(cookieValue)
        }
      }
      return null
    } catch (error) {
      console.error('Error reading CSRF token from cookie:', error)
      return null
    }
  }

  public async put<T>(url: string, data?: unknown): Promise<T> {
    // Get CSRF cookie for authenticated requests
    const token = TokenManager.getToken()
    if (token && !this.getCSRFTokenFromCookie()) {
      console.log('[API] Getting CSRF cookie for PUT request:', url)
      await this.getCsrfCookie()
    }
    
    const response = await this.client.put<T>(url, data)
    return response.data
  }

  public async delete<T>(url: string): Promise<T> {
    // Get CSRF cookie for authenticated requests
    const token = TokenManager.getToken()
    if (token && !this.getCSRFTokenFromCookie()) {
      console.log('[API] Getting CSRF cookie for DELETE request:', url)
      await this.getCsrfCookie()
    }
    
    const response = await this.client.delete<T>(url)
    return response.data
  }
}

export const apiClient = new ApiClient()
export default apiClient
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
        
        // Add CSRF token for authentication requests
        if (config.url?.includes('/auth/')) {
          const csrfToken = this.getCSRFTokenFromCookie()
          if (csrfToken) {
            config.headers['X-XSRF-TOKEN'] = csrfToken
            console.log('[AUTH] Adding CSRF token to request:', csrfToken.substring(0, 20) + '...')
          } else {
            console.warn('[AUTH] No CSRF token found in cookie')
          }
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
          TokenManager.removeToken()
          window.location.href = '/auth/login'
        }
        return Promise.reject(error)
      }
    )
  }

  public getInstance(): AxiosInstance {
    return this.client
  }

  // Generic API methods
  public async get<T>(url: string, params?: Record<string, unknown>): Promise<T> {
    const response = await this.client.get<T>(url, { params })
    return response.data
  }

  public async post<T>(url: string, data?: unknown): Promise<T> {
    // Get CSRF cookie for authentication endpoints
    if (url.includes('/auth/')) {
      console.log('[AUTH] Getting CSRF cookie for auth request:', url)
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

  public async put<T>(url: string, data?: any): Promise<T> {
    const response = await this.client.put<T>(url, data)
    return response.data
  }

  public async delete<T>(url: string): Promise<T> {
    const response = await this.client.delete<T>(url)
    return response.data
  }
}

export const apiClient = new ApiClient()
export default apiClient
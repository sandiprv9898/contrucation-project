export class TokenManager {
  private static readonly TOKEN_KEY = 'auth_token'
  private static readonly USER_KEY = 'auth_user'

  static getToken(): string | null {
    try {
      return localStorage.getItem(this.TOKEN_KEY)
    } catch (error) {
      console.error('Error getting token from localStorage:', error)
      return null
    }
  }

  static setToken(token: string): void {
    try {
      localStorage.setItem(this.TOKEN_KEY, token)
    } catch (error) {
      console.error('Error setting token in localStorage:', error)
    }
  }

  static removeToken(): void {
    try {
      localStorage.removeItem(this.TOKEN_KEY)
      localStorage.removeItem(this.USER_KEY)
    } catch (error) {
      console.error('Error removing token from localStorage:', error)
    }
  }

  static hasToken(): boolean {
    return !!this.getToken()
  }

  static getUser<T>(): T | null {
    try {
      const userString = localStorage.getItem(this.USER_KEY)
      return userString ? JSON.parse(userString) : null
    } catch (error) {
      console.error('Error getting user from localStorage:', error)
      return null
    }
  }

  static setUser<T>(user: T): void {
    try {
      localStorage.setItem(this.USER_KEY, JSON.stringify(user))
    } catch (error) {
      console.error('Error setting user in localStorage:', error)
    }
  }

  static clear(): void {
    this.removeToken()
  }
}
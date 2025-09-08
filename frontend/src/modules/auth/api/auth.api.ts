import { apiClient } from '@/modules/shared/api/client'
import { API_CONFIG } from '@/modules/shared/constants/api'
import type {
  LoginRequest,
  RegisterRequest,
  ForgotPasswordRequest,
  AuthResponse,
  MessageResponse,
  User,
} from '../types/auth.types'

export class AuthApi {
  static async login(credentials: LoginRequest): Promise<AuthResponse> {
    return apiClient.post<AuthResponse>(API_CONFIG.ENDPOINTS.AUTH.LOGIN, credentials)
  }

  static async register(userData: RegisterRequest): Promise<AuthResponse> {
    return apiClient.post<AuthResponse>(API_CONFIG.ENDPOINTS.AUTH.REGISTER, userData)
  }

  static async logout(): Promise<MessageResponse> {
    return apiClient.post<MessageResponse>(API_CONFIG.ENDPOINTS.AUTH.LOGOUT)
  }

  static async forgotPassword(data: ForgotPasswordRequest): Promise<MessageResponse> {
    return apiClient.post<MessageResponse>(API_CONFIG.ENDPOINTS.AUTH.FORGOT_PASSWORD, data)
  }

  static async getCurrentUser(): Promise<{ user: User }> {
    return apiClient.get<{ user: User }>(API_CONFIG.ENDPOINTS.AUTH.ME)
  }
}

export default AuthApi
import { UsersApi } from './users.api';
import type { 
  UserListItem, 
  UserProfile, 
  CreateUserRequest, 
  UpdateUserRequest,
  UserFilters 
} from '../types/users.types';

export class UserService {
  /**
   * Get all users with filters
   */
  static async getUsers(filters?: UserFilters) {
    const response = await UsersApi.getUsers(filters);
    return {
      data: response.data,
      meta: response.meta
    };
  }

  /**
   * Get user by ID
   */
  static async getUserById(userId: string) {
    const user = await UsersApi.getUser(userId);
    return { data: user };
  }

  /**
   * Create new user
   */
  static async createUser(userData: CreateUserRequest) {
    const user = await UsersApi.createUser(userData);
    return { data: user };
  }

  /**
   * Update user
   */
  static async updateUser(userId: string, userData: UpdateUserRequest) {
    const user = await UsersApi.updateUser(userId, userData);
    return { data: user };
  }

  /**
   * Delete user
   */
  static async deleteUser(userId: string) {
    return await UsersApi.deleteUser(userId);
  }

  /**
   * Get user statistics
   */
  static async getUserStats() {
    const stats = await UsersApi.getUserStats();
    return { data: stats };
  }

  /**
   * Get role statistics
   */
  static async getRoleStats() {
    const roleStats = await UsersApi.getRoleStats();
    return { data: roleStats };
  }

  /**
   * Export users data
   */
  static async exportUsers(format: 'csv' | 'xlsx' = 'csv', filters?: UserFilters) {
    const blob = await UsersApi.exportUsers(format, filters);
    
    // Create download link
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `users-export-${new Date().toISOString().split('T')[0]}.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    return { success: true };
  }

  /**
   * Upload user avatar
   */
  static async uploadAvatar(userId: string, file: File) {
    const result = await UsersApi.uploadAvatar(userId, file);
    return { data: result };
  }

  /**
   * Resend email verification
   */
  static async resendEmailVerification(userId: string) {
    return await UsersApi.resendEmailVerification(userId);
  }
}

// Export singleton instance
export const userService = UserService;
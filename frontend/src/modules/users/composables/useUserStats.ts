import { ref, computed } from 'vue';
import type { UserRole } from '@/modules/auth/types/auth.types';
import { userService } from '../api/user.service';

interface UserStats {
  total: number;
  active: number;
  admins: number;
  newThisMonth: number;
  recentLogins: number;
}

interface RoleStats {
  [key: string]: number;
}

const userStats = ref<UserStats>({
  total: 0,
  active: 0,
  admins: 0,
  newThisMonth: 0,
  recentLogins: 0
});

const roleStats = ref<RoleStats>({});

export function useUserStats() {
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const refreshStats = async () => {
    try {
      isLoading.value = true;
      error.value = null;

      // Fetch user statistics from the API
      const [statsResponse, roleStatsResponse] = await Promise.all([
        userService.getUserStats(),
        userService.getRoleStats()
      ]);

      userStats.value = statsResponse.data;
      roleStats.value = roleStatsResponse.data;
    } catch (err: any) {
      console.error('Failed to fetch user stats:', err);
      error.value = err.message || 'Failed to fetch user statistics';
      
      // Fallback to mock data for development
      userStats.value = {
        total: 156,
        active: 142,
        admins: 8,
        newThisMonth: 12,
        recentLogins: 45
      };
      
      roleStats.value = {
        admin: 8,
        project_manager: 24,
        supervisor: 48,
        field_worker: 76
      };
    } finally {
      isLoading.value = false;
    }
  };

  const getStatsForRole = (role: UserRole) => {
    return roleStats.value[role] || 0;
  };

  const getRolePercentage = (role: UserRole) => {
    if (!userStats.value.total) return 0;
    const roleCount = roleStats.value[role] || 0;
    return Math.round((roleCount / userStats.value.total) * 100);
  };

  const activeUserPercentage = computed(() => {
    if (!userStats.value.total) return 0;
    return Math.round((userStats.value.active / userStats.value.total) * 100);
  });

  const adminPercentage = computed(() => {
    if (!userStats.value.total) return 0;
    return Math.round((userStats.value.admins / userStats.value.total) * 100);
  });

  const growthRate = computed(() => {
    // Calculate monthly growth rate (mock calculation)
    if (!userStats.value.total || !userStats.value.newThisMonth) return 0;
    const previousMonthTotal = userStats.value.total - userStats.value.newThisMonth;
    if (previousMonthTotal === 0) return 100;
    return Math.round((userStats.value.newThisMonth / previousMonthTotal) * 100);
  });

  return {
    userStats: computed(() => userStats.value),
    roleStats: computed(() => roleStats.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    activeUserPercentage,
    adminPercentage,
    growthRate,
    refreshStats,
    getStatsForRole,
    getRolePercentage
  };
}
/**
 * Advanced Filtering System Demo
 * 
 * This file demonstrates how the enhanced API client processes and applies
 * filters when making requests to the backend API.
 */

import { UsersApi } from '../api/users.api'
import type { UserFilters } from '../types/users.types'

/**
 * Example filter configurations showing various combinations
 */
export const filterExamples: Record<string, UserFilters> = {
  // Basic search and role filtering
  basicFilter: {
    search: 'john',
    role: 'admin',
    page: 1,
    per_page: 25,
    use_cache: true
  },

  // Advanced multi-role filtering
  multiRoleFilter: {
    roles: ['admin', 'project_manager'],
    verified: true,
    active: true,
    sort_by: 'created_at',
    sort_direction: 'desc'
  },

  // Date range and department filtering
  dateRangeFilter: {
    created_from: '2024-01-01',
    created_to: '2024-12-31',
    department: 'construction',
    has_phone: true,
    email_domain: 'construction.com'
  },

  // Complex filtering with all options
  complexFilter: {
    search: 'project manager',
    role: 'project_manager',
    roles: ['project_manager', 'supervisor'],
    company_id: '123',
    verified: true,
    department: 'management',
    email_domain: 'company.com',
    has_phone: true,
    active: true,
    created_from: '2024-06-01',
    created_to: '2024-12-31',
    sort_by: 'name',
    sort_direction: 'asc',
    page: 2,
    per_page: 50,
    use_cache: false
  },

  // Performance optimized filter
  performanceFilter: {
    search: '',
    active: true,
    sort_by: 'updated_at',
    sort_direction: 'desc',
    per_page: 100,
    use_cache: true
  }
}

/**
 * Demonstration of how filters are processed by the API client
 */
export function demonstrateFilterProcessing() {
  console.group('🔍 Advanced Filtering System Demo')
  
  // Show how different filter types are processed
  console.log('📋 Filter Processing Examples:')
  
  // Example 1: Basic parameters
  console.log('\n1. Basic Filter Processing:')
  console.log('Input filters:', filterExamples.basicFilter)
  console.log('Expected API params:', {
    search: 'john',
    role: 'admin',
    page: '1',
    per_page: '25',
    use_cache: '1'
  })

  // Example 2: Array handling
  console.log('\n2. Array Parameter Processing:')
  console.log('Input roles array:', filterExamples.multiRoleFilter.roles)
  console.log('Expected API param:', 'admin,project_manager')

  // Example 3: Boolean handling
  console.log('\n3. Boolean Parameter Processing:')
  console.log('Input booleans:', {
    verified: filterExamples.multiRoleFilter.verified,
    active: filterExamples.multiRoleFilter.active,
    use_cache: filterExamples.performanceFilter.use_cache
  })
  console.log('Expected API params:', {
    verified: '1',
    active: '1',
    use_cache: '1'
  })

  // Example 4: Parameter cleaning
  console.log('\n4. Parameter Cleaning (removes empty/null/undefined):')
  const dirtyFilter = {
    search: '   trimmed   ',
    role: '',
    department: null,
    verified: undefined,
    active: true,
    empty_array: [],
    valid_array: ['admin']
  }
  console.log('Input dirty filter:', dirtyFilter)
  console.log('Expected cleaned params:', {
    search: 'trimmed',
    active: '1',
    valid_array: 'admin'
  })

  console.groupEnd()
}

/**
 * Example usage scenarios showing real API calls
 */
export class FilteringExamples {
  /**
   * Example: Load users with basic filtering
   */
  static async loadUsersWithBasicFilter() {
    console.log('🌐 Loading users with basic filter...')
    try {
      const users = await UsersApi.getUsers(filterExamples.basicFilter)
      console.log('✅ Users loaded:', users.data.length)
      console.log('📊 Metadata:', users.meta)
      return users
    } catch (error) {
      console.error('❌ Failed to load users:', error)
      throw error
    }
  }

  /**
   * Example: Load statistics with complex filtering
   */
  static async loadStatisticsWithComplexFilter() {
    console.log('📊 Loading statistics with complex filter...')
    try {
      const stats = await UsersApi.getUserStatistics(filterExamples.complexFilter)
      console.log('✅ Statistics loaded:', stats.stats)
      console.log('🎯 Applied filters:', stats.applied_filters)
      return stats
    } catch (error) {
      console.error('❌ Failed to load statistics:', error)
      throw error
    }
  }

  /**
   * Example: Bulk operations with filtering
   */
  static async performBulkActionWithFilter() {
    console.log('⚡ Performing bulk operations...')
    try {
      // First load users to get IDs
      const users = await UsersApi.getUsers(filterExamples.multiRoleFilter)
      const userIds = users.data.slice(0, 3).map(user => user.id)

      // Perform bulk verification
      const result = await UsersApi.bulkAction({
        action: 'verify',
        user_ids: userIds
      })

      console.log('✅ Bulk action completed:', result)
      return result
    } catch (error) {
      console.error('❌ Bulk action failed:', error)
      throw error
    }
  }

  /**
   * Example: Export with advanced filtering
   */
  static async exportWithAdvancedFiltering() {
    console.log('📁 Exporting users with advanced filtering...')
    try {
      const exportResult = await UsersApi.exportUsers('csv', filterExamples.dateRangeFilter)
      console.log('✅ Export completed:', exportResult)
      return exportResult
    } catch (error) {
      console.error('❌ Export failed:', error)
      throw error
    }
  }

  /**
   * Demonstrate performance optimization with caching
   */
  static async demonstratePerformanceOptimization() {
    console.log('⚡ Demonstrating performance optimization...')
    
    const startTime = performance.now()
    
    try {
      // First call - will hit API and cache result
      console.log('📞 First API call (cache miss expected)...')
      await UsersApi.getUsers(filterExamples.performanceFilter)
      
      const firstCallTime = performance.now() - startTime
      console.log(`⏱️ First call took: ${firstCallTime.toFixed(2)}ms`)

      // Second call - should use cache
      console.log('📞 Second API call (cache hit expected)...')
      const secondStartTime = performance.now()
      await UsersApi.getUsers(filterExamples.performanceFilter)
      
      const secondCallTime = performance.now() - secondStartTime
      console.log(`⏱️ Second call took: ${secondCallTime.toFixed(2)}ms`)
      
      const speedup = firstCallTime / secondCallTime
      console.log(`🚀 Speed improvement: ${speedup.toFixed(1)}x faster`)

    } catch (error) {
      console.error('❌ Performance test failed:', error)
      throw error
    }
  }
}

/**
 * Run all filter demonstrations
 */
export async function runFilteringDemo() {
  console.log('🎬 Starting Advanced Filtering System Demo')
  
  // Show filter processing logic
  demonstrateFilterProcessing()
  
  // Run API examples
  try {
    await FilteringExamples.loadUsersWithBasicFilter()
    await FilteringExamples.loadStatisticsWithComplexFilter()
    await FilteringExamples.performBulkActionWithFilter()
    await FilteringExamples.exportWithAdvancedFiltering()
    await FilteringExamples.demonstratePerformanceOptimization()
    
    console.log('🎉 All filtering demos completed successfully!')
  } catch (error) {
    console.error('💥 Demo failed:', error)
  }
}

// Export for use in components or testing
export { UsersApi }
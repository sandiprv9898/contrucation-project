<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="min-h-screen flex">
      <!-- Marketing Section - Left Side -->
      <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
        <!-- Modern Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-secondary-900"></div>
        
        <!-- Subtle Pattern Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent"></div>
        
        <div class="relative z-10 flex items-center justify-center w-full p-12">
          <div class="max-w-lg">
            <!-- Logo/Brand -->
            <div class="mb-12">
              <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm mr-4">
                  <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">ConstructFlow</h1>
              </div>
              
              <h2 class="text-4xl font-bold mb-4 leading-tight text-white">
                Build Better.<br/>
                <span class="text-primary-200">Manage Smarter.</span>
              </h2>
              <p class="text-xl text-white/80 leading-relaxed">
                The modern construction management platform that keeps your projects on track, your team connected, and your budgets in control.
              </p>
            </div>

            <!-- Key Features -->
            <div class="space-y-6 mb-12">
              <div class="flex items-start gap-4 group">
                <div class="w-11 h-11 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0 backdrop-blur-sm group-hover:bg-white/30 transition-colors duration-200">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-lg text-white mb-1">Real-time Project Tracking</h3>
                  <p class="text-white/70 text-sm leading-relaxed">Monitor progress, deadlines, and milestones with live updates across all your construction projects.</p>
                </div>
              </div>

              <div class="flex items-start gap-4 group">
                <div class="w-11 h-11 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0 backdrop-blur-sm group-hover:bg-white/30 transition-colors duration-200">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-lg text-white mb-1">Team Collaboration</h3>
                  <p class="text-white/70 text-sm leading-relaxed">Connect field workers, project managers, and clients in one unified platform.</p>
                </div>
              </div>

              <div class="flex items-start gap-4 group">
                <div class="w-11 h-11 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0 backdrop-blur-sm group-hover:bg-white/30 transition-colors duration-200">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold text-lg text-white mb-1">Smart Budget Management</h3>
                  <p class="text-white/70 text-sm leading-relaxed">Track expenses, manage resources, and get predictive insights to stay within budget.</p>
                </div>
              </div>
            </div>

            <!-- Trust Indicators -->
            <div class="border-t border-white/20 pt-8">
              <p class="text-white/60 text-sm mb-6 font-medium">Trusted by 2,500+ construction teams worldwide</p>
              <div class="grid grid-cols-3 gap-8">
                <div class="text-center">
                  <div class="text-2xl font-bold text-white mb-1">2.5K+</div>
                  <div class="text-white/60 text-sm">Active Projects</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-white mb-1">99.9%</div>
                  <div class="text-white/60 text-sm">Uptime</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-white mb-1">4.9★</div>
                  <div class="text-white/60 text-sm">User Rating</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Auth Forms Section - Right Side -->
      <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
          <!-- Mobile Logo -->
          <div class="lg:hidden text-center mb-8">
            <div class="inline-flex items-center mb-4">
              <div class="w-10 h-10 bg-primary-500 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
              </div>
              <span class="text-xl font-bold text-secondary-900">ConstructFlow</span>
            </div>
          </div>

          <!-- Form Header -->
          <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-secondary-900 mb-2">
              {{ getFormTitle() }}
            </h2>
            <p class="text-gray-600 text-sm leading-relaxed">
              {{ getWelcomeMessage() }}
            </p>
          </div>

          <!-- Form Container -->
          <div class="bg-white rounded-2xl shadow-xl shadow-gray-100/50 border border-gray-200/50 p-8">
            <router-view />
          </div>

          <!-- Footer -->
          <div class="text-center mt-6">
            <p class="text-xs text-gray-500">
              By continuing, you agree to our 
              <a href="#" class="text-primary-600 hover:text-primary-700 underline">Terms of Service</a>
              and 
              <a href="#" class="text-primary-600 hover:text-primary-700 underline">Privacy Policy</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'

const route = useRoute()

const getFormTitle = () => {
  const routeName = route.name as string
  
  switch (routeName) {
    case 'Login':
      return 'Welcome back'
    case 'Register':
      return 'Create your account'
    case 'ForgotPassword':
      return 'Reset password'
    default:
      return 'Welcome'
  }
}

const getWelcomeMessage = () => {
  const routeName = route.name as string
  
  switch (routeName) {
    case 'Login':
      return 'Enter your credentials to access your dashboard'
    case 'Register':
      return 'Join thousands of construction professionals using ConstructFlow'
    case 'ForgotPassword':
      return 'Enter your email address and we\'ll send you a reset link'
    default:
      return 'Access your construction management platform'
  }
}
</script>

<style scoped>
/* Mobile responsive adjustments */
@media (max-width: 1023px) {
  .min-h-screen {
    flex-direction: column;
  }
}
</style>
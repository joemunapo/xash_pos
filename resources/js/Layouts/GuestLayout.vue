<template>
  <div class="h-screen flex flex-col bg-white overflow-hidden">
    <!-- Animated background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-brand-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-pulse"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-brand-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-4 py-4 overflow-y-auto">
      <div class="w-full">
        <!-- Slot for page content -->
        <slot />
      </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-20 py-4 border-t border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-gradient-to-br from-brand-500 to-brand-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
            <span class="text-gray-900 font-semibold">XASH<span class="text-brand-500">POS</span></span>
          </div>
          <p class="text-gray-500 text-sm">© {{ new Date().getFullYear() }} XASH POS. Built for you.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { useFlashMessages } from '@/composables/useFlashMessages';

// Initialize flash messages
useFlashMessages();
</script>

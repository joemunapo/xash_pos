<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-brand-50 to-brand-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 flex items-center justify-center px-4 py-12">
    <!-- Animated background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-brand-200 dark:bg-brand-900/20 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-success-200 dark:bg-success-900/20 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 w-full">
      <!-- Logo/Brand section -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-brand-600 to-brand-600 rounded-lg flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
          </div>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">XASH<span class="text-brand-500">POS</span></h1>
      </div>

      <!-- Slot for page content -->
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useFlashMessages } from '@/Composables/useFlashMessages';

// Initialize flash message watcher
useFlashMessages();
</script>

<style scoped>
/* Additional animations if needed */
</style>

<template>
  <div class="h-screen flex flex-col bg-white dark:bg-slate-900 overflow-hidden">
    <!-- Animated background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-brand-500/20 dark:bg-brand-400/10 rounded-full mix-blend-multiply dark:mix-blend-normal filter blur-3xl opacity-70 animate-pulse"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-brand-500/20 dark:bg-brand-400/10 rounded-full mix-blend-multiply dark:mix-blend-normal filter blur-3xl opacity-70 animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-4 py-4 overflow-y-auto">
      <div class="w-full">
        <!-- Slot for page content -->
        <slot />
      </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-20 py-4 border-t border-gray-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="flex items-center gap-2">
            <img 
              src="/logo.png" 
              alt="XASH POS" 
              class="h-8 w-auto object-contain dark:hidden"
            />
            <img 
              src="/logo-white.png" 
              alt="XASH POS" 
              class="h-8 w-auto object-contain hidden dark:block"
            />
          </div>
          <p class="text-gray-500 dark:text-gray-400 text-sm">© {{ new Date().getFullYear() }} XASH POS. Built for you.</p>
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

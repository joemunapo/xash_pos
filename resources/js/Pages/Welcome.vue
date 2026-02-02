<template>
  <GuestLayout>
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="relative">
        <div class="text-center">
          <!-- Logo -->
          <div class="flex justify-center mb-8">
            <div class="relative">
              <img 
                src="/logo.png" 
                alt="XASH POS Logo" 
                class="h-24 w-auto object-contain dark:hidden"
              />
              <img 
                src="/logo-white.png" 
                alt="XASH POS Logo" 
                class="h-24 w-auto object-contain hidden dark:block"
              />
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
            Modern Point of Sale System
          </h1>

          <p class="text-xl sm:text-2xl text-gray-700 dark:text-gray-300 mb-4 max-w-3xl mx-auto">
            Modern Point of Sale for Zimbabwe Grocery Stores
          </p>

          <p class="text-gray-600 dark:text-gray-400 mb-12 max-w-2xl mx-auto">
            Fast, reliable, and built for offline-first operations. Optimized for Sunmi devices with multi-currency support (USD/ZWL) and mobile money integrations.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link :href="route('login')" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-brand-500 to-brand-600 rounded-xl hover:from-brand-600 hover:to-brand-700 transition-all shadow-lg shadow-brand-500/30 hover:shadow-brand-500/50">
              <i class="fas fa-sign-in-alt mr-3"></i>
              Sign In to Dashboard
            </Link>
          </div>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  canLogin: Boolean,
  laravelVersion: String,
  phpVersion: String,
});
</script>

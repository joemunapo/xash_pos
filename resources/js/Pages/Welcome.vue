<template>
  <GuestLayout>
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="relative">
        <div class="text-center">
          <!-- Logo -->
          <div class="flex justify-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-emerald-500/30">
              <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
            XASH<span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-600">POS</span>
          </h1>

          <p class="text-xl sm:text-2xl text-gray-700 mb-4 max-w-3xl mx-auto">
            Modern Point of Sale for Zimbabwe Grocery Stores
          </p>

          <p class="text-gray-600 mb-12 max-w-2xl mx-auto">
            Fast, reliable, and built for offline-first operations. Optimized for Sunmi devices with multi-currency support (USD/ZWL) and mobile money integrations.
          </p>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link :href="route('login')" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50">
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

<template>
  <GuestLayout>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8">
        <!-- Mobile Icon -->
        <div class="flex justify-center mb-6">
          <div class="w-20 h-20 bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-500/30">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-3xl font-bold text-gray-900 mb-2 text-center">
          {{ title }}
        </h1>

        <!-- Role Badge -->
        <div class="flex justify-center mb-6">
          <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-brand-100 text-brand-800">
            <i class="fas fa-user-tag mr-2"></i>
            {{ role }}
          </span>
        </div>

        <!-- Message -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-blue-800">
                {{ message }}
              </p>
            </div>
          </div>
        </div>

        <!-- Instructions -->
        <div class="space-y-4 mb-8">
          <h2 class="text-lg font-semibold text-gray-900">Getting Started:</h2>
          <ol class="list-decimal list-inside space-y-2 text-gray-700">
            <li>Download the XASH POS Mobile App from your app store</li>
            <li>Login with your credentials ({{ $page.props.auth?.user?.email }})</li>
            <li>Start managing your operations on the go</li>
          </ol>
        </div>

        <!-- Features List -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <h3 class="text-sm font-semibold text-gray-900 mb-3">Mobile App Features:</h3>
          <ul class="space-y-2 text-sm text-gray-700">
            <li class="flex items-start">
              <i class="fas fa-check text-brand-500 mr-2 mt-0.5"></i>
              <span>Offline-first operations</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-check text-brand-500 mr-2 mt-0.5"></i>
              <span>Real-time inventory tracking</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-check text-brand-500 mr-2 mt-0.5"></i>
              <span>Sales management and reporting</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-check text-brand-500 mr-2 mt-0.5"></i>
              <span>Multi-currency support (USD/ZWL)</span>
            </li>
          </ul>
        </div>

        <!-- Logout Button -->
        <div class="flex justify-center">
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
          >
            <i class="fas fa-sign-out-alt mr-2"></i>
            Logout
          </Link>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    required: true,
  },
  role: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    required: true,
  },
});
</script>

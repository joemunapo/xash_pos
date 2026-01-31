<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] p-6 pb-8">
      <button @click="goBack" class="mb-4 p-2 rounded-full hover:bg-white/10 transition-colors">
        <i class="fa-solid fa-arrow-left text-white text-lg"></i>
      </button>
      <h1 class="text-white text-2xl font-bold">Zimbabwe Mobile</h1>
      <p class="text-white/60 text-sm mt-1">NetOne, Econet, Telecel services</p>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-6 relative z-20">
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <!-- Services List -->
        <div class="space-y-3">
          <button
            @click="navigateTo('/recharge/netone')"
            class="w-full flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition-all border border-gray-200 text-left"
          >
            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 bg-blue-100">
              <i class="fa-solid fa-mobile text-xl text-blue-600"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900">NetOne</h3>
              <p class="text-xs text-gray-500">Airtime & Data</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-400 text-sm"></i>
          </button>

          <button
            @click="navigateTo('/recharge/econet')"
            class="w-full flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition-all border border-gray-200 text-left"
          >
            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 bg-purple-100">
              <i class="fa-solid fa-mobile text-xl text-purple-600"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900">Econet</h3>
              <p class="text-xs text-gray-500">Airtime & Data</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-400 text-sm"></i>
          </button>

          <button
            @click="navigateTo('/recharge/telecel')"
            class="w-full flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition-all border border-gray-200 text-left"
          >
            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 bg-cyan-100">
              <i class="fa-solid fa-mobile text-xl text-cyan-600"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-900">Telecel</h3>
              <p class="text-xs text-gray-500">Airtime & Data</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-400 text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Info Section -->
      <div class="mt-6 bg-blue-50 rounded-lg p-4 border border-blue-200">
        <div class="flex gap-3">
          <i class="fa-solid fa-circle-info text-blue-600 text-lg flex-shrink-0 mt-0.5"></i>
          <div class="text-sm text-blue-900">
            <p class="font-semibold mb-1">Zimbabwe Mobile Services</p>
            <p class="text-xs">Select your provider to buy airtime or data bundles</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = () => {
  router.back()
}

const navigateTo = (path) => {
  router.push(path)
}
</script>

<style scoped>
/* Smooth transitions */
button {
  transition: all 0.2s ease;
}

button:active {
  transform: scale(0.98);
}
</style>

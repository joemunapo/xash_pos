<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6">
        <div class="flex items-center gap-4 mb-4">
          <button @click="goBack" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fa-solid fa-arrow-left text-white text-lg"></i>
          </button>
          <div class="mb-5">
            <h1 class="text-white text-xl font-semibold">NetOne</h1>
            <p class="text-white/60 text-sm">What would you like to buy?</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-12 relative z-20">
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <!-- Provider Logo/Icon -->
        <div class="flex justify-center mb-6">
          <div class="w-20 h-20 rounded-2xl flex items-center justify-center bg-green-100">
            <i class="fa-solid fa-tower-cell text-3xl text-green-600"></i>
          </div>
        </div>

        <!-- Options -->
        <div class="space-y-4">
          <!-- Airtime Option -->
          <button
            @click="navigateTo('/recharge/netone/airtime')"
            class="w-full flex items-center gap-4 p-5 rounded-xl border-2 border-gray-100 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/5 transition-all"
          >
            <div class="w-14 h-14 rounded-lg flex items-center justify-center bg-blue-100">
              <i class="fa-solid fa-phone text-2xl text-blue-600"></i>
            </div>
            <div class="flex-1 text-left">
              <h3 class="font-semibold text-lg text-[var(--fluxr-dark)]">Buy Airtime</h3>
              <p class="text-sm text-gray-500">Top up your phone balance</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-400"></i>
          </button>
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
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>

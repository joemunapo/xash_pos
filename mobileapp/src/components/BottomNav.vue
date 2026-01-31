<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 pb-safe">
    <div class="max-w-screen-xl mx-auto">
      <div class="grid grid-cols-4 gap-1">
        <router-link
          v-for="item in navItems"
          :key="item.name"
          :to="item.path"
          class="flex flex-col items-center justify-center py-3 px-2 transition-colors"
          :class="isActive(item.path) ? 'text-[var(--fluxr-green-dark)]' : 'text-gray-500'"
        >
          <i :class="`${item.icon} text-xl mb-1`"></i>
          <span class="text-xs font-medium">{{ item.label }}</span>
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const navItems = [
  {
    name: 'home',
    label: 'Home',
    icon: 'fa-solid fa-house',
    path: '/dashboard'
  },
  {
    name: 'wallet',
    label: 'My Wallet',
    icon: 'fa-solid fa-wallet',
    path: '/wallet'
  },
  {
    name: 'services',
    label: 'Services',
    icon: 'fa-solid fa-grip',
    path: '/services'
  },
  {
    name: 'profile',
    label: 'Profile',
    icon: 'fa-solid fa-user',
    path: '/profile'
  }
]

const isActive = (path) => {
  return route.path === path
}
</script>

<style scoped>
nav {
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}
</style>

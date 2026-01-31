<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
    <div class="max-w-screen-xl mx-auto">
      <div class="grid grid-cols-5 gap-1">
        <router-link
          v-for="item in navItems"
          :key="item.name"
          :to="item.path"
          class="flex flex-col items-center justify-center py-2.5 px-2 transition-colors"
          :class="isActive(item.path) ? 'text-blue-600 bg-blue-50' : 'text-gray-500'"
        >
          <i :class="`${item.icon} text-lg mb-1`"></i>
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
    name: 'dashboard',
    label: 'Dashboard',
    icon: 'fa-solid fa-chart-line',
    path: '/manager/dashboard'
  },
  {
    name: 'sales',
    label: 'Sales',
    icon: 'fa-solid fa-receipt',
    path: '/manager/sales'
  },
  {
    name: 'reports',
    label: 'Reports',
    icon: 'fa-solid fa-file-chart',
    path: '/manager/reports'
  },
  {
    name: 'cashiers',
    label: 'Cashiers',
    icon: 'fa-solid fa-users',
    path: '/manager/cashiers'
  },
  {
    name: 'profile',
    label: 'Profile',
    icon: 'fa-solid fa-user-circle',
    path: '/manager/profile'
  }
]

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}
</script>

<style scoped>
nav {
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}
</style>

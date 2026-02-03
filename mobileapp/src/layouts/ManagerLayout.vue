<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Top Navbar -->
    <nav class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-30 pt-safe">
      <div class="px-4 py-3">
        <div class="flex items-center justify-between">
          <!-- Left: Menu toggle + Branch -->
          <div class="flex items-center gap-3">
            <button
              @click="toggleSidebar"
              class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition"
            >
              <i class="fas fa-bars text-gray-600"></i>
            </button>
            <div class="flex items-center gap-2">
              <i class="fas fa-store text-primary"></i>
              <div>
                <h1 class="text-sm font-semibold text-gray-800">{{ branch?.name || 'No Branch' }}</h1>
              </div>
            </div>
          </div>

          <!-- Right: User info + Logout -->
          <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-medium text-gray-800">{{ user?.name }}</p>
              <p class="text-xs text-gray-500">{{ user?.email }}</p>
            </div>
            <div class="w-10 h-10 bg-primary-light rounded-full flex items-center justify-center">
              <i class="fas fa-user text-primary"></i>
            </div>
            <button
              @click="handleLogout"
              class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition"
              title="Logout"
            >
              <i class="fas fa-sign-out-alt"></i>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed left-0 bottom-0 w-64 bg-white border-r border-gray-200 z-20 transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
      style="top: calc(53px + var(--sat));"
    >
      <div class="py-4">
        <br>
        <nav class="space-y-1 px-3">
          <router-link
            v-for="item in navItems"
            :key="item.name"
            :to="item.path"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="isActive(item.path)
              ? 'bg-primary-light text-primary font-medium'
              : 'text-gray-600 hover:bg-gray-50'"
            @click="closeSidebarOnMobile"
          >
            <i :class="`${item.icon} text-lg w-5`"></i>
            <span>{{ item.label }}</span>
          </router-link>
        </nav>
      </div>

      <!-- Sidebar Footer -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-gray-50">
        <div class="text-xs text-gray-500 text-center">
          <p class="font-medium">XASH POS Manager</p>
          <p>Version 1.0.0</p>
        </div>
      </div>
    </aside>

    <!-- Overlay for mobile -->
    <div
      v-if="sidebarOpen"
      @click="closeSidebar"
      class="fixed inset-0 bg-black/50 z-10 lg:hidden"
    ></div>

    <!-- Main Content -->
    <main
      class="lg:pl-64 min-h-screen transition-all duration-300"
      style="padding-top: calc(53px + var(--sat));"
    >
      <div class="p-4">
        <br />
        <router-view />
      </div>
    </main>

    <!-- Logout Confirmation Modal -->
    <Transition name="fade">
      <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="showLogoutModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full p-6">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
              <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm Logout</h3>
            <p class="text-sm text-gray-500 mb-6">
              Are you sure you want to log out?
            </p>
            <div class="flex gap-3">
              <button
                @click="showLogoutModal = false"
                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition"
              >
                Cancel
              </button>
              <button
                @click="confirmLogout"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition"
              >
                Yes, Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const sidebarOpen = ref(false)
const showLogoutModal = ref(false)

const user = computed(() => authStore.user)
const branch = computed(() => authStore.user?.branch)

const navItems = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    icon: 'fas fa-home',
    path: '/manager/dashboard'
  },
  {
    name: 'sales',
    label: 'Sales Management',
    icon: 'fas fa-receipt',
    path: '/manager/sales'
  },
  {
    name: 'products',
    label: 'Products',
    icon: 'fas fa-box',
    path: '/manager/products'
  },
  {
    name: 'inventory',
    label: 'Inventory',
    icon: 'fas fa-warehouse',
    path: '/manager/inventory'
  },
  {
    name: 'reports',
    label: 'Reports',
    icon: 'fas fa-chart-line',
    path: '/manager/reports/sales-summary'
  },
  {
    name: 'cashiers',
    label: 'Cashiers',
    icon: 'fas fa-users',
    path: '/manager/cashiers'
  },
  {
    name: 'profile',
    label: 'My Profile',
    icon: 'fas fa-user-circle',
    path: '/manager/profile'
  }
]

function isActive(path) {
  return route.path === path || route.path.startsWith(path + '/')
}

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
  sidebarOpen.value = false
}

function closeSidebarOnMobile() {
  // Close sidebar on mobile after navigation
  if (window.innerWidth < 1024) {
    closeSidebar()
  }
}

function handleLogout() {
  showLogoutModal.value = true
}

async function confirmLogout() {
  showLogoutModal.value = false
  await authStore.logout()
}
</script>

<style scoped>
/* Smooth transitions */
aside {
  transition: transform 0.3s ease-in-out;
}

/* Fade transition for modal */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

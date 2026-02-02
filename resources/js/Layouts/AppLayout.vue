<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Sidebar Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed left-0 top-0 h-full z-50 w-64 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-700',
        'transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-slate-700 flex-shrink-0">
        <Link :href="route('welcome')" class="flex items-center gap-2 group">
          <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-brand-600 rounded-xl flex items-center justify-center shadow-lg">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
          </div>
          <span class="text-lg font-bold text-gray-900 dark:text-white">XASH<span class="text-brand-500">POS</span></span>
        </Link>
        <button
          @click="sidebarOpen = false"
          class="lg:hidden p-1 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800"
        >
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="overflow-y-auto px-3 py-4 space-y-1 flex-1">
        <NavLink
          :href="isAdmin ? route('admin.dashboard') : route('user.dashboard')"
          :active="route().current('admin.dashboard') || route().current('user.dashboard')"
          icon="fa-chart-line"
        >
          Dashboard
        </NavLink>

        <template v-if="isAdmin">
          <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Store Management</p>
            <NavLink :href="route('admin.branches.index')" :active="route().current('admin.branches.*')" icon="fa-store" class="mt-2">Branches</NavLink>
            <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')" icon="fa-users">Users</NavLink>
          </div>

          <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catalog</p>
            <NavLink :href="route('admin.products.index')" :active="route().current('admin.products.*')" icon="fa-box" class="mt-2">Products</NavLink>
            <NavLink :href="route('admin.categories.index')" :active="route().current('admin.categories.*')" icon="fa-tags">Categories</NavLink>
          </div>

          <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customers</p>
            <NavLink :href="route('admin.customers.index')" :active="route().current('admin.customers.*')" icon="fa-user-friends" class="mt-2">Customers</NavLink>
          </div>

          <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">System</p>
            <NavLink :href="route('admin.settings.index')" :active="route().current('admin.settings.*')" icon="fa-cog" class="mt-2">Settings</NavLink>
          </div>
        </template>

        <template v-else>
          <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</p>
            <NavLink :href="route('user.profile.edit')" :active="route().current('user.profile.edit')" icon="fa-user-circle" class="mt-2">Profile Settings</NavLink>
          </div>
        </template>
      </nav>

      <!-- User Menu -->
      <div class="border-t border-gray-200 dark:border-slate-700 p-3 bg-white dark:bg-slate-900 relative flex-shrink-0">
        <button
          @click="userMenuOpen = !userMenuOpen"
          class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-500 flex items-center justify-center text-white text-sm font-semibold shadow">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div class="text-left min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 capitalize truncate">{{ user.role }}</p>
            </div>
          </div>
          <i :class="`fas fa-chevron-down transition-transform text-xs text-gray-500 ${userMenuOpen ? 'rotate-180' : ''}`"></i>
        </button>

        <!-- User Dropdown -->
        <div v-if="userMenuOpen" class="absolute bottom-full mb-2 left-0 right-0 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-gray-200 dark:border-slate-700 z-10 mx-2 overflow-hidden">
          <Link :href="route('user.profile.edit')" @click="userMenuOpen = false" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-user-circle w-4"></i> Profile
          </Link>
          <form @submit.prevent="logout" class="w-full">
            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
              <i class="fas fa-sign-out-alt w-4"></i> Sign Out
            </button>
          </form>
        </div>
      </div>
    </aside>

    <!-- Main Content (No top nav) -->
    <div class="lg:ml-64">
      <!-- Mobile Header Only -->
      <header class="h-14 lg:hidden bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between px-4 sticky top-0 z-40">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800">
          <i class="fas fa-bars text-xl"></i>
        </button>
        <span class="text-lg font-bold text-gray-900 dark:text-white">XASH<span class="text-brand-500">POS</span></span>
        <div class="w-10"></div>
      </header>

      <!-- Page Content -->
      <main class="p-4 lg:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const page = usePage();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');

onMounted(() => {
  // Apply dark mode from localStorage
  if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
});

const logout = () => {
  router.post(route('logout'));
};
</script>

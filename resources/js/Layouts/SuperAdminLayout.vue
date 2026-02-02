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
        'fixed left-0 top-0 h-full z-50 bg-white dark:bg-slate-900/95 backdrop-blur-xl border-r border-gray-100 dark:border-slate-800',
        'transform transition-all duration-300 ease-in-out lg:translate-x-0 flex flex-col shadow-xl',
        miniSidebar ? 'w-20' : 'w-64',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100 dark:border-slate-800 flex-shrink-0 bg-gradient-to-r from-gray-50/50 to-transparent dark:from-slate-800/50">
        <div :class="['flex items-center flex-1', miniSidebar ? 'justify-center' : 'gap-2']">
          <Link :href="route('superadmin.dashboard')" :class="['flex items-center group', miniSidebar ? 'justify-center' : 'gap-2.5']">
            <img 
              v-if="!miniSidebar"
              src="/logo.png" 
              alt="XASH POS Logo" 
              class="h-10 w-auto object-contain dark:hidden group-hover:scale-105 transition-transform"
            />
            <img 
              v-if="!miniSidebar"
              src="/logo-white.png" 
              alt="XASH POS Logo" 
              class="h-10 w-auto object-contain hidden dark:block group-hover:scale-105 transition-transform"
            />
            <div v-if="miniSidebar" class="w-10 h-10 bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-500/30 flex-shrink-0 group-hover:scale-105 transition-transform">
              <i class="fas fa-shield-alt text-white text-sm"></i>
            </div>
          </Link>
        </div>

        <button
          @click="toggleMiniSidebar"
          class="hidden lg:flex items-center justify-center ml-2 w-8 h-8 rounded-lg bg-brand-50 dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400 transition-all flex-shrink-0"
          :title="miniSidebar ? 'Expand Sidebar' : 'Collapse Sidebar'"
        >
          <i :class="`fas ${miniSidebar ? 'fa-angle-right' : 'fa-angle-left'} text-sm`"></i>
        </button>

        <button
          @click="sidebarOpen = false"
          class="lg:hidden p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800"
        >
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>

      <!-- Super Admin Badge -->
      <div v-if="!miniSidebar" class="mx-3 mt-3 mb-1 px-3 py-1.5 bg-brand-50 dark:bg-brand-900/20 rounded-lg border border-brand-100 dark:border-brand-800/30">
        <p class="text-xs font-semibold text-brand-700 dark:text-brand-300 flex items-center gap-1.5">
          Super Admin Portal
        </p>
      </div>

      <!-- Navigation -->
      <nav ref="sidebarNav" class="overflow-y-auto overflow-x-hidden px-3 py-4 space-y-1 flex-1 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-slate-700 scrollbar-track-transparent">
        <NavLink
          :href="route('superadmin.dashboard')"
          :active="route().current('superadmin.dashboard')"
          icon="fa-chart-line"
        >
          Dashboard
        </NavLink>

        <NavLink
          :href="route('superadmin.tenants.index')"
          :active="route().current('superadmin.tenants.*')"
          icon="fa-building"
        >
          Tenants
        </NavLink>

        <NavLink
          :href="route('superadmin.plans.index')"
          :active="route().current('superadmin.plans.*')"
          icon="fa-credit-card"
        >
          Subscription Plans
        </NavLink>
      </nav>

      <!-- User Menu -->
      <div class="border-t border-gray-100 dark:border-slate-800 p-3 bg-gradient-to-r from-gray-50/50 to-transparent dark:from-slate-800/50 relative flex-shrink-0">
        <button
          @click="userMenuOpen = !userMenuOpen"
          :class="[
            'w-full flex items-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-all duration-300',
            miniSidebar ? 'justify-center' : 'justify-between',
            userMenuOpen ? 'bg-gray-100 dark:bg-slate-800' : ''
          ]"
        >
          <div :class="['flex items-center', miniSidebar ? '' : 'gap-3']">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 via-brand-500 to-brand-600 flex items-center justify-center text-white text-sm font-bold shadow-lg ring-2 ring-white dark:ring-slate-900">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div v-if="!miniSidebar" class="text-left min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-brand-600 dark:text-brand-400 font-medium">Super Admin</p>
            </div>
          </div>
          <i v-if="!miniSidebar" :class="`fas fa-chevron-down transition-all duration-300 text-xs text-gray-400 ${userMenuOpen ? 'rotate-180 text-brand-500' : ''}`"></i>
        </button>

        <!-- User Dropdown -->
        <div v-if="userMenuOpen" :class="[
          'absolute bottom-full mb-3 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-slate-700 z-10 overflow-hidden',
          miniSidebar ? 'left-full ml-3 bottom-0' : 'left-3 right-3'
        ]">
          <Link :href="route('admin.dashboard')" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400 transition-all whitespace-nowrap">
            <i class="fas fa-store text-base"></i>
            <span>Admin Panel</span>
          </Link>
          <div class="h-px bg-gray-100 dark:bg-slate-700"></div>
          <form @submit.prevent="logout" class="w-full">
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-all whitespace-nowrap">
              <i class="fas fa-sign-out-alt text-base"></i>
              <span>Sign Out</span>
            </button>
          </form>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div :class="['transition-all duration-300', miniSidebar ? 'lg:ml-20' : 'lg:ml-64']">
      <!-- Mobile Header -->
      <header class="h-14 lg:hidden bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between px-4 sticky top-0 z-40">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800">
          <i class="fas fa-bars text-xl"></i>
        </button>
        <img src="/logo.png" alt="XASH POS" class="h-8 w-auto object-contain dark:hidden" />
        <img src="/logo-white.png" alt="XASH POS" class="h-8 w-auto object-contain hidden dark:block" />
        <div class="w-10"></div>
      </header>

      <!-- Page Header (Desktop) -->
      <header v-if="pageTitle" class="hidden lg:block bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ pageTitle }}</h1>
      </header>

      <!-- Page Content -->
      <main class="p-4 lg:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, provide, watch, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import { useFlashMessages } from '@/composables/useFlashMessages';

defineProps({
  pageTitle: {
    type: String,
    default: null,
  },
});

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const miniSidebar = ref(false);
const activeTooltip = ref(null);
const sidebarNav = ref(null);
const page = usePage();

const user = computed(() => page.props.auth.user);

useFlashMessages();

provide('miniSidebar', miniSidebar);
provide('activeTooltip', activeTooltip);

onMounted(() => {
  if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }

  const savedMiniState = localStorage.getItem('superadminSidebarMini');
  if (savedMiniState !== null) {
    miniSidebar.value = savedMiniState === 'true';
  }
});

watch(miniSidebar, (newValue) => {
  localStorage.setItem('superadminSidebarMini', newValue.toString());
});

const toggleMiniSidebar = () => {
  miniSidebar.value = !miniSidebar.value;
  userMenuOpen.value = false;
};

const logout = () => {
  router.post(route('logout'));
};
</script>

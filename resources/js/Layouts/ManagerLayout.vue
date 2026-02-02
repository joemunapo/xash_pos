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
          <Link :href="route('manager.dashboard')" :class="['flex items-center group', miniSidebar ? '' : 'gap-2.5']">
            <div class="w-10 h-10 bg-gradient-to-br from-primary to-brand-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-500/30 flex-shrink-0 group-hover:scale-105 transition-transform">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
            <span v-if="!miniSidebar" class="text-lg font-bold bg-gradient-to-r from-gray-900 to-gray-700 dark:from-white dark:to-gray-300 bg-clip-text text-transparent whitespace-nowrap">XASH<span class="bg-gradient-to-r from-primary to-brand-500 bg-clip-text">POS</span></span>
          </Link>
        </div>

        <!-- Toggle Button (Desktop Only) - Always visible -->
        <button
          @click="toggleMiniSidebar"
          class="hidden lg:flex items-center justify-center ml-2 w-8 h-8 rounded-lg bg-brand-50 dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:bg-brand-100 dark:hover:bg-brand-900/20 hover:text-primary dark:hover:text-brand-400 transition-all flex-shrink-0"
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

      <!-- Navigation -->
      <nav ref="sidebarNav" class="overflow-y-auto overflow-x-hidden px-3 py-4 space-y-1 flex-1 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-slate-700 scrollbar-track-transparent">
        <!-- Dashboard -->
        <NavLink
          :href="route('manager.dashboard')"
          :active="route().current('manager.dashboard')"
          icon="fa-chart-line"
        >
          Dashboard
        </NavLink>

        <!-- Cashiers -->
        <NavGroup
          title="Cashiers"
          icon="fa-users"
          :active-patterns="['/manager/cashiers']"
        >
          <NavSubLink :href="route('manager.cashiers.index')" :active="route().current('manager.cashiers.index') || route().current('manager.cashiers.edit')" icon="fa-list">
            All Cashiers
          </NavSubLink>
          <NavSubLink :href="route('manager.cashiers.create')" :active="route().current('manager.cashiers.create')" icon="fa-plus">
            Add Cashier
          </NavSubLink>

          <template #mini>
            <NavSubLink :href="route('manager.cashiers.index')" :active="route().current('manager.cashiers.index') || route().current('manager.cashiers.edit')" icon="fa-list">
              All Cashiers
            </NavSubLink>
            <NavSubLink :href="route('manager.cashiers.create')" :active="route().current('manager.cashiers.create')" icon="fa-plus">
              Add Cashier
            </NavSubLink>
          </template>
        </NavGroup>

        <!-- Products -->
        <NavLink
          :href="route('manager.products.index')"
          :active="route().current('manager.products.*')"
          icon="fa-box"
        >
          Products
        </NavLink>

        <!-- Inventory -->
        <NavGroup title="Inventory" icon="fa-warehouse" :active-patterns="['/manager/inventory']">
          <NavSubLink :href="route('manager.inventory.overview')" :active="route().current('manager.inventory.overview')" icon="fa-boxes">Stock Overview</NavSubLink>
          <NavSubLink :href="route('manager.inventory.low-stock')" :active="route().current('manager.inventory.low-stock')" icon="fa-exclamation-triangle">Low Stock Alerts</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('manager.inventory.overview')" :active="route().current('manager.inventory.overview')" icon="fa-boxes">Stock Overview</NavSubLink>
            <NavSubLink :href="route('manager.inventory.low-stock')" :active="route().current('manager.inventory.low-stock')" icon="fa-exclamation-triangle">Low Stock Alerts</NavSubLink>
          </template>
        </NavGroup>

        <!-- Sales -->
        <NavGroup title="Sales" icon="fa-receipt" :active-patterns="['/manager/sales']">
          <NavSubLink :href="route('manager.sales.index')" :active="route().current('manager.sales.index') || route().current('manager.sales.show')" icon="fa-list">Sales History</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('manager.sales.index')" :active="route().current('manager.sales.index') || route().current('manager.sales.show')" icon="fa-list">Sales History</NavSubLink>
          </template>
        </NavGroup>

        <!-- Reports -->
        <NavGroup title="Reports" icon="fa-chart-bar" :active-patterns="['/manager/reports']">
          <NavSubLink :href="route('manager.reports.index')" :active="route().current('manager.reports.index')" icon="fa-tachometer-alt">Overview</NavSubLink>
          <NavSubLink :href="route('manager.reports.sales-summary')" :active="route().current('manager.reports.sales-summary')" icon="fa-chart-line">Sales Summary</NavSubLink>
          <NavSubLink :href="route('manager.reports.cashier-performance')" :active="route().current('manager.reports.cashier-performance')" icon="fa-users">Cashier Performance</NavSubLink>
          <NavSubLink :href="route('manager.reports.product-sales')" :active="route().current('manager.reports.product-sales')" icon="fa-box">Product Sales</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('manager.reports.index')" :active="route().current('manager.reports.index')" icon="fa-tachometer-alt">Overview</NavSubLink>
            <NavSubLink :href="route('manager.reports.sales-summary')" :active="route().current('manager.reports.sales-summary')" icon="fa-chart-line">Sales Summary</NavSubLink>
            <NavSubLink :href="route('manager.reports.cashier-performance')" :active="route().current('manager.reports.cashier-performance')" icon="fa-users">Cashier Performance</NavSubLink>
            <NavSubLink :href="route('manager.reports.product-sales')" :active="route().current('manager.reports.product-sales')" icon="fa-box">Product Sales</NavSubLink>
          </template>
        </NavGroup>
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
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary via-brand-500 to-brand-600 flex items-center justify-center text-white text-sm font-bold shadow-lg ring-2 ring-white dark:ring-slate-900">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div v-if="!miniSidebar" class="text-left min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 capitalize truncate">{{ user.role }}</p>
            </div>
          </div>
          <i v-if="!miniSidebar" :class="`fas fa-chevron-down transition-all duration-300 text-xs text-gray-400 ${userMenuOpen ? 'rotate-180 text-primary' : ''}`"></i>
        </button>

        <!-- User Dropdown -->
        <div v-if="userMenuOpen" :class="[
          'absolute bottom-full mb-3 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-slate-700 z-10 overflow-hidden',
          miniSidebar ? 'left-full ml-3 bottom-0' : 'left-3 right-3'
        ]">
          <Link :href="route('user.profile.edit')" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-primary dark:hover:text-brand-400 transition-all whitespace-nowrap">
            <i class="fas fa-user-circle text-base"></i>
            <span>My Profile</span>
          </Link>
          <div class="h-px bg-gray-100 dark:bg-slate-700"></div>
          <form @submit.prevent="logout" class="w-full">
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all whitespace-nowrap">
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
        <span class="text-lg font-bold text-gray-900 dark:text-white">XASH<span class="text-primary">POS</span></span>
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
import NavGroup from '@/Components/NavGroup.vue';
import NavSubLink from '@/Components/NavSubLink.vue';
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

// Initialize flash messages
useFlashMessages();

// Provide miniSidebar and activeTooltip to child components
provide('miniSidebar', miniSidebar);
provide('activeTooltip', activeTooltip);

// Function to scroll to active menu item
const scrollToActiveItem = () => {
  nextTick(() => {
    if (!sidebarNav.value) return;

    const activeLink = sidebarNav.value.querySelector('a.bg-brand-50, a.bg-primary, button.bg-brand-50');

    if (activeLink) {
      const navContainer = sidebarNav.value;
      const linkRect = activeLink.getBoundingClientRect();
      const navRect = navContainer.getBoundingClientRect();

      const isAboveView = linkRect.top < navRect.top;
      const isBelowView = linkRect.bottom > navRect.bottom;

      if (isAboveView || isBelowView) {
        const scrollTop = activeLink.offsetTop - navContainer.offsetTop - (navContainer.clientHeight / 2) + (activeLink.clientHeight / 2);

        navContainer.scrollTo({
          top: scrollTop,
          behavior: 'smooth'
        });
      }
    }
  });
};

onMounted(() => {
  // Apply dark mode from localStorage
  if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }

  // Load mini sidebar preference from localStorage (separate key for manager)
  const savedMiniState = localStorage.getItem('managerSidebarMini');
  if (savedMiniState !== null) {
    miniSidebar.value = savedMiniState === 'true';
  }

  scrollToActiveItem();
});

// Watch for route changes and scroll to active item
watch(() => page.url, () => {
  scrollToActiveItem();
});

// Save mini sidebar preference to localStorage
watch(miniSidebar, (newValue) => {
  localStorage.setItem('managerSidebarMini', newValue.toString());
});

const toggleMiniSidebar = () => {
  miniSidebar.value = !miniSidebar.value;
  userMenuOpen.value = false;
};

const logout = () => {
  router.post(route('logout'));
};
</script>

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
          <Link :href="route('admin.dashboard')" :class="['flex items-center group', miniSidebar ? '' : 'gap-2']">
            <img
              :src="miniSidebar ? '/logo.png' : '/logo.png'"
              alt="XASH POS"
              :class="[
                'flex-shrink-0 group-hover:scale-105 transition-transform object-contain',
                miniSidebar ? 'w-10 h-10' : 'h-10 w-auto max-w-[140px]'
              ]"
            />
          </Link>
        </div>
        
        <!-- Toggle Button (Desktop Only) - Always visible -->
        <button
          @click="toggleMiniSidebar"
          class="hidden lg:flex items-center justify-center ml-2 w-8 h-8 rounded-lg bg-success-50 dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400 transition-all flex-shrink-0"
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
          :href="route('admin.dashboard')"
          :active="route().current('admin.dashboard')"
          icon="fa-chart-line"
        >
          Dashboard
        </NavLink> 

        <NavLink
          :href="route('admin.dashboard')"
          :active="route().current('admin.pos')"
          icon="fa-dollar-sign"
        >
          POS Terminal
        </NavLink> 

        
        <NavLink :href="route('admin.branches.index')" :active="route().current('admin.branches.*')" icon="fa-store">
          Branches
        </NavLink> 
        <NavGroup
          title="Products"
          icon="fa-box"
          :active-patterns="['/admin/products', '/admin/categories', '/admin/units']"
        >
          <NavSubLink :href="route('admin.products.index')" :active="route().current('admin.products.index')" icon="fa-list">
            All Products
          </NavSubLink>
          <NavSubLink :href="route('admin.products.create')" :active="route().current('admin.products.create')" icon="fa-plus">
            Add Product
          </NavSubLink>
          <NavSubLink :href="route('admin.categories.index')" :active="route().current('admin.categories.*')" icon="fa-tags">
            Categories
          </NavSubLink>
          <NavSubLink :href="route('admin.units.index')" :active="route().current('admin.units.*')" icon="fa-ruler">
            Units of Measure
          </NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.products.index')" :active="route().current('admin.products.index')" icon="fa-list">
              All Products
            </NavSubLink>
            <NavSubLink :href="route('admin.products.create')" :active="route().current('admin.products.create')" icon="fa-plus">
              Add Product
            </NavSubLink>
            <NavSubLink :href="route('admin.categories.index')" :active="route().current('admin.categories.*')" icon="fa-tags">
              Categories
            </NavSubLink>
            <NavSubLink :href="route('admin.units.index')" :active="route().current('admin.units.*')" icon="fa-ruler">
              Units of Measure
            </NavSubLink>
          </template>
        </NavGroup>

        <NavGroup title="Inventory" icon="fa-warehouse" :active-patterns="['/admin/inventory']">
          <NavSubLink :href="route('admin.inventory.overview')" :active="route().current('admin.inventory.overview')" icon="fa-boxes">Stock Overview</NavSubLink>
          <NavSubLink :href="route('admin.inventory.low-stock')" :active="route().current('admin.inventory.low-stock')" icon="fa-exclamation-triangle">Low Stock Alerts</NavSubLink>
          <NavSubLink :href="route('admin.inventory.expiring')" :active="route().current('admin.inventory.expiring')" icon="fa-calendar-times">Expiring Items</NavSubLink>
          <NavSubLink :href="route('admin.inventory.adjustments')" :active="route().current('admin.inventory.adjustments')" icon="fa-clipboard-check">Stock Adjustments</NavSubLink>
          <NavSubLink :href="route('admin.inventory.transfers')" :active="route().current('admin.inventory.transfers')" icon="fa-exchange-alt">Stock Transfers</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.inventory.overview')" :active="route().current('admin.inventory.overview')" icon="fa-boxes">Stock Overview</NavSubLink>
            <NavSubLink :href="route('admin.inventory.low-stock')" :active="route().current('admin.inventory.low-stock')" icon="fa-exclamation-triangle">Low Stock Alerts</NavSubLink>
            <NavSubLink :href="route('admin.inventory.expiring')" :active="route().current('admin.inventory.expiring')" icon="fa-calendar-times">Expiring Items</NavSubLink>
            <NavSubLink :href="route('admin.inventory.adjustments')" :active="route().current('admin.inventory.adjustments')" icon="fa-clipboard-check">Stock Adjustments</NavSubLink>
            <NavSubLink :href="route('admin.inventory.transfers')" :active="route().current('admin.inventory.transfers')" icon="fa-exchange-alt">Stock Transfers</NavSubLink>
          </template>
        </NavGroup>

        <NavGroup title="Suppliers" icon="fa-truck" :active-patterns="['/admin/suppliers', '/admin/purchase-orders', '/admin/goods-received']">
          <NavSubLink :href="route('admin.suppliers.index')" :active="route().current('admin.suppliers.*')" icon="fa-list">All Suppliers</NavSubLink>
          <NavSubLink :href="route('admin.suppliers.create')" :active="route().current('admin.suppliers.create')" icon="fa-plus">Add Supplier</NavSubLink>
          <NavSubLink :href="route('admin.purchase-orders.index')" :active="route().current('admin.purchase-orders.*')" icon="fa-file-invoice">Purchase Orders</NavSubLink>
          <NavSubLink :href="route('admin.goods-received.index')" :active="route().current('admin.goods-received.*')" icon="fa-dolly">Goods Received</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.suppliers.index')" :active="route().current('admin.suppliers.*')" icon="fa-list">All Suppliers</NavSubLink>
            <NavSubLink :href="route('admin.suppliers.create')" :active="route().current('admin.suppliers.create')" icon="fa-plus">Add Supplier</NavSubLink>
            <NavSubLink :href="route('admin.purchase-orders.index')" :active="route().current('admin.purchase-orders.*')" icon="fa-file-invoice">Purchase Orders</NavSubLink>
            <NavSubLink :href="route('admin.goods-received.index')" :active="route().current('admin.goods-received.*')" icon="fa-dolly">Goods Received</NavSubLink>
          </template>
        </NavGroup>  
 
        <!-- Dashboard -->
        


        <NavGroup title="Sales" icon="fa-receipt" :active-patterns="['/admin/sales']">
          <NavSubLink :href="route('admin.sales.index')" :active="route().current('admin.sales.index') || route().current('admin.sales.show')" icon="fa-list">Sales History</NavSubLink>
          <NavSubLink :href="route('admin.sales.daily-summary')" :active="route().current('admin.sales.daily-summary')" icon="fa-chart-line">Daily Summary</NavSubLink>
          <NavSubLink :href="route('admin.sales.refunds')" :active="route().current('admin.sales.refunds')" icon="fa-undo">Refunds</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.sales.index')" :active="route().current('admin.sales.index') || route().current('admin.sales.show')" icon="fa-list">Sales History</NavSubLink>
            <NavSubLink :href="route('admin.sales.daily-summary')" :active="route().current('admin.sales.daily-summary')" icon="fa-chart-line">Daily Summary</NavSubLink>
            <NavSubLink :href="route('admin.sales.refunds')" :active="route().current('admin.sales.refunds')" icon="fa-undo">Refunds</NavSubLink>
          </template>
        </NavGroup>
        <NavGroup
          title="Customers"
          icon="fa-user-friends"
          :active-patterns="['/admin/customers', '/admin/loyalty', '/admin/coupons']"
        >
          <NavSubLink :href="route('admin.customers.index')" :active="route().current('admin.customers.index')" icon="fa-list">
            All Customers
          </NavSubLink>
          <NavSubLink :href="route('admin.customers.create')" :active="route().current('admin.customers.create')" icon="fa-plus">
            Add Customer
          </NavSubLink>
          <NavSubLink :href="route('admin.loyalty.index')" :active="$page.url.startsWith('/admin/loyalty')" icon="fa-gift">Loyalty Program</NavSubLink>
          <NavSubLink :href="route('admin.coupons.index')" :active="$page.url.startsWith('/admin/coupons')" icon="fa-ticket-alt">Coupons & Vouchers</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.customers.index')" :active="route().current('admin.customers.index')" icon="fa-list">
              All Customers
            </NavSubLink>
            <NavSubLink :href="route('admin.customers.create')" :active="route().current('admin.customers.create')" icon="fa-plus">
              Add Customer
            </NavSubLink>
            <NavSubLink :href="route('admin.loyalty.index')" :active="$page.url.startsWith('/admin/loyalty')" icon="fa-gift">Loyalty Program</NavSubLink>
            <NavSubLink :href="route('admin.coupons.index')" :active="$page.url.startsWith('/admin/coupons')" icon="fa-ticket-alt">Coupons & Vouchers</NavSubLink>
          </template>
        </NavGroup> 
        <NavGroup
          title="Staff"
          icon="fa-users"
          :active-patterns="['/admin/users', '/admin/activity-logs']"
        >
          <NavSubLink :href="route('admin.users.index')" :active="route().current('admin.users.index')" icon="fa-list">
            All Users
          </NavSubLink>
          <NavSubLink :href="route('admin.users.create')" :active="route().current('admin.users.create')" icon="fa-plus">
            Add User
          </NavSubLink>
          <NavSubLink :href="route('admin.activity-logs.index')" :active="route().current('admin.activity-logs.*')" icon="fa-history">Activity Logs</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.users.index')" :active="route().current('admin.users.index')" icon="fa-list">
              All Users
            </NavSubLink>
            <NavSubLink :href="route('admin.users.create')" :active="route().current('admin.users.create')" icon="fa-plus">
              Add User
            </NavSubLink>
            <NavSubLink :href="route('admin.activity-logs.index')" :active="route().current('admin.activity-logs.*')" icon="fa-history">Activity Logs</NavSubLink>
          </template>
        </NavGroup> 
        <NavGroup title="Reports" icon="fa-chart-bar" :active-patterns="['/admin/reports']">
          <NavSubLink :href="route('admin.reports.sales')" :active="route().current('admin.reports.sales')" icon="fa-chart-line">Sales Reports</NavSubLink>
          <NavSubLink :href="route('admin.reports.branch-comparison')" :active="route().current('admin.reports.branch-comparison')" icon="fa-chart-pie">Branch Comparison</NavSubLink>
          <NavSubLink :href="route('admin.reports.employee-performance')" :active="route().current('admin.reports.employee-performance')" icon="fa-user-chart">Employee Performance</NavSubLink>
          <NavSubLink :href="route('admin.reports.inventory')" :active="route().current('admin.reports.inventory')" icon="fa-boxes">Inventory Reports</NavSubLink>
          <NavSubLink :href="route('admin.reports.financial')" :active="route().current('admin.reports.financial')" icon="fa-file-invoice-dollar">Financial Reports</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.reports.sales')" :active="route().current('admin.reports.sales')" icon="fa-chart-line">Sales Reports</NavSubLink>
            <NavSubLink :href="route('admin.reports.branch-comparison')" :active="route().current('admin.reports.branch-comparison')" icon="fa-chart-pie">Branch Comparison</NavSubLink>
            <NavSubLink :href="route('admin.reports.employee-performance')" :active="route().current('admin.reports.employee-performance')" icon="fa-user-chart">Employee Performance</NavSubLink>
            <NavSubLink :href="route('admin.reports.inventory')" :active="route().current('admin.reports.inventory')" icon="fa-boxes">Inventory Reports</NavSubLink>
            <NavSubLink :href="route('admin.reports.financial')" :active="route().current('admin.reports.financial')" icon="fa-file-invoice-dollar">Financial Reports</NavSubLink>
          </template>
        </NavGroup> 
        <NavGroup
          title="Settings"
          icon="fa-cog"
          :active-patterns="['/admin/settings']"
        >
          <NavSubLink :href="route('admin.settings.index')" :active="route().current('admin.settings.index')" icon="fa-building">
            Company Profile
          </NavSubLink>
          <NavSubLink :href="route('admin.settings.tax')" :active="route().current('admin.settings.tax')" icon="fa-percent">Tax Rates</NavSubLink>

          <template #mini>
            <NavSubLink :href="route('admin.settings.index')" :active="route().current('admin.settings.index')" icon="fa-building">
              Company Profile
            </NavSubLink>
            <NavSubLink :href="route('admin.settings.tax')" :active="route().current('admin.settings.tax')" icon="fa-percent">Tax Rates</NavSubLink>
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
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 via-brand-500 to-brand-500 flex items-center justify-center text-white text-sm font-bold shadow-lg ring-2 ring-white dark:ring-slate-900">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div v-if="!miniSidebar" class="text-left min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 capitalize truncate">{{ user.role }}</p>
            </div>
          </div>
          <i v-if="!miniSidebar" :class="`fas fa-chevron-down transition-all duration-300 text-xs text-gray-400 ${userMenuOpen ? 'rotate-180 text-brand-500' : ''}`"></i>
        </button>

        <!-- User Dropdown -->
        <div v-if="userMenuOpen" :class="[
          'absolute bottom-full mb-3 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-slate-700 z-10 overflow-hidden',
          miniSidebar ? 'left-full ml-3 bottom-0' : 'left-3 right-3'
        ]">
          <Link :href="route('user.profile.edit')" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400 transition-all whitespace-nowrap">
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
        <img src="/logo.png" alt="XASH POS" class="h-8 w-auto object-contain" />
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
const activeTooltip = ref(null); // Track currently active tooltip/popover
const sidebarNav = ref(null); // Reference to sidebar nav element
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

    // Find active nav link or active nav group
    const activeLink = sidebarNav.value.querySelector('a.bg-brand-50, a.bg-brand-600, button.bg-brand-50');
    
    if (activeLink) {
      // Get the position of the active element
      const navContainer = sidebarNav.value;
      const linkRect = activeLink.getBoundingClientRect();
      const navRect = navContainer.getBoundingClientRect();
      
      // Check if the active link is out of view
      const isAboveView = linkRect.top < navRect.top;
      const isBelowView = linkRect.bottom > navRect.bottom;
      
      if (isAboveView || isBelowView) {
        // Calculate scroll position to center the active item
        const scrollTop = activeLink.offsetTop - navContainer.offsetTop - (navContainer.clientHeight / 2) + (activeLink.clientHeight / 2);
        
        // Smooth scroll to the active item
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

  // Load mini sidebar preference from localStorage
  const savedMiniState = localStorage.getItem('adminSidebarMini');
  if (savedMiniState !== null) {
    miniSidebar.value = savedMiniState === 'true';
  }

  // Scroll to active item on mount
  scrollToActiveItem();
});

// Watch for route changes and scroll to active item
watch(() => page.url, () => {
  scrollToActiveItem();
});

// Save mini sidebar preference to localStorage
watch(miniSidebar, (newValue) => {
  localStorage.setItem('adminSidebarMini', newValue.toString());
});

const toggleMiniSidebar = () => {
  miniSidebar.value = !miniSidebar.value;
  userMenuOpen.value = false; // Close user menu when toggling
};

const logout = () => {
  router.post(route('logout'));
};
</script>

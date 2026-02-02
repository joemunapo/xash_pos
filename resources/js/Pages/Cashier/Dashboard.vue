<template>
  <CashierLayout page-title="Dashboard">
    <div class="space-y-6">
      <!-- Welcome Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ $page.props.auth.user.name }}!</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1" v-if="branch">{{ branch.name }}</p>
          <p class="text-red-600 dark:text-red-400 mt-1" v-else>{{ message }}</p>
        </div>
        <div v-if="branch" class="flex gap-2">
          <Link :href="route('cashier.pos')" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-colors font-medium flex items-center gap-2">
            <i class="fas fa-cash-register"></i>
            <span>Open POS</span>
          </Link>
        </div>
      </div>

      <div v-if="!branch" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex items-center gap-3">
          <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-xl"></i>
          <p class="text-yellow-800 dark:text-yellow-300">{{ message }}</p>
        </div>
      </div>

      <div v-if="branch">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Today's Sales -->
          <div class="bg-brand-500 rounded-lg p-4 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-receipt text-white text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-brand-100 font-medium">Today's Sales</p>
                <p class="text-2xl font-bold text-white">{{ today.total_sales }}</p>
                <p class="text-sm text-brand-100">R{{ formatCurrency(today.total_revenue) }}</p>
              </div>
            </div>
          </div>

          <!-- Average Sale -->
          <div class="bg-cyan-500 rounded-lg p-4 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-line text-white text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-cyan-100 font-medium">Average Sale</p>
                <p class="text-2xl font-bold text-white">R{{ formatCurrency(today.average_sale) }}</p>
              </div>
            </div>
          </div>

          <!-- Week's Sales -->
          <div class="bg-sky-500 rounded-lg p-4 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-week text-white text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-sky-100 font-medium">This Week</p>
                <p class="text-2xl font-bold text-white">{{ week.total_sales }}</p>
                <p class="text-sm text-sky-100">R{{ formatCurrency(week.total_revenue) }}</p>
              </div>
            </div>
          </div>

          <!-- Month's Sales -->
          <div class="bg-blue-500 rounded-lg p-4 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar-alt text-white text-lg"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-blue-100 font-medium">This Month</p>
                <p class="text-2xl font-bold text-white">{{ month.total_sales }}</p>
                <p class="text-sm text-blue-100">R{{ formatCurrency(month.total_revenue) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Sales -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Sales</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">Your latest transactions today</p>
            </div>
            <Link :href="route('cashier.sales.index')" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">
              View all
            </Link>
          </div>
          <div v-if="recentSales.length > 0" class="space-y-3">
            <div v-for="sale in recentSales" :key="sale.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors cursor-pointer" @click="$inertia.visit(route('cashier.sales.show', sale.id))">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                  <i class="fas fa-receipt text-brand-600 dark:text-brand-400"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ sale.receipt_number }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatTime(sale.created_at) }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-bold text-gray-900 dark:text-white">R{{ formatCurrency(sale.total_amount) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ sale.payment_method }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
            <i class="fas fa-receipt text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
            <p>No sales recorded today</p>
            <Link :href="route('cashier.pos')" class="text-brand-600 dark:text-brand-400 hover:underline text-sm mt-2 inline-block">
              Make your first sale
            </Link>
          </div>
        </div>

        <!-- Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="card">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-900/10 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-brand-600 dark:text-brand-400 text-xl"></i>
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Today's Revenue</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">R{{ formatCurrency(today.total_revenue) }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-cyan-100 to-cyan-50 dark:from-cyan-900/30 dark:to-cyan-900/10 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-cyan-600 dark:text-cyan-400 text-xl"></i>
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Transactions</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ today.total_sales }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/10 flex items-center justify-center">
                <i class="fas fa-chart-bar text-blue-600 dark:text-blue-400 text-xl"></i>
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Average Transaction</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">R{{ formatCurrency(today.average_sale) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CashierLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CashierLayout from '@/Layouts/CashierLayout.vue';

defineProps({
  branch: {
    type: Object,
    default: null,
  },
  today: {
    type: Object,
    default: () => ({ total_sales: 0, total_revenue: 0, average_sale: 0 }),
  },
  week: {
    type: Object,
    default: () => ({ total_sales: 0, total_revenue: 0 }),
  },
  month: {
    type: Object,
    default: () => ({ total_sales: 0, total_revenue: 0 }),
  },
  recentSales: {
    type: Array,
    default: () => [],
  },
  message: {
    type: String,
    default: '',
  },
});

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-ZA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

// Format time
const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleTimeString('en-ZA', {
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

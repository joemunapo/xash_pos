<template>
  <AdminLayout page-title="Financial Reports">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Financial Reports</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Revenue, profit margins, and financial analysis</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="label">Start Date</label>
            <input v-model="filterForm.start_date" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">End Date</label>
            <input v-model="filterForm.end_date" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="resetFilters" class="btn-secondary w-full h-[42px] justify-center">
              <i class="fas fa-undo mr-2"></i>Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-xl text-brand-600 dark:text-brand-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Revenue</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.revenue) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-shopping-cart text-xl text-red-600 dark:text-red-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Cost of Goods</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.cost_of_goods) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-line text-xl text-green-600 dark:text-green-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Gross Profit</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.gross_profit) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-percentage text-xl text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Gross Margin</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ summary.gross_margin }}%</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Revenue Trend -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-bar mr-2 text-brand-500"></i>Monthly Revenue Trend
          </h2>
          <div class="space-y-3">
            <div v-for="month in monthlyRevenue" :key="`${month.year}-${month.month}`" class="flex items-center gap-4">
              <span class="w-24 text-sm text-gray-500 dark:text-gray-400">{{ formatMonth(month.year, month.month) }}</span>
              <div class="flex-1 bg-gray-200 dark:bg-slate-700 rounded-full h-4 overflow-hidden">
                <div
                  class="bg-brand-500 h-full rounded-full transition-all duration-500"
                  :style="{ width: getRevenuePercentage(month.revenue) + '%' }"
                ></div>
              </div>
              <span class="w-28 text-right font-semibold text-gray-900 dark:text-white">${{ formatNumber(month.revenue) }}</span>
            </div>
            <div v-if="monthlyRevenue.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-chart-bar text-4xl mb-2 opacity-50"></i>
              <p>No revenue data available</p>
            </div>
          </div>
        </div>

        <!-- Revenue by Payment Method -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-credit-card mr-2 text-brand-500"></i>Revenue by Payment Method
          </h2>
          <div class="space-y-3">
            <div v-for="payment in revenueByPayment" :key="payment.payment_method" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="flex items-center gap-3">
                <i :class="getPaymentIcon(payment.payment_method)" class="text-xl text-gray-600 dark:text-gray-400"></i>
                <p class="font-medium text-gray-900 dark:text-white capitalize">{{ payment.payment_method || 'Cash' }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold text-brand-600 dark:text-brand-400">${{ formatNumber(payment.total) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ getPaymentPercentage(payment.total) }}%</p>
              </div>
            </div>
            <div v-if="revenueByPayment.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-credit-card text-4xl mb-2 opacity-50"></i>
              <p>No payment data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Profit Summary -->
      <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          <i class="fas fa-calculator mr-2 text-brand-500"></i>Profit Summary
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.revenue) }}</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Less: Cost of Goods Sold</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">-${{ formatNumber(summary.cost_of_goods) }}</p>
          </div>
          <div class="p-4 bg-brand-50 dark:bg-brand-900/20 rounded-lg border-2 border-brand-200 dark:border-brand-800">
            <p class="text-sm text-brand-600 dark:text-brand-400 mb-1">Gross Profit</p>
            <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">${{ formatNumber(summary.gross_profit) }}</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  summary: Object,
  monthlyRevenue: Array,
  revenueByPayment: Array,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  start_date: props.filters.start_date,
  end_date: props.filters.end_date,
  branch_id: props.filters.branch_id || '',
});

const maxRevenue = computed(() => Math.max(...props.monthlyRevenue.map(m => m.revenue), 1));
const totalPaymentRevenue = computed(() => props.revenueByPayment.reduce((sum, p) => sum + (p.total || 0), 0));

const applyFilters = () => {
  router.get(route('admin.reports.financial'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filterForm.start_date = '';
  filterForm.end_date = '';
  filterForm.branch_id = '';
  applyFilters();
};

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
};

const formatMonth = (year, month) => {
  return new Date(year, month - 1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const getRevenuePercentage = (revenue) => {
  return Math.round((revenue / maxRevenue.value) * 100);
};

const getPaymentPercentage = (total) => {
  if (totalPaymentRevenue.value === 0) return 0;
  return ((total / totalPaymentRevenue.value) * 100).toFixed(1);
};

const getPaymentIcon = (method) => {
  const icons = {
    cash: 'fas fa-money-bill-wave',
    card: 'fas fa-credit-card',
    ecocash: 'fas fa-mobile-alt',
    mukuru: 'fas fa-exchange-alt',
  };
  return icons[method] || 'fas fa-money-bill-wave';
};
</script>

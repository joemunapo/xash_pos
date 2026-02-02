<template>
  <AdminLayout page-title="Sales Reports">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Reports</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Analyze your sales performance and trends</p>
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
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Sales</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.total_sales) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-receipt text-xl text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Transactions</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ formatNumber(summary.total_transactions) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-line text-xl text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Transaction</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.average_transaction) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-tags text-xl text-red-600 dark:text-red-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Discounts</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(summary.total_discounts) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-trophy mr-2 text-brand-500"></i>Top Selling Products
          </h2>
          <div class="space-y-3">
            <div v-for="(product, index) in topProducts" :key="product.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-sm font-bold text-brand-600 dark:text-brand-400">
                  {{ index + 1 }}
                </span>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatNumber(product.total_quantity) }} units sold</p>
                </div>
              </div>
              <p class="font-semibold text-brand-600 dark:text-brand-400">${{ formatNumber(product.total_revenue) }}</p>
            </div>
            <div v-if="topProducts.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-box-open text-4xl mb-2 opacity-50"></i>
              <p>No sales data for this period</p>
            </div>
          </div>
        </div>

        <!-- Sales by Payment Method -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-credit-card mr-2 text-brand-500"></i>Sales by Payment Method
          </h2>
          <div class="space-y-3">
            <div v-for="payment in salesByPayment" :key="payment.payment_method" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="flex items-center gap-3">
                <i :class="getPaymentIcon(payment.payment_method)" class="text-xl text-gray-600 dark:text-gray-400"></i>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white capitalize">{{ payment.payment_method || 'Cash' }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ payment.count }} transactions</p>
                </div>
              </div>
              <p class="font-semibold text-brand-600 dark:text-brand-400">${{ formatNumber(payment.total) }}</p>
            </div>
            <div v-if="salesByPayment.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-credit-card text-4xl mb-2 opacity-50"></i>
              <p>No payment data for this period</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Sales Table -->
      <div class="card overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            <i class="fas fa-calendar-alt mr-2 text-brand-500"></i>Daily Sales Breakdown
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transactions</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Sales</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Net Sales</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="day in dailySales" :key="day.date" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ formatDate(day.date) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ day.transactions }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">${{ formatNumber(day.total_sales) }}</td>
                <td class="px-6 py-4 text-sm font-medium text-brand-600 dark:text-brand-400 text-right">${{ formatNumber(day.net_sales) }}</td>
              </tr>
              <tr v-if="dailySales.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <i class="fas fa-chart-bar text-4xl mb-2 opacity-50"></i>
                  <p>No sales data for this period</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  dailySales: Array,
  summary: Object,
  topProducts: Array,
  salesByPayment: Array,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  start_date: props.filters.start_date,
  end_date: props.filters.end_date,
  branch_id: props.filters.branch_id || '',
});

const applyFilters = () => {
  router.get(route('admin.reports.sales'), filterForm, {
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

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
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

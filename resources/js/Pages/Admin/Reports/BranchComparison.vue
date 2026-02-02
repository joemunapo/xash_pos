<template>
  <AdminLayout page-title="Branch Comparison">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Branch Comparison</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Compare performance across all branches</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Start Date</label>
            <input v-model="filterForm.start_date" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">End Date</label>
            <input v-model="filterForm.end_date" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div class="flex items-end">
            <button @click="resetFilters" class="btn-secondary w-full h-[42px] justify-center">
              <i class="fas fa-undo mr-2"></i>Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Branch Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="branch in branches" :key="branch.id" class="card p-6">
          <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200 dark:border-slate-700">
            <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-store text-xl text-brand-600 dark:text-brand-400"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ branch.name }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ branch.address || 'No address' }}</p>
            </div>
          </div>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Total Sales</span>
              <span class="font-semibold text-gray-900 dark:text-white">${{ formatNumber(branch.total_sales) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Transactions</span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ branch.total_transactions }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Avg. Sale</span>
              <span class="font-semibold text-gray-900 dark:text-white">${{ formatNumber(branch.average_sale) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-400">Items Sold</span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ formatNumber(branch.total_items) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Comparison Table -->
      <div class="card overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            <i class="fas fa-chart-bar mr-2 text-brand-500"></i>Detailed Comparison
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Branch</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sales</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transactions</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg. Sale</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Items</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Share</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="branch in branches" :key="branch.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ branch.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">${{ formatNumber(branch.total_sales) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ branch.total_transactions }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">${{ formatNumber(branch.average_sale) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ formatNumber(branch.total_items) }}</td>
                <td class="px-6 py-4 text-sm font-medium text-brand-600 dark:text-brand-400 text-right">{{ getSharePercentage(branch) }}%</td>
              </tr>
            </tbody>
          </table>
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
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  start_date: props.filters.start_date,
  end_date: props.filters.end_date,
});

const totalSales = computed(() => props.branches.reduce((sum, b) => sum + (b.total_sales || 0), 0));

const applyFilters = () => {
  router.get(route('admin.reports.branch-comparison'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filterForm.start_date = '';
  filterForm.end_date = '';
  applyFilters();
};

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
};

const getSharePercentage = (branch) => {
  if (totalSales.value === 0) return 0;
  return ((branch.total_sales / totalSales.value) * 100).toFixed(1);
};
</script>

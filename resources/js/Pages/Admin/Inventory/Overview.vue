<template>
  <AdminLayout page-title="Stock Overview">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory Overview</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor stock levels across all branches</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card p-6 hover:shadow-xl transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Products</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_products }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-box text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6 hover:shadow-xl transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Low Stock Items</p>
              <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ stats.low_stock_items }}</p>
            </div>
            <div class="w-12 h12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-exclamation-triangle text-orange-600 dark:text-orange-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6 hover:shadow-xl transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Out of Stock</p>
              <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ stats.out_of_stock }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6 hover:shadow-xl transition-shadow">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Value</p>
              <p class="text-3xl font-bold text-brand-600 dark:text-brand-400">${{ formatMoney(stats.total_value) }}</p>
            </div>
            <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-dollar-sign text-brand-600 dark:text-brand-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="label">Search Products</label>
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input
                v-model="filterForm.search"
                type="text"
                class="input-field pl-10"
                placeholder="Search by product name or SKU..."
                @input="debouncedFilter"
              />
            </div>
          </div>
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Stock Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Branch</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Quantity</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Reorder Level</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="stocks.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-boxes text-6xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium mb-1">No stock records found</p>
                    <p class="text-sm">Try adjusting your filters</p>
                  </div>
                </td>
              </tr>
              <tr v-for="stock in stocks.data" :key="stock.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ stock.product?.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ stock.product?.sku }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded">
                    {{ stock.branch?.name }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ stock.quantity }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ stock.product?.reorder_level || 0 }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="getStockStatusClass(stock)">
                    {{ getStockStatus(stock) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="stocks.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ stocks.from }}</span> to <span class="font-medium">{{ stocks.to }}</span> of
              <span class="font-medium">{{ stocks.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in stocks.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 text-sm rounded-lg border transition-colors',
                    link.active
                      ? 'bg-brand-600 text-white border-brand-600'
                      : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-1 text-sm rounded-lg border opacity-50 cursor-not-allowed',
                    'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  stocks: Object,
  stats: Object,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  search: props.filters?.search || '',
  branch_id: props.filters?.branch_id || '',
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.inventory.overview'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatMoney = (value) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const getStockStatus = (stock) => {
  if (stock.quantity <= 0) return 'Out of Stock';
  if (stock.quantity <= (stock.product?.reorder_level || 0)) return 'Low Stock';
  return 'In Stock';
};

const getStockStatusClass = (stock) => {
  const baseClasses = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
  
  if (stock.quantity <= 0) {
    return `${baseClasses} bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400`;
  }
  if (stock.quantity <= (stock.product?.reorder_level || 0)) {
    return `${baseClasses} bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400`;
  }
  return `${baseClasses} bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400`;
};
</script>

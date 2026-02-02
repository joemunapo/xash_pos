<template>
  <AdminLayout page-title="Inventory Reports">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory Reports</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Stock levels, valuations, and movement analysis</p>
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
              <p class="text-sm text-gray-500 dark:text-gray-400">Total Stock Value</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">${{ formatNumber(totalStockValue) }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-tags text-xl text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Categories</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stockByCategory.length }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-exclamation-triangle text-xl text-red-600 dark:text-red-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Low Stock Items</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ lowStockProducts.length }}</p>
            </div>
          </div>
        </div>
        <div class="card p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-exchange-alt text-xl text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-400">Movements (30 days)</p>
              <p class="text-xl font-bold text-gray-900 dark:text-white">{{ getTotalMovements() }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock by Category -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-chart-pie mr-2 text-brand-500"></i>Stock Value by Category
          </h2>
          <div class="space-y-3">
            <div v-for="category in stockByCategory" :key="category.category" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ category.category || 'Uncategorized' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatNumber(category.total_quantity) }} units</p>
              </div>
              <p class="font-semibold text-brand-600 dark:text-brand-400">${{ formatNumber(category.stock_value) }}</p>
            </div>
            <div v-if="stockByCategory.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-boxes text-4xl mb-2 opacity-50"></i>
              <p>No stock data available</p>
            </div>
          </div>
        </div>

        <!-- Stock Movements -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <i class="fas fa-history mr-2 text-brand-500"></i>Stock Movements (Last 30 Days)
          </h2>
          <div class="space-y-3">
            <div v-for="movement in stockMovements" :key="movement.type" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="flex items-center gap-3">
                <i :class="getMovementIcon(movement.type)" class="text-xl"></i>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white capitalize">{{ movement.type }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ movement.count }} adjustments</p>
                </div>
              </div>
              <p class="font-semibold text-gray-900 dark:text-white">{{ formatNumber(movement.total_quantity) }} units</p>
            </div>
            <div v-if="stockMovements.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-exchange-alt text-4xl mb-2 opacity-50"></i>
              <p>No movements in last 30 days</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Stock Products -->
      <div class="card overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>Low Stock Alert
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Current Stock</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reorder Level</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="product in lowStockProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ product.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ product.category?.name || 'N/A' }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ getTotalStock(product) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ getReorderLevel(product) }}</td>
                <td class="px-6 py-4 text-right">
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                    Low Stock
                  </span>
                </td>
              </tr>
              <tr v-if="lowStockProducts.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <i class="fas fa-check-circle text-4xl mb-2 text-brand-500 opacity-50"></i>
                  <p>All products are well stocked</p>
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
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  stockByCategory: Array,
  lowStockProducts: Array,
  stockMovements: Array,
  totalStockValue: Number,
  branches: Array,
  filters: Object,
});

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
};

const getTotalMovements = () => {
  return props.stockMovements.reduce((sum, m) => sum + (m.count || 0), 0);
};

const getMovementIcon = (type) => {
  const icons = {
    addition: 'fas fa-plus-circle text-brand-500',
    subtraction: 'fas fa-minus-circle text-red-500',
    adjustment: 'fas fa-sync text-blue-500',
    transfer: 'fas fa-exchange-alt text-purple-500',
  };
  return icons[type] || 'fas fa-circle text-gray-500';
};

const getTotalStock = (product) => {
  return product.branch_stock?.reduce((sum, s) => sum + (s.quantity || 0), 0) || 0;
};

const getReorderLevel = (product) => {
  return product.branch_stock?.[0]?.reorder_level || 10;
};
</script>

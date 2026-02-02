<template>
  <AdminLayout page-title="Low Stock Alerts">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Low Stock Alerts</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Products below reorder level</p>
      </div>

      <!-- Filter -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-3">
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label invisible">Action</label>
            <button @click="clearFilters" class="btn-secondary w-full h-[42px] flex items-center justify-center">
              <i class="fas fa-times mr-2"></i>Clear Filter
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Branch</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Current</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Reorder Level</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Shortage</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="lowStockItems.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <i class="fas fa-check-circle text-6xl text-brand-500 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">All Stock Levels Good!</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">No products are below reorder level</p>
                </td>
              </tr>
              <tr v-for="item in lowStockItems.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ item.product?.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ item.product?.sku }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-700 rounded">
                    {{ item.branch?.name }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ item.quantity }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ item.product?.reorder_level || 0 }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                    -{{ Math.max(0, (item.product?.reorder_level || 0) - item.quantity) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="lowStockItems.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ lowStockItems.from }}</span> to <span class="font-medium">{{ lowStockItems.to }}</span> of
              <span class="font-medium">{{ lowStockItems.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in lowStockItems.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 text-sm rounded-lg border', link.active ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
                <span v-else :class="['px-3 py-1 text-sm rounded-lg border opacity-50 cursor-not-allowed bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
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
  lowStockItems: Object,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  branch_id: props.filters?.branch_id || '',
});

const applyFilters = () => {
  router.get(route('admin.inventory.low-stock'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.branch_id = '';
  applyFilters();
};
</script>

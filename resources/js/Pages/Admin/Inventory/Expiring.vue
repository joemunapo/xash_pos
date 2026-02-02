<template>
  <AdminLayout page-title="Expiring Items">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Expiring Items</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Batches approaching expiry date</p>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Days Ahead</label>
            <select v-model="filterForm.days" class="input-field" @change="applyFilters">
              <option value="7">7 Days</option>
              <option value="14">14 Days</option>
              <option value="30">30 Days</option>
              <option value="60">60 Days</option>
              <option value="90">90 Days</option>
            </select>
          </div>
          <div>
            <label class="label invisible">Action</label>
            <button @click="clearFilters" class="btn-secondary w-full h-[42px] flex items-center justify-center">
              <i class="fas fa-times mr-2"></i>Clear Filters
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Batch #</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Quantity</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Expiry Date</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="expiringBatches.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <i class="fas fa-calendar-check text-6xl text-brand-500 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No Expiring Items</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">No batches are expiring in the selected period</p>
                </td>
              </tr>
              <tr v-for="batch in expiringBatches.data" :key="batch.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ batch.product?.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ batch.product?.sku }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-700 rounded">
                    {{ batch.branch?.name }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="font-mono text-sm text-gray-900 dark:text-white">{{ batch.batch_number }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ batch.quantity_remaining }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="text-sm text-gray-900 dark:text-white">{{ formatDate(batch.expiry_date) }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="getExpiryStatusClass(batch.expiry_date)">
                    {{ getExpiryStatus(batch.expiry_date) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="expiringBatches.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ expiringBatches.from }}</span> to <span class="font-medium">{{ expiringBatches.to }}</span> of
              <span class="font-medium">{{ expiringBatches.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in expiringBatches.links" :key="link.label">
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
  expiringBatches: Object,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  branch_id: props.filters?.branch_id || '',
  days: props.filters?.days || '30',
});

const applyFilters = () => {
  router.get(route('admin.inventory.expiring'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.branch_id = '';
  filterForm.days = '30';
  applyFilters();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getExpiryStatus = (expiryDate) => {
  const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
  if (days < 0) return 'Expired';
  if (days <= 7) return `${days}d left`;
  if (days <= 30) return `${days}d left`;
  return `${days}d left`;
};

const getExpiryStatusClass = (expiryDate) => {
  const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
  const base = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
  
  if (days < 0) return `${base} bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400`;
  if (days <= 7) return `${base} bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400`;
  if (days <= 30) return `${base} bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400`;
  return `${base} bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400`;
};
</script>

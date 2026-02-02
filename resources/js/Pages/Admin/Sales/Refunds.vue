<template>
  <AdminLayout page-title="Refunds">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Refunds</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">View all refunded transactions</p>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">From Date</label>
            <input v-model="filterForm.date_from" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">To Date</label>
            <input v-model="filterForm.date_to" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label invisible">Action</label>
            <button @click="clearFilters" class="btn-secondary w-full h-[42px] flex items-center justify-center">
              <i class="fas fa-times mr-2"></i>Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Refunds Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Receipt #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Original Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Branch</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Refunded By</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="refunds.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <i class="fas fa-undo text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No refunds found</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Refunded transactions will appear here</p>
                </td>
              </tr>
              <tr v-for="refund in refunds.data" :key="refund.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">{{ refund.receipt_number }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                  {{ formatDateTime(refund.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <div v-if="refund.customer">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ refund.customer.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ refund.customer.phone }}</p>
                  </div>
                  <span v-else class="text-sm text-gray-500 dark:text-gray-400">Walk-in</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-700 rounded">{{ refund.branch?.name }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-red-600 dark:text-red-400">-${{ parseFloat(refund.total_amount).toFixed(2) }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  {{ refund.user?.name }}
                </td>
                <td class="px-6 py-4 text-center">
                  <Link :href="route('admin.sales.show', refund.id)" class="btn-primary text-sm py-1.5 px-3">
                    <i class="fas fa-eye mr-1.5"></i>View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="refunds.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ refunds.from }}</span> to <span class="font-medium">{{ refunds.to }}</span> of
              <span class="font-medium">{{ refunds.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in refunds.links" :key="link.label">
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
  refunds: Object,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  branch_id: props.filters?.branch_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
  router.get(route('admin.sales.refunds'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.branch_id = '';
  filterForm.date_from = '';
  filterForm.date_to = '';
  applyFilters();
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

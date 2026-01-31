<template>
  <AdminLayout page-title="Sales History">
    <div class="space-y-6">
      <!-- Header & Stats -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales History</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">View and manage all sales transactions</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Total Sales</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_sales.toLocaleString() }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-receipt text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</p>
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ stats.total_revenue.toFixed(2) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Today's Sales</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.today_sales.toLocaleString() }}</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
              <i class="fas fa-calendar-day text-xl text-gray-600 dark:text-gray-400"></i>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Today's Revenue</p>
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ stats.today_revenue.toFixed(2) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-line text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <div class="md:col-span-2">
            <label class="label">Search</label>
            <input v-model="filterForm.search" type="text" class="input-field" placeholder="Receipt #, customer..." @input="debouncedFilter" />
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="filterForm.status" class="input-field" @change="applyFilters">
              <option value="">All</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
              <option value="refunded">Refunded</option>
            </select>
          </div>
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
        </div>
      </div>

      <!-- Sales Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Receipt #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Branch</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Payment</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:border-slate-700">
              <tr v-if="sales.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <i class="fas fa-receipt text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No sales found</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Sales will appear here once transactions are completed</p>
                </td>
              </tr>
              <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">{{ sale.receipt_number }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white whitespace-nowrap">
                  {{ formatDateTime(sale.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <div v-if="sale.customer">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ sale.customer.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ sale.customer.phone }}</p>
                  </div>
                  <span v-else class="text-sm text-gray-500 dark:text-gray-400">Walk-in</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-700 rounded">{{ sale.branch?.name }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-gray-900 dark:text-white">${{ parseFloat(sale.total_amount).toFixed(2) }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium capitalize bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded">
                    {{ sale.payment_method }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="getStatusClass(sale.status)">
                    {{ sale.status.charAt(0).toUpperCase() + sale.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <Link :href="route('admin.sales.show', sale.id)" class="btn-primary text-sm py-1.5 px-3">
                    <i class="fas fa-eye mr-1.5"></i>View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="sales.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ sales.from }}</span> to <span class="font-medium">{{ sales.to }}</span> of
              <span class="font-medium">{{ sales.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in sales.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 text-sm rounded-lg border', link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
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
  sales: Object,
  branches: Array,
  stats: Object,
  filters: Object,
});

const filterForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  branch_id: props.filters?.branch_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
  payment_method: props.filters?.payment_method || '',
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.sales.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
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

const getStatusClass = (status) => {
  const classes = {
    completed: 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    pending: 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    cancelled: 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    refunded: 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  };
  return classes[status] || classes.pending;
};
</script>

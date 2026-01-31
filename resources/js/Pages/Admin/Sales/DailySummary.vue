<template>
  <AdminLayout page-title="Daily Summary">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daily Sales Summary</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive daily sales analytics</p>
        </div>
        <div class="flex gap-3">
          <input v-model="selectedDate" type="date" class="input-field" @change="loadData" />
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Total Sales</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ summary.total_sales.toLocaleString() }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-receipt text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Revenue</p>
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">${{ summary.total_revenue.toFixed(2) }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-xl text-emerald-600 dark:text-emerald-400"></i>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Total Tax</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ summary.total_tax.toFixed(2) }}</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
              <i class="fas fa-percentage text-xl text-gray-600 dark:text-gray-400"></i>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Discounts</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ summary.total_discount.toFixed(2) }}</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
              <i class="fas fa-tags text-xl text-gray-600 dark:text-gray-400"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales by Branch & Payment Method -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- By Branch -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-building text-emerald-500"></i>
            Sales by Branch
          </h2>
          <div class="space-y-3">
            <div v-for="item in salesByBranch" :key="item.branch_id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ item.branch?.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.count }} sales</p>
              </div>
              <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">${{ parseFloat(item.revenue).toFixed(2) }}</span>
            </div>
            <div v-if="salesByBranch.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              No sales data
            </div>
          </div>
        </div>

        <!-- By Payment Method -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-credit-card text-emerald-500"></i>
            Sales by Payment Method
          </h2>
          <div class="space-y-3">
            <div v-for="item in salesByPayment" :key="item.payment_method" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white capitalize">{{ item.payment_method }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.count }} transactions</p>
              </div>
              <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">${{ parseFloat(item.revenue).toFixed(2) }}</span>
            </div>
            <div v-if="salesByPayment.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              No payment data
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fas fa-star text-emerald-500"></i>
          Top Selling Products
        </h2>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-16">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Quantity</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="(product, index) in topProducts" :key="index" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ index + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ product.product_name }}</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ product.total_quantity }}</td>
                <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">${{ parseFloat(product.total_revenue).toFixed(2) }}</td>
              </tr>
              <tr v-if="topProducts.length === 0">
                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No product data</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  summary: Object,
  salesByBranch: Array,
  salesByPayment: Array,
  hourlySales: Array,
  topProducts: Array,
  branches: Array,
  date: String,
});

const selectedDate = ref(props.date);

const loadData = () => {
  router.get(route('admin.sales.daily-summary'), { date: selectedDate.value }, {
    preserveState: true,
  });
};
</script>

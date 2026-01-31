<template>
  <AdminLayout page-title="Sale Details">
    <div class="max-w-6xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ sale.receipt_number }}</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Sale Details</p>
        </div>
        <div class="flex gap-3">
          <button class="btn-secondary">
            <i class="fas fa-print mr-2"></i>Print Receipt
          </button>
          <Link :href="route('admin.sales.index')" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back
          </Link>
        </div>
      </div>

      <!-- Status & Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Status</div>
          <span :class="getStatusClass(sale.status)">
            {{ sale.status.charAt(0).toUpperCase() + sale.status.slice(1) }}
          </span>
        </div>
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Sale Date</div>
          <div class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ formatDateTime(sale.created_at) }}
          </div>
        </div>
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Payment Method</div>
          <div class="text-lg font-semibold text-gray-900 dark:text-white capitalize">
            {{ sale.payment_method }}
          </div>
        </div>
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Amount</div>
          <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
            ${{ parseFloat(sale.total_amount).toFixed(2) }}
          </div>
        </div>
      </div>

      <!-- Customer & Branch Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-user text-emerald-500"></i>
            Customer Information
          </h2>
          <div v-if="sale.customer" class="space-y-3">
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Name</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ sale.customer.name }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Phone</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ sale.customer.phone }}</div>
            </div>
          </div>
          <p v-else class="text-gray-500 dark:text-gray-400 italic">Walk-in Customer</p>
        </div>

        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-building text-emerald-500"></i>
            Branch & Cashier
          </h2>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Branch</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ sale.branch?.name }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Cashier</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ sale.user?.name }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sale Items -->
      <div class="card overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-shopping-cart text-emerald-500"></i>
            Sale Items
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Quantity</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Unit Price</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Discount</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="(item, index) in sale.items" :key="item.id">
                <td class="px-6 py-4 text-gray-900 dark:text-white">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ item.product_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ item.sku }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white">{{ item.quantity }} {{ item.unit }}</td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white">${{ parseFloat(item.unit_price).toFixed(2) }}</td>
                <td class="px-6 py-4 text-right text-red-600 dark:text-red-400">-${{ parseFloat(item.discount_amount).toFixed(2) }}</td>
                <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">${{ parseFloat(item.line_total).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Sale Totals -->
        <div class="p-6 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700">
          <div class="flex justify-end">
            <div class="w-full md:w-1/3 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(sale.subtotal).toFixed(2) }}</span>
              </div>
              <div v-if="sale.tax_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Tax:</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(sale.tax_amount).toFixed(2) }}</span>
              </div>
              <div v-if="sale.discount_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                <span class="font-medium text-red-600 dark:text-red-400">-${{ parseFloat(sale.discount_amount).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-300 dark:border-slate-600">
                <span class="text-gray-900 dark:text-white">Total:</span>
                <span class="text-emerald-600 dark:text-emerald-400">${{ parseFloat(sale.total_amount).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-sm pt-2">
                <span class="text-gray-600 dark:text-gray-400">Amount Paid:</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(sale.amount_paid).toFixed(2) }}</span>
              </div>
              <div v-if="sale.change_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Change:</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(sale.change_amount).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="sale.notes" class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <i class="fas fa-sticky-note text-gray-500"></i>
          Notes
        </h2>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ sale.notes }}</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  sale: Object,
});

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
    completed: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    pending: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    cancelled: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    refunded: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  };
  return classes[status] || classes.pending;
};
</script>

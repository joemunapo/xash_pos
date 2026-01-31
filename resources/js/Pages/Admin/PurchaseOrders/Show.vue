<template>
  <AdminLayout page-title="Purchase Order Details">
    <div class="max-w-6xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ purchaseOrder.po_number }}</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Purchase Order Details</p>
        </div>
        <div class="flex gap-3">
          <button class="btn-secondary">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
          </button>
          <Link :href="route('admin.purchase-orders.index')" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back
          </Link>
        </div>
      </div>

      <!-- Status & Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Status</div>
          <div>
            <span :class="getStatusClass(purchaseOrder.status)">
              <i :class="getStatusIcon(purchaseOrder.status) + ' mr-2'"></i>
              {{ getStatusLabel(purchaseOrder.status) }}
            </span>
          </div>
        </div>
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Order Date</div>
          <div class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ formatDate(purchaseOrder.order_date) }}
          </div>
        </div>
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Expected Delivery</div>
          <div class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ purchaseOrder.expected_delivery ? formatDate(purchaseOrder.expected_delivery) : 'N/A' }}
          </div>
        </div>
        <div class="card p-6">
          <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Amount</div>
          <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
            ${{ parseFloat(purchaseOrder.total_amount).toFixed(2) }}
          </div>
        </div>
      </div>

      <!-- Supplier & Branch Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-truck text-blue-500"></i>
            Supplier Information
          </h2>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Supplier Name</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ purchaseOrder.supplier?.name }}</div>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-building text-purple-500"></i>
            Branch Information
          </h2>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Receiving Branch</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ purchaseOrder.branch?.name }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 dark:text-gray-400">Created By</div>
              <div class="text-gray-900 dark:text-white font-medium">{{ purchaseOrder.user?.name }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="card overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-boxes text-emerald-500"></i>
            Order Items
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
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="!purchaseOrder.items || purchaseOrder.items.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                  <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                  <p>No items in this purchase order</p>
                </td>
              </tr>
              <tr v-for="(item, index) in purchaseOrder.items" :key="item.id">
                <td class="px-6 py-4 text-gray-900 dark:text-white">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ item.product?.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ item.product?.sku }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-right text-gray-900 dark:text-white">${{ parseFloat(item.unit_price).toFixed(2) }}</td>
                <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">${{ parseFloat(item.total).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Order Totals -->
        <div class="p-6 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700">
          <div class="flex justify-end">
            <div class="w-full md:w-1/3 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(purchaseOrder.subtotal).toFixed(2) }}</span>
              </div>
              <div v-if="purchaseOrder.tax_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Tax:</span>
                <span class="font-medium text-gray-900 dark:text-white">${{ parseFloat(purchaseOrder.tax_amount).toFixed(2) }}</span>
              </div>
              <div v-if="purchaseOrder.discount_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                <span class="font-medium text-red-600 dark:text-red-400">-${{ parseFloat(purchaseOrder.discount_amount).toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-300 dark:border-slate-600">
                <span class="text-gray-900 dark:text-white">Total:</span>
                <span class="text-emerald-600 dark:text-emerald-400">${{ parseFloat(purchaseOrder.total_amount).toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="purchaseOrder.notes" class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <i class="fas fa-sticky-note text-orange-500"></i>
          Notes
        </h2>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ purchaseOrder.notes }}</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  purchaseOrder: Object,
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

const getStatusClass = (status) => {
  const classes = {
    draft: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    pending: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    approved: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    ordered: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    received: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    cancelled: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  };
  return classes[status] || classes.draft;
};

const getStatusIcon = (status) => {
  const icons = {
    draft: 'fas fa-file-alt',
    pending: 'fas fa-clock',
    approved: 'fas fa-check',
    ordered: 'fas fa-shipping-fast',
    received: 'fas fa-check-double',
    cancelled: 'fas fa-times-circle',
  };
  return icons[status] || 'fas fa-file';
};

const getStatusLabel = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};
</script>

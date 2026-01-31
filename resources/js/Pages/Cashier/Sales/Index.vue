<template>
  <CashierLayout page-title="My Sales">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Sales</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">View your sales history</p>
        </div>
      </div>

      <!-- Sales List -->
      <div class="card">
        <div v-if="sales.data && sales.data.length > 0" class="space-y-3">
          <div v-for="sale in sales.data" :key="sale.id" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors cursor-pointer" @click="$inertia.visit(route('cashier.sales.show', sale.id))">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                <i class="fas fa-receipt text-teal-600 dark:text-teal-400 text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ sale.receipt_number }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDateTime(sale.created_at) }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold text-gray-900 dark:text-white">R{{ formatCurrency(sale.total_amount) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ sale.payment_method }}</p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-16 text-gray-500 dark:text-gray-400">
          <i class="fas fa-receipt text-5xl mb-4 text-gray-300 dark:text-gray-600"></i>
          <p class="text-lg">No sales found</p>
          <Link :href="route('cashier.pos')" class="text-teal-600 dark:text-teal-400 hover:underline text-sm mt-2 inline-block">
            Make your first sale
          </Link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="sales.data && sales.data.length > 0" class="flex items-center justify-between">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Showing {{ sales.from }} to {{ sales.to }} of {{ sales.total }} results
        </p>
        <div class="flex gap-2">
          <Link v-if="sales.prev_page_url" :href="sales.prev_page_url" class="px-3 py-2 bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">
            Previous
          </Link>
          <Link v-if="sales.next_page_url" :href="sales.next_page_url" class="px-3 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
            Next
          </Link>
        </div>
      </div>
    </div>
  </CashierLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CashierLayout from '@/Layouts/CashierLayout.vue';

defineProps({
  sales: {
    type: Object,
    required: true,
  },
});

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-ZA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

// Format date and time
const formatDateTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-ZA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

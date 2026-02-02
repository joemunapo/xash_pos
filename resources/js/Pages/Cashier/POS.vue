<template>
  <CashierLayout page-title="POS Terminal">
    <div class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/30 dark:to-brand-900/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-cash-register text-brand-600 dark:text-brand-400 text-4xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">POS Terminal</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ message }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-500">This feature will be implemented in Phase 5</p>
        <Link :href="route('cashier.dashboard')" class="inline-block mt-6 px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-colors">
          Back to Dashboard
        </Link>
      </div>
    </div>
  </CashierLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CashierLayout from '@/Layouts/CashierLayout.vue';

defineProps({
  message: {
    type: String,
    default: 'Coming Soon',
  },
});
</script>

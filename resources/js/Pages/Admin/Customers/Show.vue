<template>
  <AdminLayout page-title="Customer Details">
    <div class="max-w-3xl space-y-6">
      <div class="mb-6">
        <Link :href="route('admin.customers.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Customers
        </Link>
      </div>

      <div class="card p-6">
        <div class="flex items-start gap-4">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 flex items-center justify-center text-white text-2xl font-bold">
            {{ customer.first_name.charAt(0) }}
          </div>
          <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ customer.first_name }} {{ customer.last_name }}</h1>
            <p class="text-gray-500 dark:text-gray-400">Member since {{ new Date(customer.member_since).toLocaleDateString() }}</p>
          </div>
          <Link :href="route('admin.customers.edit', customer.id)" class="btn-secondary">
            <i class="fas fa-edit mr-2"></i> Edit
          </Link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
          <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ Number(customer.loyalty_points).toLocaleString() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Points</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white capitalize">{{ customer.loyalty_tier }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tier</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ customer.phone || '-' }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
          </div>
          <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ customer.city || '-' }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">City</p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Loyalty Transactions</h2>
        <div class="space-y-3" v-if="customer.loyalty_transactions?.length > 0">
          <div v-for="tx in customer.loyalty_transactions" :key="tx.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <div>
              <p class="font-medium text-gray-900 dark:text-white capitalize">{{ tx.type }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ tx.description || tx.reference || '-' }}</p>
            </div>
            <span :class="['font-bold', tx.points > 0 ? 'text-brand-600' : 'text-red-600']">
              {{ tx.points > 0 ? '+' : '' }}{{ tx.points }}
            </span>
          </div>
        </div>
        <p v-else class="text-gray-500 dark:text-gray-400 text-center py-4">No transactions yet</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({ customer: Object });
</script>




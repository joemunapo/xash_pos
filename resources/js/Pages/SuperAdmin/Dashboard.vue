<template>
  <SuperAdminLayout page-title="Dashboard">
    <div class="space-y-6">
      <!-- Welcome Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Platform Overview</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor all tenants and subscriptions across XASH POS.</p>
        </div>
        <div class="flex gap-2">
          <Link :href="route('superadmin.tenants.create')" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors shadow-sm">
            <i class="fas fa-plus mr-2"></i> New Tenant
          </Link>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-brand-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-building text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-brand-100">Total Tenants</p>
              <p class="text-lg font-bold text-white">{{ overview.total_tenants }}</p>
            </div>
          </div>
        </div>

        <div class="bg-success-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-check-circle text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-success-100">Active Tenants</p>
              <p class="text-lg font-bold text-white">{{ overview.active_tenants }}</p>
            </div>
          </div>
        </div>

        <div class="bg-warn-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-hourglass-half text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-warn-100">On Trial</p>
              <p class="text-lg font-bold text-white">{{ overview.trial_tenants }}</p>
            </div>
          </div>
        </div>

        <div class="bg-danger-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-ban text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-danger-100">Suspended</p>
              <p class="text-lg font-bold text-white">{{ overview.suspended_tenants }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Secondary Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-credit-card text-brand-600 dark:text-brand-400"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Active Subscriptions</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ overview.active_subscriptions }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-warn-100 dark:bg-warn-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-exclamation-triangle text-warn-600 dark:text-warn-400"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Expiring Soon</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ overview.expiring_soon }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-brand-600 dark:text-brand-400"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">${{ formatCurrency(overview.total_revenue) }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-users text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Users</p>
              <p class="text-lg font-bold text-gray-900 dark:text-white">{{ overview.total_users }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Growth & Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Growth -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Growth (Last 30 Days)</h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-brand-50 dark:bg-brand-900/20 rounded-lg text-center">
              <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">+{{ growth.new_tenants_30d }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">New Tenants</p>
            </div>
            <div class="p-4 bg-brand-50 dark:bg-brand-900/20 rounded-lg text-center">
              <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">+{{ growth.new_users_30d }}</p>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">New Users</p>
            </div>
          </div>
        </div>

        <!-- Subscription Distribution -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription Distribution</h2>
          <div v-if="subscriptionDistribution.length > 0" class="space-y-3">
            <div v-for="item in subscriptionDistribution" :key="item.plan_name" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.plan_name }}</span>
              <span class="px-2 py-1 text-xs font-bold bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 rounded-full">{{ item.count }}</span>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            <i class="fas fa-chart-pie text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
            <p>No active subscriptions yet</p>
          </div>
        </div>
      </div>

      <!-- Recent Tenants -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Tenants</h2>
          <Link :href="route('superadmin.tenants.index')" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">
            View all
          </Link>
        </div>

        <div v-if="recentTenants.length > 0" class="space-y-3">
          <div v-for="tenant in recentTenants" :key="tenant.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
            <div class="flex items-center gap-3 flex-1 min-w-0">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ tenant.name.charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <Link :href="route('superadmin.tenants.show', tenant.id)" class="font-medium text-gray-900 dark:text-white truncate hover:text-brand-600 dark:hover:text-brand-400">
                  {{ tenant.name }}
                </Link>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ tenant.email }} &middot; {{ tenant.users_count }} users &middot; {{ tenant.branches_count }} branches</p>
              </div>
            </div>
            <span :class="statusBadgeClass(tenant.subscription_status)">
              {{ tenant.subscription_status }}
            </span>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
          <i class="fas fa-building text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
          <p>No tenants yet</p>
        </div>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

defineProps({
  overview: Object,
  growth: Object,
  recentTenants: Array,
  monthlyRevenue: Array,
  subscriptionDistribution: Array,
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

const statusBadgeClass = (status) => {
  const base = 'px-2 py-1 text-xs font-medium rounded-full capitalize';
  switch (status) {
    case 'active': return `${base} bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400`;
    case 'trial': return `${base} bg-warn-100 text-warn-700 dark:bg-warn-900/30 dark:text-warn-400`;
    case 'suspended': return `${base} bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400`;
    case 'cancelled': return `${base} bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400`;
    default: return `${base} bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400`;
  }
};
</script>

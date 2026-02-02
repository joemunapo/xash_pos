<template>
  <AdminLayout page-title="Loyalty Program">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Loyalty Program</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Manage customer loyalty points and rewards</p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Members</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_members }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Points</p>
              <p class="text-3xl font-bold text-brand-600 dark:text-brand-400">{{ formatNumber(stats.total_points) }}</p>
            </div>
            <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-star text-brand-600 dark:text-brand-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Points Redeemed</p>
              <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ formatNumber(stats.points_redeemed) }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-gift text-orange-600 dark:text-orange-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active This Month</p>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.active_this_month }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-chart-line text-green-600 dark:text-green-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Tier Distribution & Filters -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Tier Distribution -->
        <div class="card p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tier Distribution</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-700"></span>
                <span class="text-sm text-gray-700 dark:text-gray-300">Bronze</span>
              </div>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ tierDistribution.bronze }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                <span class="text-sm text-gray-700 dark:text-gray-300">Silver</span>
              </div>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ tierDistribution.silver }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="text-sm text-gray-700 dark:text-gray-300">Gold</span>
              </div>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ tierDistribution.gold }}</span>
            </div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                <span class="text-sm text-gray-700 dark:text-gray-300">Platinum</span>
              </div>
              <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ tierDistribution.platinum }}</span>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="lg:col-span-2 card p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Search Members</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input v-model="filterForm.search" type="text" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Search by name, phone, or email..." @input="debouncedFilter" />
              </div>
            </div>
            <div>
              <select v-model="filterForm.tier" class="input-field" @change="applyFilters">
                <option value="">All Tiers</option>
                <option value="bronze">Bronze</option>
                <option value="silver">Silver</option>
                <option value="gold">Gold</option>
                <option value="platinum">Platinum</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Members Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Tier</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Points</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Contact</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="customers.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <i class="fas fa-star text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No loyalty members yet</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Customers will appear here once they earn points</p>
                </td>
              </tr>
              <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ customer.full_name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Member since {{ formatDate(customer.member_since) }}</p>
                </td>
                <td class="px-6 py-4">
                  <span :class="getTierClass(customer.loyalty_tier)">
                    <i class="fas fa-crown mr-1"></i>{{ customer.loyalty_tier }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ formatNumber(customer.loyalty_points) }}</span>
                </td>
                <td class="px-6 py-4">
                  <p v-if="customer.phone" class="text-sm text-gray-600 dark:text-gray-400">{{ customer.phone }}</p>
                  <p v-if="customer.email" class="text-sm text-gray-500 dark:text-gray-500">{{ customer.email }}</p>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Link :href="route('admin.customers.show', customer.id)" class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                      <i class="fas fa-eye mr-1"></i>View
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="customers.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ customers.from }}</span> to <span class="font-medium">{{ customers.to }}</span> of
              <span class="font-medium">{{ customers.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in customers.links" :key="link.label">
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
  customers: Object,
  stats: Object,
  tierDistribution: Object,
  filters: Object,
});

const filterForm = reactive({
  search: props.filters?.search || '',
  tier: props.filters?.tier || '',
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.loyalty.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US').format(value || 0);
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const getTierClass = (tier) => {
  const base = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize';
  switch (tier) {
    case 'platinum':
      return `${base} bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400`;
    case 'gold':
      return `${base} bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400`;
    case 'silver':
      return `${base} bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300`;
    default:
      return `${base} bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400`;
  }
};
</script>

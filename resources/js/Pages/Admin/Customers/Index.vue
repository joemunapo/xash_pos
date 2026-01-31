<template>
  <AdminLayout page-title="Customers">
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Customers</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your customer database</p>
        </div>
        <Link :href="route('admin.customers.create')" class="btn-primary">
          <i class="fas fa-plus mr-2"></i> Add Customer
        </Link>
      </div>

      <div class="card p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input v-model="searchQuery" type="text" placeholder="Search customers..." class="input-field pl-10" @input="debouncedSearch" />
          </div>
          <select v-model="tierFilter" @change="applyFilters" class="input-field sm:w-40">
            <option value="">All Tiers</option>
            <option v-for="tier in tiers" :key="tier.value" :value="tier.value">{{ tier.label }}</option>
          </select>
        </div>
      </div>

      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200 dark:border-slate-700">
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Customer</th>
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Contact</th>
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Tier</th>
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Points</th>
              <th class="text-right py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in customers.data" :key="customer.id" class="border-b border-gray-100 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-pink-500 flex items-center justify-center text-white font-semibold">
                    {{ customer.first_name.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ customer.first_name }} {{ customer.last_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ customer.city || '-' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4">
                <p class="text-gray-900 dark:text-white">{{ customer.phone || '-' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ customer.email || '-' }}</p>
              </td>
              <td class="py-3 px-4">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full capitalize', getTierClass(customer.loyalty_tier)]">
                  {{ customer.loyalty_tier }}
                </span>
              </td>
              <td class="py-3 px-4 text-gray-900 dark:text-white font-medium">{{ Number(customer.loyalty_points).toLocaleString() }}</td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('admin.customers.show', customer.id)" class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-eye"></i>
                  </Link>
                  <Link :href="route('admin.customers.edit', customer.id)" class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-edit"></i>
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="customers.data.length === 0" class="text-center py-12">
          <i class="fas fa-user-friends text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
          <p class="text-gray-500 dark:text-gray-400">No customers yet</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ customers: Object, filters: Object, tiers: Array });

const searchQuery = ref(props.filters?.search || '');
const tierFilter = ref(props.filters?.tier || '');
let searchTimeout = null;

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 300);
};

const applyFilters = () => {
  router.get(route('admin.customers.index'), {
    search: searchQuery.value || undefined,
    tier: tierFilter.value || undefined,
  }, { preserveState: true, preserveScroll: true });
};

const getTierClass = (tier) => {
  const classes = {
    bronze: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    silver: 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300',
    gold: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    platinum: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  };
  return classes[tier] || classes.bronze;
};
</script>




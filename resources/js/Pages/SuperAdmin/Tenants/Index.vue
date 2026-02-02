<template>
  <SuperAdminLayout page-title="Tenants">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tenants</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage all tenant accounts on the platform.</p>
        </div>
        <Link :href="route('superadmin.tenants.create')" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors shadow-sm">
          <i class="fas fa-plus mr-2"></i> New Tenant
        </Link>
      </div>

      <!-- Filters -->
      <div class="card">
        <div class="flex flex-col sm:flex-row gap-3">
          <!-- Search Input -->
          <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              v-model="filterForm.search"
              @input="debouncedFilter"
              type="text"
              placeholder="Search by name, email, or domain..."
              class="input-field pl-10 pr-10"
            />
            <button
              v-if="filterForm.search"
              @click="clearSearch"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Status Filter -->
          <div class="relative sm:w-48">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-filter text-gray-400 text-sm"></i>
            </div>
            <select
              v-model="filterForm.status"
              @change="applyFilters"
              class="input-field pl-9 appearance-none"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="trial">Trial</option>
              <option value="suspended">Suspended</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
            </div>
          </div>

          <!-- Clear Filters Button -->
          <button
            v-if="filterForm.search || filterForm.status"
            @click="clearFilters"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-slate-700 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors whitespace-nowrap"
          >
            <i class="fas fa-times-circle mr-2"></i>Clear Filters
          </button>
        </div>
      </div>

      <!-- Mobile View: Cards -->
      <div class="lg:hidden space-y-3">
        <div v-for="tenant in tenants.data" :key="tenant.id" class="card">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {{ tenant.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <Link :href="route('superadmin.tenants.show', tenant.id)" class="font-semibold text-gray-900 dark:text-white hover:text-brand-600">
                  {{ tenant.name }}
                </Link>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ tenant.email }}</p>
              </div>
            </div>
            <span :class="statusBadgeClass(tenant.subscription_status)">
              {{ tenant.subscription_status }}
            </span>
          </div>
          <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-3">
            <span><i class="fas fa-users mr-1"></i>{{ tenant.users_count }} users</span>
            <span><i class="fas fa-store mr-1"></i>{{ tenant.branches_count }} branches</span>
            <span><i class="fas fa-box mr-1"></i>{{ tenant.products_count }} products</span>
          </div>
          <div class="flex items-center gap-2">
            <Link :href="route('superadmin.tenants.show', tenant.id)" class="px-3 py-1.5 text-xs font-medium bg-brand-600 text-white rounded hover:bg-brand-700 transition-colors">
              <i class="fas fa-eye mr-1"></i>View
            </Link>
            <button v-if="tenant.subscription_status !== 'suspended'" @click="suspendTenant(tenant)" class="px-3 py-1.5 text-xs font-medium bg-danger-600 text-white rounded hover:bg-danger-700 transition-colors">
              <i class="fas fa-ban mr-1"></i>Suspend
            </button>
            <button v-else @click="activateTenant(tenant)" class="px-3 py-1.5 text-xs font-medium bg-success-600 text-white rounded hover:bg-success-700 transition-colors">
              <i class="fas fa-check mr-1"></i>Activate
            </button>
          </div>
        </div>
        <div v-if="tenants.data.length === 0" class="card text-center py-12">
          <i class="fas fa-building text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
          <p class="text-gray-500 dark:text-gray-400">No tenants found</p>
        </div>
      </div>

      <!-- Desktop View: Table -->
      <div class="hidden lg:block card !p-0 overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
            <tr>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tenant</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Plan</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Users</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Branches</th>
              <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Products</th>
              <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
            <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ tenant.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <Link :href="route('superadmin.tenants.show', tenant.id)" class="font-medium text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400">
                      {{ tenant.name }}
                    </Link>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ tenant.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <span :class="statusBadgeClass(tenant.subscription_status)">
                  {{ tenant.subscription_status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                {{ tenant.active_subscription?.plan_name || 'No plan' }}
              </td>
              <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ tenant.users_count }}</td>
              <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ tenant.branches_count }}</td>
              <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-400">{{ tenant.products_count }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('superadmin.tenants.show', tenant.id)" class="px-3 py-1.5 text-xs font-medium bg-brand-600 text-white rounded hover:bg-brand-700 transition-colors">
                    <i class="fas fa-eye mr-1"></i>View
                  </Link>
                  <button v-if="tenant.subscription_status !== 'suspended'" @click="suspendTenant(tenant)" class="px-3 py-1.5 text-xs font-medium bg-danger-600 text-white rounded hover:bg-danger-700 transition-colors">
                    <i class="fas fa-ban mr-1"></i>Suspend
                  </button>
                  <button v-else @click="activateTenant(tenant)" class="px-3 py-1.5 text-xs font-medium bg-success-600 text-white rounded hover:bg-success-700 transition-colors">
                    <i class="fas fa-check mr-1"></i>Activate
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="tenants.data.length === 0" class="text-center py-12">
          <i class="fas fa-building text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
          <p class="text-gray-500 dark:text-gray-400">No tenants found</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="tenants.last_page > 1" class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Showing {{ tenants.from }} to {{ tenants.to }} of {{ tenants.total }} tenants
        </p>
        <div class="flex gap-1">
          <template v-for="link in tenants.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              :class="[
                'px-3 py-1.5 text-sm rounded border transition-colors',
                link.active
                  ? 'bg-brand-600 text-white border-brand-600'
                  : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
              ]"
              preserve-state
              preserve-scroll
            />
            <span
              v-else
              v-html="link.label"
              class="px-3 py-1.5 text-sm rounded border border-gray-200 dark:border-slate-700 text-gray-400 dark:text-gray-600 bg-white dark:bg-slate-800"
            />
          </template>
        </div>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { confirmAction } from '@/composables/useFlashMessages';

const props = defineProps({
  tenants: Object,
  filters: Object,
});

const filterForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
});

let debounceTimeout = null;

const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => applyFilters(), 500);
};

const applyFilters = () => {
  router.get(route('superadmin.tenants.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearSearch = () => {
  filterForm.search = '';
  applyFilters();
};

const clearFilters = () => {
  filterForm.search = '';
  filterForm.status = '';
  applyFilters();
};

const suspendTenant = async (tenant) => {
  const result = await confirmAction({
    title: 'Suspend Tenant?',
    text: `Are you sure you want to suspend "${tenant.name}"? Their users will lose access.`,
    icon: 'warning',
    confirmButtonText: 'Yes, suspend',
  });
  if (result.isConfirmed) {
    router.post(route('superadmin.tenants.suspend', tenant.id));
  }
};

const activateTenant = async (tenant) => {
  const result = await confirmAction({
    title: 'Activate Tenant?',
    text: `Are you sure you want to activate "${tenant.name}"?`,
    icon: 'question',
    confirmButtonText: 'Yes, activate',
  });
  if (result.isConfirmed) {
    router.post(route('superadmin.tenants.activate', tenant.id));
  }
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

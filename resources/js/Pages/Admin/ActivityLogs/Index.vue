<template>
  <AdminLayout page-title="Activity Logs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activity Logs</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Track all system activities and changes</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div class="lg:col-span-2">
            <label class="label">Search</label>
            <input
              v-model="filterForm.search"
              type="text"
              class="input-field"
              placeholder="Search actions, models..."
              @input="debouncedFilter"
            />
          </div>
          <div>
            <label class="label">Action</label>
            <select v-model="filterForm.action" class="input-field" @change="applyFilters">
              <option value="">All Actions</option>
              <option v-for="action in actions" :key="action.value" :value="action.value">{{ action.label }}</option>
            </select>
          </div>
          <div>
            <label class="label">User</label>
            <select v-model="filterForm.user_id" class="input-field" @change="applyFilters">
              <option value="">All Users</option>
              <option v-for="user in users" :key="user.value" :value="user.value">{{ user.label }}</option>
            </select>
          </div>
          <div>
            <label class="label">Date From</label>
            <input v-model="filterForm.date_from" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">Date To</label>
            <input v-model="filterForm.date_to" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div class="lg:col-span-5 flex justify-end gap-2">
            <button @click="clearFilters" class="btn-secondary">
              <i class="fas fa-times mr-2"></i>Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Activity Log Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Timestamp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Model</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Details</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">IP Address</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">View</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="logs.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-5xl mb-3 opacity-50"></i>
                    <p class="text-lg font-medium">No activity logs found</p>
                    <p class="text-sm mt-1">Try adjusting your filters</p>
                  </div>
                </td>
              </tr>
              <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatDate(log.created_at) }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatTime(log.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ log.user?.name || 'System' }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
                    {{ log.user?.email || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="getActionBadgeClass(log.action)" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap">
                    <i :class="getActionIcon(log.action)" class="mr-1.5"></i>
                    {{ formatAction(log.action) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ getModelName(log.model_type) }}
                  </div>
                  <div v-if="log.model_id" class="text-xs text-gray-500 dark:text-gray-400">
                    ID: {{ log.model_id }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="log.new_values" class="text-sm text-gray-700 dark:text-gray-300">
                    <p class="line-clamp-2">{{ getChangeSummary(log) }}</p>
                  </div>
                  <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                    No details
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm font-mono text-gray-600 dark:text-gray-400">
                    {{ log.ip_address }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <Link
                    :href="route('admin.activity-logs.show', log.id)"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-900/20 transition-colors"
                    title="View Details"
                  >
                    <i class="fas fa-eye"></i>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ logs.from }}</span> to <span class="font-medium">{{ logs.to }}</span> of
              <span class="font-medium">{{ logs.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in logs.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 text-sm rounded-lg border transition-colors',
                    link.active
                      ? 'bg-emerald-600 text-white border-emerald-600'
                      : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'px-3 py-1 text-sm rounded-lg border opacity-50 cursor-not-allowed',
                    'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  logs: Object,
  filters: Object,
  actions: Array,
  users: Array,
});

const filterForm = reactive({
  search: props.filters.search || '',
  action: props.filters.action || '',
  user_id: props.filters.user_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.activity-logs.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.search = '';
  filterForm.action = '';
  filterForm.user_id = '';
  filterForm.date_from = '';
  filterForm.date_to = '';
  applyFilters();
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatAction = (action) => {
  return action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getActionBadgeClass = (action) => {
  const classes = {
    created: 'badge-success',
    updated: 'badge-info',
    deleted: 'badge-danger',
    login: 'badge-primary',
    logout: 'badge-secondary',
    failed_login: 'badge-warning',
  };
  return classes[action] || 'badge-secondary';
};

const getActionIcon = (action) => {
  const icons = {
    created: 'fas fa-plus',
    updated: 'fas fa-edit',
    deleted: 'fas fa-trash',
    login: 'fas fa-sign-in-alt',
    logout: 'fas fa-sign-out-alt',
    failed_login: 'fas fa-exclamation-triangle',
  };
  return icons[action] || 'fas fa-info-circle';
};

const getModelName = (modelType) => {
  if (!modelType) return 'N/A';
  const parts = modelType.split('\\');
  return parts[parts.length - 1];
};

const getChangeSummary = (log) => {
  if (!log.new_values) return '';
  
  const changes = Object.entries(log.new_values)
    .slice(0, 3)
    .map(([key, value]) => `${key}: ${value}`)
    .join(', ');
  
  const moreCount = Object.keys(log.new_values).length - 3;
  return changes + (moreCount > 0 ? ` +${moreCount} more` : '');
};
</script>

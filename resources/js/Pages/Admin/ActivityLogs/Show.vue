<template>
  <AdminLayout page-title="Activity Details">
    <div class="max-w-4xl space-y-6">
      <!-- Header -->
      <div class="mb-6">
        <Link :href="route('admin.activity-logs.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Activity Logs
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activity Log Details</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Detailed information about this activity</p>
      </div>

      <!-- Main Info Card -->
      <div class="card p-6 space-y-6">
        <div class="pb-4 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <span :class="getActionBadgeClass(log.action)" class="badge text-base">
              <i :class="getActionIcon(log.action)" class="mr-2"></i>
              {{ formatAction(log.action) }}
            </span>
          </h2>
        </div>

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="label">Timestamp</label>
            <p class="text-gray-900 dark:text-white font-medium">
              {{ formatDateTime(log.created_at) }}
            </p>
          </div>

          <div>
            <label class="label">Performed By</label>
            <p class="text-gray-900 dark:text-white font-medium">
              {{ log.user?.name || 'System' }}
            </p>
            <p v-if="log.user" class="text-sm text-gray-500 dark:text-gray-400">
              {{ log.user.email }}
            </p>
          </div>

          <div>
            <label class="label">Model Type</label>
            <p class="text-gray-900 dark:text-white font-medium">
              {{ getModelName(log.model_type) }}
            </p>
            <p v-if="log.model_id" class="text-sm text-gray-500 dark:text-gray-400">
              ID: {{ log.model_id }}
            </p>
          </div>

          <div>
            <label class="label">Branch</label>
            <p class="text-gray-900 dark:text-white font-medium">
              {{ log.branch?.name || 'N/A' }}
            </p>
          </div>

          <div>
            <label class="label">IP Address</label>
            <p class="text-gray-900 dark:text-white font-medium font-mono">
              {{ log.ip_address }}
            </p>
          </div>

          <div>
            <label class="label">User Agent</label>
            <p class="text-gray-900 dark:text-white text-sm break-all">
              {{ log.user_agent || 'N/A' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Old Values -->
      <div v-if="log.old_values && Object.keys(log.old_values).length > 0" class="card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fas fa-history text-orange-500"></i>
          Previous Values
        </h3>
        <div class="bg-gray-50 dark:bg-slate-800 rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(value, key) in log.old_values" :key="key">
              <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ key }}</label>
              <p class="text-gray-900 dark:text-white mt-1 font-medium">{{ formatValue(value) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- New Values -->
      <div v-if="log.new_values && Object.keys(log.new_values).length > 0" class="card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fas fa-star text-brand-500"></i>
          New Values
        </h3>
        <div class="bg-brand-50 dark:bg-brand-900/20 rounded-lg p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(value, key) in log.new_values" :key="key">
              <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ key }}</label>
              <p class="text-gray-900 dark:text-white mt-1 font-medium">{{ formatValue(value) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Changes Summary (if both old and new values exist) -->
      <div v-if="hasChanges" class="card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fas fa-exchange-alt text-blue-500"></i>
          Changes Summary
        </h3>
        <div class="space-y-3">
          <div v-for="change in getChanges()" :key="change.field" class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ change.field }}</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded text-sm line-through">
                {{ formatValue(change.old) }}
              </span>
              <i class="fas fa-arrow-right text-gray-400"></i>
              <span class="px-3 py-1 bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 rounded text-sm font-medium">
                {{ formatValue(change.new) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  log: Object,
});

const hasChanges = computed(() => {
  return props.log.old_values && props.log.new_values && 
         Object.keys(props.log.old_values).length > 0 && 
         Object.keys(props.log.new_values).length > 0;
});

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  });
};

const formatAction = (action) => {
  return action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatValue = (value) => {
  if (value === null || value === undefined) return 'N/A';
  if (typeof value === 'boolean') return value ? 'Yes' : 'No';
  if (typeof value === 'object') return JSON.stringify(value, null, 2);
  return String(value);
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

const getChanges = () => {
  if (!hasChanges.value) return [];
  
  const changes = [];
  const oldValues = props.log.old_values || {};
  const newValues = props.log.new_values || {};
  
  // Get all unique keys
  const allKeys = new Set([...Object.keys(oldValues), ...Object.keys(newValues)]);
  
  allKeys.forEach(key => {
    if (oldValues[key] !== newValues[key]) {
      changes.push({
        field: key,
        old: oldValues[key],
        new: newValues[key],
      });
    }
  });
  
  return changes;
};
</script>

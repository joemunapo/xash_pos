<template>
  <AdminLayout page-title="Branch Details">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <Link :href="route('admin.branches.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400 mb-4">
            <i class="fas fa-arrow-left mr-2"></i> Back to Branches
          </Link>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ branch.name }}</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">{{ branch.code || 'No code assigned' }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span :class="['px-3 py-1 text-sm font-medium rounded-lg', branch.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
            {{ branch.is_active ? 'Active' : 'Inactive' }}
          </span>
          <Link :href="route('admin.branches.edit', branch.id)" class="px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Edit Branch
          </Link>
        </div>
      </div>

      <!-- Branch Info Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Basic Information</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Branch Name</dt>
              <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ branch.name }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Branch Code</dt>
              <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ branch.code || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Status</dt>
              <dd class="text-sm font-medium" :class="branch.is_active ? 'text-brand-600' : 'text-gray-500'">
                {{ branch.is_active ? 'Active' : 'Inactive' }}
              </dd>
            </div>
          </dl>
        </div>

        <!-- Location -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Location</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500 dark:text-gray-400">City</dt>
              <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ branch.city || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Address</dt>
              <dd class="text-sm text-gray-900 dark:text-white">{{ branch.address || 'No address provided' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Contact -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Contact</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Phone</dt>
              <dd class="text-sm font-medium text-gray-900 dark:text-white">
                <a v-if="branch.phone" :href="'tel:' + branch.phone" class="text-brand-600 hover:text-brand-700">{{ branch.phone }}</a>
                <span v-else>-</span>
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
              <dd class="text-sm font-medium text-gray-900 dark:text-white">
                <a v-if="branch.email" :href="'mailto:' + branch.email" class="text-brand-600 hover:text-brand-700">{{ branch.email }}</a>
                <span v-else>-</span>
              </dd>
            </div>
          </dl>
        </div>

        <!-- Receipt Settings -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Receipt Settings</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Header</dt>
              <dd class="text-sm text-gray-900 dark:text-white">{{ branch.receipt_header || 'Not set' }}</dd>
            </div>
            <div>
              <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Footer</dt>
              <dd class="text-sm text-gray-900 dark:text-white">{{ branch.receipt_footer || 'Not set' }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Users Section -->
      <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Assigned Users</h3>
        </div>
        <div v-if="branch.users && branch.users.length > 0">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 dark:bg-slate-800">
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Email</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Role</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in branch.users" :key="user.id" class="border-b border-gray-100 dark:border-slate-800">
                <td class="py-3 px-4 text-sm text-gray-900 dark:text-white">{{ user.name }}</td>
                <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                <td class="py-3 px-4">
                  <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 capitalize">
                    {{ user.pivot?.role || user.role }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span :class="['px-2 py-1 text-xs font-medium rounded', user.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
          <i class="fas fa-users text-3xl mb-2 text-gray-300 dark:text-gray-600"></i>
          <p>No users assigned to this branch</p>
        </div>
      </div>

      <!-- Timestamps -->
      <div class="text-sm text-gray-500 dark:text-gray-400">
        <p>Created: {{ formatDate(branch.created_at) }}</p>
        <p>Last Updated: {{ formatDate(branch.updated_at) }}</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useFlashMessages } from '@/Composables/useFlashMessages';

// Initialize flash messages
useFlashMessages();

const props = defineProps({
  branch: Object,
});

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

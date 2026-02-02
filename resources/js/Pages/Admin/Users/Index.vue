<template>
  <AdminLayout page-title="Users">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users Management</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage staff members and their permissions</p>
        </div>
        <Link :href="route('admin.users.create')" class="btn-primary">
          <i class="fas fa-user-plus mr-2"></i>Add New User
        </Link>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="label">Search</label>
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
              <input
                v-model="filterForm.search"
                type="text"
                class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                placeholder="Search by name, email, or phone..."
                @input="debouncedFilter"
              />
            </div>
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="filterForm.status" class="input-field" @change="applyFilters">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Cards View (Mobile) -->
      <div class="lg:hidden space-y-4">
        <div
          v-for="user in users.data"
          :key="user.id"
          class="card p-4 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-brand-400 via-brand-500 to-brand-500 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                <p v-if="user.phone_number" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                  <i class="fas fa-phone mr-1"></i>{{ user.phone_number }}
                </p>
              </div>
            </div>
            <span :class="['px-2 py-1 text-xs font-medium rounded-full', user.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
              {{ user.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <div class="space-y-2 mb-4">
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500 dark:text-gray-400">Role:</span>
              <span :class="['px-2 py-0.5 text-xs font-medium rounded-full capitalize', getRoleBadgeClass(user.role)]">
                {{ user.role }}
              </span>
            </div>
            <div v-if="user.branches?.length" class="flex items-start gap-2">
              <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">Branches:</span>
              <div class="flex flex-wrap gap-1">
                <span v-for="branch in user.branches" :key="branch.id" class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded">
                  {{ branch.name }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-slate-700">
            <Link :href="route('admin.users.edit', user.id)" class="flex-1 btn-secondary text-center">
              <i class="fas fa-edit mr-1"></i>Edit
            </Link>
            <button @click="confirmDelete(user)" class="flex-1 btn-danger">
              <i class="fas fa-trash mr-1"></i>Delete
            </button>
          </div>
        </div>

        <!-- Mobile Empty State -->
        <div v-if="users.data.length === 0" class="card p-12 text-center">
          <i class="fas fa-users text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users found</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-4">Try adjusting your filters or add a new user</p>
          <Link :href="route('admin.users.create')" class="btn-primary inline-flex items-center">
            <i class="fas fa-user-plus mr-2"></i>Add New User
          </Link>
        </div>
      </div>

      <!-- Users Table View (Desktop) -->
      <div class="hidden lg:block card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branches</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="users.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-users text-6xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium mb-1">No users found</p>
                    <p class="text-sm mb-4">Try adjusting your filters or add a new user</p>
                    <Link :href="route('admin.users.create')" class="btn-primary">
                      <i class="fas fa-user-plus mr-2"></i>Add New User
                    </Link>
                  </div>
                </td>
              </tr>
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 via-brand-500 to-brand-500 flex items-center justify-center text-white font-bold shadow-lg ring-2 ring-white dark:ring-slate-900">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                      <p v-if="user.phone_number" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        <i class="fas fa-phone mr-1"></i>{{ user.phone_number }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize whitespace-nowrap', getRoleBadgeClass(user.role)]">
                    <i :class="[getRoleIcon(user.role), 'mr-1.5']"></i>
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="branch in user.branches?.slice(0, 2)" :key="branch.id" class="px-2 py-1 text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded font-medium">
                      {{ branch.name }}
                    </span>
                    <span v-if="user.branches?.length > 2" class="px-2 py-1 text-xs bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 rounded font-medium">
                      +{{ user.branches.length - 2 }} more
                    </span>
                    <span v-if="!user.branches?.length" class="text-gray-400 text-sm italic">No branches</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', user.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                    <i :class="[user.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle', 'mr-1.5']"></i>
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Link
                      :href="route('admin.users.edit', user.id)"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 transition-colors"
                      title="Edit User"
                    >
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button
                      @click="confirmDelete(user)"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                      title="Delete User"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ users.from }}</span> to <span class="font-medium">{{ users.to }}</span> of
              <span class="font-medium">{{ users.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in users.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 text-sm rounded-lg border transition-colors',
                    link.active
                      ? 'bg-brand-600 text-white border-brand-600'
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
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { confirmAction } from '@/composables/useFlashMessages';

const props = defineProps({
  users: Object,
  branches: Array,
  filters: Object,
  roles: Array,
});

const filterForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.users.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.search = '';
  filterForm.status = '';
  applyFilters();
};

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    manager: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    cashier: 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400',
    stockist: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
  };
  return classes[role] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
};

const getRoleIcon = (role) => {
  const icons = {
    admin: 'fas fa-user-shield',
    manager: 'fas fa-user-tie',
    cashier: 'fas fa-cash-register',
    stockist: 'fas fa-boxes',
  };
  return icons[role] || 'fas fa-user';
};

const confirmDelete = async (user) => {
  const result = await confirmAction({
    title: 'Delete User?',
    text: `Are you sure you want to delete ${user.name}? This action cannot be undone.`,
    icon: 'warning',
    confirmButtonText: 'Yes, delete it!',
  });

  if (result.isConfirmed) {
    router.delete(route('admin.users.destroy', user.id), {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <AdminLayout page-title="Branches">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Branches</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your store locations</p>
        </div>
        <Link :href="route('admin.branches.create')" class="btn-primary">
          <i class="fas fa-plus mr-2"></i> Add Branch
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="flex-1">
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search branches..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                @input="debouncedSearch"
              />
            </div>
          </div>
          <select v-model="statusFilter" @change="applyFilters" class="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white sm:w-36">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Branch</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Location</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Users</th>
                <th class="text-left py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="text-right py-3 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="branch in branches.data" :key="branch.id" class="border-b border-gray-100 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
                      <i class="fas fa-store text-brand-600 dark:text-brand-400 text-sm"></i>
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white text-sm">{{ branch.name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ branch.code || '-' }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <p class="text-sm text-gray-900 dark:text-white">{{ branch.city || '-' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ branch.address || '-' }}</p>
                </td>
                <td class="py-3 px-4">
                  <p class="text-sm text-gray-900 dark:text-white">{{ branch.phone || '-' }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ branch.email || '-' }}</p>
                </td>
                <td class="py-3 px-4">
                  <span class="text-sm text-gray-900 dark:text-white">{{ branch.users_count }}</span>
                </td>
                <td class="py-3 px-4">
                  <span :class="['px-2 py-1 text-xs font-medium rounded', branch.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                    {{ branch.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('admin.branches.show', branch.id)" class="px-3 py-1.5 text-xs font-medium bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                      <i class="fas fa-eye mr-1"></i>View
                    </Link>
                    <Link :href="route('admin.branches.edit', branch.id)" class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                      <i class="fas fa-edit mr-1"></i>Edit
                    </Link>
                    <button @click="confirmDelete(branch)" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                      <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="branches.data.length === 0" class="text-center py-12">
          <i class="fas fa-store text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No branches found</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first branch.</p>
          <Link :href="route('admin.branches.create')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Add Branch
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="branches.data.length > 0" class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-slate-700">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Showing {{ branches.from }} to {{ branches.to }} of {{ branches.total }} results
          </p>
          <div class="flex gap-2">
            <Link
              v-for="link in branches.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="['px-3 py-1 text-sm rounded-lg transition-colors', link.active ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700', !link.url && 'opacity-50 cursor-not-allowed']"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Branch</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to delete <strong>{{ branchToDelete?.name }}</strong>? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
          <button @click="deleteBranch" class="btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  branches: Object,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const branchToDelete = ref(null);

let searchTimeout = null;

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

const applyFilters = () => {
  router.get(route('admin.branches.index'), {
    search: searchQuery.value || undefined,
    status: statusFilter.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const confirmDelete = (branch) => {
  branchToDelete.value = branch;
  showDeleteModal.value = true;
};

const deleteBranch = () => {
  router.delete(route('admin.branches.destroy', branchToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      branchToDelete.value = null;
    }
  });
};
</script>




<template>
  <AdminLayout page-title="Suppliers">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Suppliers</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your product suppliers</p>
        </div>
        <Link :href="route('admin.suppliers.create')" class="btn-primary">
          <i class="fas fa-plus mr-2"></i>Add Supplier
        </Link>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="label">Search</label>
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input v-model="filterForm.search" type="text" class="input-field pl-10" placeholder="Search by name, email, or phone..." @input="debouncedFilter" />
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

      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Products</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="suppliers.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <i class="fas fa-truck text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No suppliers found</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Add suppliers to track your inventory sources</p>
                  <Link :href="route('admin.suppliers.create')" class="btn-primary inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add First Supplier
                  </Link>
                </td>
              </tr>
              <tr v-for="supplier in suppliers.data" :key="supplier.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ supplier.name }}</p>
                    <p v-if="supplier.city" class="text-sm text-gray-500 dark:text-gray-400">{{ supplier.city }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="space-y-1">
                    <p v-if="supplier.contact_person" class="text-sm text-gray-900 dark:text-white">{{ supplier.contact_person }}</p>
                    <p v-if="supplier.email" class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-envelope mr-1"></i>{{ supplier.email }}</p>
                    <p v-if="supplier.phone" class="text-sm text-gray-500 dark:text-gray-400"><i class="fas fa-phone mr-1"></i>{{ supplier.phone }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full">
                    {{ supplier.products_count }} products
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', supplier.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                    <i :class="[supplier.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle', 'mr-1.5']"></i>
                    {{ supplier.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Link :href="route('admin.suppliers.show', supplier.id)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-600 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors" title="View Details">
                      <i class="fas fa-eye"></i>
                    </Link>
                    <Link :href="route('admin.suppliers.edit', supplier.id)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 transition-colors" title="Edit">
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button @click="confirmDelete(supplier)" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="suppliers.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ suppliers.from }}</span> to <span class="font-medium">{{ suppliers.to }}</span> of
              <span class="font-medium">{{ suppliers.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in suppliers.links" :key="link.label">
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
import { confirmAction } from '@/composables/useFlashMessages';

const props = defineProps({
  suppliers: Object,
  filters: Object,
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
  router.get(route('admin.suppliers.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const confirmDelete = async (supplier) => {
  const result = await confirmAction({
    title: 'Delete Supplier?',
    text: `Are you sure you want to delete ${supplier.name}? This action cannot be undone.`,
    icon: 'warning',
    confirmButtonText: 'Yes, delete it!',
  });

  if (result.isConfirmed) {
    router.delete(route('admin.suppliers.destroy', supplier.id), {
      preserveScroll: true,
    });
  }
};
</script>

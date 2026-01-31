<template>
  <AdminLayout page-title="Tax Management">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Rates</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage tax rates for your company</p>
        </div>
        <button @click="showCreateModal = true" class="btn-primary">
          <i class="fas fa-plus mr-2"></i>Add Tax Rate
        </button>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="label">Search</label>
            <input v-model="filterForm.search" type="text" class="input-field" placeholder="Search tax name..." @input="debouncedFilter" />
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="filterForm.status" class="input-field" @change="applyFilters">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tax Rates Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Rate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Start Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">End Date</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Default</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="filteredTaxes.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <i class="fas fa-percentage text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No tax rates found</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Add a tax rate to get started</p>
                </td>
              </tr>
              <tr v-for="tax in filteredTaxes" :key="tax.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ tax.name }}</p>
                    <p v-if="tax.description" class="text-sm text-gray-500 dark:text-gray-400">{{ tax.description }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ tax.rate }}%</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  {{ tax.start_date ? formatDate(tax.start_date) : '-' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  {{ tax.end_date ? formatDate(tax.end_date) : '-' }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span v-if="tax.is_default" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    <i class="fas fa-star mr-1"></i>Default
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="tax.is_active ? 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
                    {{ tax.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="editTax(tax)" class="btn-secondary text-sm py-1.5 px-3">
                      <i class="fas fa-edit mr-1.5"></i>Edit
                    </button>
                    <button v-if="!tax.is_default" @click="deleteTax(tax)" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit Tax Rate' : 'Add Tax Rate' }}
          </h2>
        </div>
        
        <form @submit.prevent="submitForm" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="label">Tax Name *</label>
              <input v-model="form.name" type="text" class="input-field" placeholder="e.g., VAT" required />
              <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
            </div>
            
            <div>
              <label class="label">Tax Rate (%) *</label>
              <input v-model="form.rate" type="number" step="0.01" min="0" max="100" class="input-field" placeholder="e.g., 14.5" required />
              <p v-if="form.errors.rate" class="text-red-500 text-sm mt-1">{{ form.errors.rate }}</p>
            </div>
            
            <div>
              <label class="label">Start Date</label>
              <input v-model="form.start_date" type="date" class="input-field" />
              <p v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</p>
            </div>
            
            <div class="md:col-span-2">
              <label class="label">End Date</label>
              <input v-model="form.end_date" type="date" class="input-field" />
              <p v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</p>
            </div>
            
            <div class="md:col-span-2">
              <label class="label">Description</label>
              <textarea v-model="form.description" class="input-field" rows="2" placeholder="Optional description"></textarea>
              <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</p>
            </div>
            
            <div class="md:col-span-2 space-y-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_default" type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Set as default tax rate</span>
              </label>
              
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
              </label>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="form.processing">
              <i class="fas fa-save mr-2"></i>{{ form.processing ? 'Saving...' : 'Save Tax Rate' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  taxes: Array,
  filters: Object,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTax = ref(null);

const filterForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
});

const form = useForm({
  name: '',
  rate: '',
  start_date: '',
  end_date: '',
  is_default: false,
  is_active: true,
  description: '',
});

const filteredTaxes = computed(() => {
  let filtered = props.taxes;

  if (filterForm.search) {
    filtered = filtered.filter(tax => 
      tax.name.toLowerCase().includes(filterForm.search.toLowerCase())
    );
  }

  if (filterForm.status === 'active') {
    filtered = filtered.filter(tax => tax.is_active);
  } else if (filterForm.status === 'inactive') {
    filtered = filtered.filter(tax => !tax.is_active);
  }

  return filtered;
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.settings.tax'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const editTax = (tax) => {
  editingTax.value = tax;
  form.name = tax.name;
  form.rate = tax.rate;
  form.start_date = tax.start_date || '';
  form.end_date = tax.end_date || '';
  form.is_default = tax.is_default;
  form.is_active = tax.is_active;
  form.description = tax.description || '';
  showEditModal.value = true;
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingTax.value = null;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (showEditModal.value && editingTax.value) {
    form.put(route('admin.settings.tax.update', editingTax.value.id), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('admin.settings.tax.store'), {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteTax = (tax) => {
  Swal.fire({
    title: 'Delete Tax Rate?',
    text: `Are you sure you want to delete "${tax.name}"? This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.settings.tax.destroy', tax.id));
    }
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};
</script>

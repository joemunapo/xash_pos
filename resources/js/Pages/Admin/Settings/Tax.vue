<template>
  <AdminLayout page-title="Tax Management">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tax Settings</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Configure tax collection for your business</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-6">
          <!-- Enable Tax Card -->
          <div class="card p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div :class="['w-14 h-14 rounded-lg flex items-center justify-center', taxEnabled ? 'bg-brand-100 dark:bg-brand-900/30' : 'bg-gray-100 dark:bg-slate-800']">
                  <i :class="['fas fa-percentage text-2xl', taxEnabled ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400']"></i>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tax Collection</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ taxEnabled ? 'Tax is being applied to sales' : 'Tax is currently disabled' }}
                  </p>
                </div>
              </div>
              <button
                @click="toggleTaxEnabled"
                :class="[
                  'relative inline-flex h-8 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2',
                  taxEnabled ? 'bg-brand-600' : 'bg-gray-200 dark:bg-slate-700'
                ]"
              >
                <span
                  :class="[
                    'pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    taxEnabled ? 'translate-x-6' : 'translate-x-0'
                  ]"
                ></span>
              </button>
            </div>
          </div>

          <!-- Branch Tax Settings -->
          <div v-if="taxEnabled && branches.length > 0" class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              <i class="fas fa-store mr-2 text-brand-500"></i>Branch Tax Settings
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Enable or disable tax collection per branch</p>
            <div class="space-y-3">
              <div
                v-for="branch in branches"
                :key="branch.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-brand-600 dark:text-brand-400"></i>
                  </div>
                  <span class="font-medium text-gray-900 dark:text-white">{{ branch.name }}</span>
                </div>
                <button
                  @click="toggleBranchTax(branch)"
                  :class="[
                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2',
                    branchTaxSettings[branch.id] ? 'bg-brand-600' : 'bg-gray-200 dark:bg-slate-700'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      branchTaxSettings[branch.id] ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  ></span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Tax Rates -->
        <div class="space-y-6">
          <!-- Tax Rates Header -->
          <div class="card p-6" :class="{ 'opacity-50 pointer-events-none': !taxEnabled }">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                <i class="fas fa-list mr-2 text-brand-500"></i>Tax Rates
              </h3>
              <button @click="showCreateModal = true" class="btn-primary text-sm" :disabled="!taxEnabled">
                <i class="fas fa-plus mr-2"></i>Add Rate
              </button>
            </div>

            <!-- Tax Rates List -->
            <div class="space-y-3">
              <div v-if="taxes.length === 0" class="text-center py-8">
                <i class="fas fa-percentage text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                <p class="text-gray-500 dark:text-gray-400">No tax rates configured</p>
                <button @click="showCreateModal = true" class="btn-primary mt-3 text-sm" :disabled="!taxEnabled">
                  <i class="fas fa-plus mr-2"></i>Add Your First Tax Rate
                </button>
              </div>

              <div
                v-for="tax in taxes"
                :key="tax.id"
                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-800 rounded-lg"
              >
                <div class="flex items-center gap-4 min-w-0">
                  <div class="w-16 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ tax.rate }}%</span>
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-medium text-gray-900 dark:text-white">{{ tax.name }}</p>
                      <span v-if="tax.is_default" class="px-2 py-0.5 text-xs font-medium bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 rounded-full">
                        Default
                      </span>
                      <span v-if="!tax.is_active" class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-full">
                        Inactive
                      </span>
                    </div>
                    <p v-if="tax.description" class="text-sm text-gray-500 dark:text-gray-400">{{ tax.description }}</p>
                    <p v-if="tax.start_date || tax.end_date" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                      {{ tax.start_date ? formatDate(tax.start_date) : 'Start' }} - {{ tax.end_date ? formatDate(tax.end_date) : 'No end' }}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button @click="editTax(tax)" class="p-2 text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button v-if="!tax.is_default" @click="deleteTax(tax)" class="p-2 text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ showEditModal ? 'Edit Tax Rate' : 'Add Tax Rate' }}
          </h2>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <div>
            <label class="label">Tax Name *</label>
            <input v-model="form.name" type="text" class="input-field" placeholder="e.g., VAT, Sales Tax" required />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="label">Tax Rate (%) *</label>
            <input v-model="form.rate" type="number" step="0.01" min="0" max="100" class="input-field" placeholder="e.g., 15" required />
            <p v-if="form.errors.rate" class="text-red-500 text-sm mt-1">{{ form.errors.rate }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Start Date</label>
              <input v-model="form.start_date" type="date" class="input-field" />
              <p v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</p>
            </div>
            <div>
              <label class="label">End Date</label>
              <input v-model="form.end_date" type="date" class="input-field" />
              <p v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</p>
            </div>
          </div>

          <div>
            <label class="label">Description</label>
            <textarea v-model="form.description" class="input-field" rows="2" placeholder="Optional description"></textarea>
          </div>

          <div class="space-y-3 pt-2">
            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="form.is_default" type="checkbox" class="w-5 h-5 text-brand-600 border-gray-300 rounded focus:ring-brand-500" />
              <span class="text-sm text-gray-700 dark:text-gray-300">Set as default tax rate</span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="form.is_active" type="checkbox" class="w-5 h-5 text-brand-600 border-gray-300 rounded focus:ring-brand-500" />
              <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="form.processing">
              <i :class="[form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save', 'mr-2']"></i>
              {{ form.processing ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  taxes: Array,
  branches: Array,
  taxEnabled: Boolean,
  branchTaxSettings: Object,
  filters: Object,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingTax = ref(null);
const taxEnabled = ref(props.taxEnabled);
const branchTaxSettings = reactive({ ...props.branchTaxSettings });

const form = useForm({
  name: '',
  rate: '',
  start_date: '',
  end_date: '',
  is_default: false,
  is_active: true,
  description: '',
});

const toggleTaxEnabled = () => {
  const newValue = !taxEnabled.value;
  router.post(route('admin.settings.tax.toggle'), { enabled: newValue }, {
    preserveScroll: true,
    onSuccess: () => {
      taxEnabled.value = newValue;
    },
  });
};

const toggleBranchTax = (branch) => {
  const newValue = !branchTaxSettings[branch.id];
  router.post(route('admin.settings.tax.branch.toggle', branch.id), { enabled: newValue }, {
    preserveScroll: true,
    onSuccess: () => {
      branchTaxSettings[branch.id] = newValue;
    },
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
    text: `Are you sure you want to delete "${tax.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it',
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

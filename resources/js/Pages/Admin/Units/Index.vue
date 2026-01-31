<template>
  <AdminLayout page-title="Units of Measure">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Units of Measure</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage units and conversions for your products</p>
        </div>
        <button @click="openCreateModal" class="btn-primary">
          <i class="fas fa-plus mr-2"></i> Add Unit
        </button>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-slate-700">
        <nav class="flex gap-4">
          <button
            @click="activeTab = 'units'"
            :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'units' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']"
          >
            <i class="fas fa-ruler mr-2"></i>Units
          </button>
          <button
            @click="activeTab = 'conversions'"
            :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'conversions' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']"
          >
            <i class="fas fa-exchange-alt mr-2"></i>Conversions
          </button>
        </nav>
      </div>

      <!-- Units Tab -->
      <div v-show="activeTab === 'units'">
        <!-- Filters -->
        <div class="card p-4 mb-4">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input v-model="searchQuery" type="text" placeholder="Search units..." class="input-field pl-10" @input="debouncedSearch" />
              </div>
            </div>
            <select v-model="categoryFilter" @change="applyFilters" class="input-field sm:w-40">
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <select v-model="statusFilter" @change="applyFilters" class="input-field sm:w-40">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>

        <!-- Units Table -->
        <div class="card overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200 dark:border-slate-700">
                  <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Unit</th>
                  <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Abbreviation</th>
                  <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Category</th>
                  <th class="text-center py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Decimals</th>
                  <th class="text-center py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Conversions</th>
                  <th class="text-center py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Status</th>
                  <th class="text-right py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="unit in units.data" :key="unit.id" class="border-b border-gray-100 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                  <td class="py-3 px-4">
                    <span class="font-medium text-gray-900 dark:text-white">{{ unit.name }}</span>
                    <span v-if="unit.is_base_unit" class="ml-2 px-1.5 py-0.5 text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">Base</span>
                  </td>
                  <td class="py-3 px-4">
                    <code class="px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded text-sm">{{ unit.abbreviation }}</code>
                  </td>
                  <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ unit.category || '-' }}</td>
                  <td class="py-3 px-4 text-center">
                    <i :class="unit.allow_decimal ? 'fas fa-check text-emerald-500' : 'fas fa-times text-gray-400'"></i>
                  </td>
                  <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-400">{{ unit.conversions_from_count || 0 }}</td>
                  <td class="py-3 px-4 text-center">
                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', unit.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                      {{ unit.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="py-3 px-4">
                    <div class="flex items-center justify-end gap-2">
                      <button @click="editUnit(unit)" class="px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                      </button>
                      <button @click="confirmDelete(unit)" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="units.data.length === 0" class="text-center py-12">
            <i class="fas fa-ruler text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No units found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Create your first unit of measure to get started.</p>
            <button @click="openCreateModal" class="btn-primary">
              <i class="fas fa-plus mr-2"></i> Add Unit
            </button>
          </div>

          <!-- Pagination -->
          <div v-if="units.data.length > 0" class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-slate-700">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Showing {{ units.from }} to {{ units.to }} of {{ units.total }} results
            </p>
            <div class="flex gap-2">
              <Link
                v-for="link in units.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="['px-3 py-1 text-sm rounded-lg transition-colors', link.active ? 'bg-emerald-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700', !link.url && 'opacity-50 cursor-not-allowed']"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Conversions Tab -->
      <div v-show="activeTab === 'conversions'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Add Conversion Form -->
          <div class="card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add Conversion</h3>
            <form @submit.prevent="submitConversion" class="space-y-4">
              <div class="grid grid-cols-3 gap-3 items-end">
                <div>
                  <label class="label">From Unit</label>
                  <select v-model="conversionForm.from_unit_id" class="input-field">
                    <option value="">Select</option>
                    <option v-for="unit in units.data" :key="unit.id" :value="unit.id">{{ unit.name }} ({{ unit.abbreviation }})</option>
                  </select>
                </div>
                <div>
                  <label class="label">Factor</label>
                  <input v-model="conversionForm.conversion_factor" type="number" step="0.000001" class="input-field" placeholder="1.0" />
                </div>
                <div>
                  <label class="label">To Unit</label>
                  <select v-model="conversionForm.to_unit_id" class="input-field">
                    <option value="">Select</option>
                    <option v-for="unit in units.data" :key="unit.id" :value="unit.id">{{ unit.name }} ({{ unit.abbreviation }})</option>
                  </select>
                </div>
              </div>
              <p v-if="conversionPreview" class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-slate-800 p-3 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>{{ conversionPreview }}
              </p>
              <button type="submit" :disabled="conversionForm.processing" class="btn-primary w-full">
                <span v-if="conversionForm.processing"><i class="fas fa-spinner fa-spin mr-2"></i>Adding...</span>
                <span v-else><i class="fas fa-plus mr-2"></i>Add Conversion</span>
              </button>
            </form>
          </div>

          <!-- Existing Conversions -->
          <div class="card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Existing Conversions</h3>
            <div v-if="conversions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-exchange-alt text-3xl mb-3 opacity-50"></i>
              <p>No conversions defined yet</p>
            </div>
            <div v-else class="space-y-3 max-h-80 overflow-y-auto">
              <div v-for="conv in conversions" :key="conv.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                <div class="flex items-center gap-2 text-sm">
                  <span class="font-medium text-gray-900 dark:text-white">1 {{ conv.from_unit?.abbreviation }}</span>
                  <i class="fas fa-arrow-right text-gray-400"></i>
                  <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ conv.conversion_factor }} {{ conv.to_unit?.abbreviation }}</span>
                </div>
                <button @click="deleteConversion(conv)" class="p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded transition-colors">
                  <i class="fas fa-trash text-sm"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Unit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="closeModal"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ editingUnit ? 'Edit Unit' : 'Create Unit' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="submitUnit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="label">Unit Name *</label>
              <input v-model="unitForm.name" type="text" class="input-field" placeholder="e.g., Kilograms" autofocus />
              <p v-if="unitForm.errors.name" class="text-red-500 text-sm mt-1">{{ unitForm.errors.name }}</p>
            </div>
            <div>
              <label class="label">Abbreviation *</label>
              <input v-model="unitForm.abbreviation" type="text" class="input-field" placeholder="e.g., kg" maxlength="10" />
              <p v-if="unitForm.errors.abbreviation" class="text-red-500 text-sm mt-1">{{ unitForm.errors.abbreviation }}</p>
            </div>
            <div>
              <label class="label">Category</label>
              <input v-model="unitForm.category" type="text" class="input-field" placeholder="e.g., Weight" list="categories-list" />
              <datalist id="categories-list">
                <option v-for="cat in categories" :key="cat" :value="cat" />
              </datalist>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer">
              <input type="checkbox" v-model="unitForm.allow_decimal" class="w-4 h-4 text-emerald-600 rounded" />
              <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Allow Decimals</span>
                <p class="text-xs text-gray-500">e.g., 1.5 kg</p>
              </div>
            </label>
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer">
              <input type="checkbox" v-model="unitForm.is_base_unit" class="w-4 h-4 text-emerald-600 rounded" />
              <div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Base Unit</span>
                <p class="text-xs text-gray-500">Primary in category</p>
              </div>
            </label>
          </div>

          <div v-if="editingUnit">
            <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer">
              <input type="checkbox" v-model="unitForm.is_active" class="w-4 h-4 text-emerald-600 rounded" />
              <span class="text-sm font-medium text-gray-900 dark:text-white">Active</span>
            </label>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" :disabled="unitForm.processing" class="btn-primary">
              <span v-if="unitForm.processing"><i class="fas fa-spinner fa-spin mr-2"></i>Saving...</span>
              <span v-else><i class="fas fa-check mr-2"></i>{{ editingUnit ? 'Update' : 'Create' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Unit</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to delete <strong>{{ unitToDelete?.name }}</strong>? This will also remove all associated conversions.
        </p>
        <div class="flex justify-end gap-3">
          <button @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
          <button @click="deleteUnit" class="btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  units: Object,
  conversions: Array,
  categories: Array,
  filters: Object,
});

const activeTab = ref('units');
const showModal = ref(false);
const showDeleteModal = ref(false);
const editingUnit = ref(null);
const unitToDelete = ref(null);

const searchQuery = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category || '');
const statusFilter = ref(props.filters?.status || '');

const unitForm = useForm({
  name: '',
  abbreviation: '',
  category: '',
  is_base_unit: false,
  allow_decimal: true,
  is_active: true,
  sort_order: 0,
});

const conversionForm = useForm({
  from_unit_id: '',
  to_unit_id: '',
  conversion_factor: '',
});

let searchTimeout = null;

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 300);
};

const applyFilters = () => {
  router.get(route('admin.units.index'), {
    search: searchQuery.value || undefined,
    category: categoryFilter.value || undefined,
    status: statusFilter.value || undefined,
  }, { preserveState: true, preserveScroll: true });
};

const openCreateModal = () => {
  editingUnit.value = null;
  unitForm.reset();
  unitForm.clearErrors();
  showModal.value = true;
};

const editUnit = (unit) => {
  editingUnit.value = unit;
  unitForm.name = unit.name;
  unitForm.abbreviation = unit.abbreviation;
  unitForm.category = unit.category || '';
  unitForm.is_base_unit = unit.is_base_unit;
  unitForm.allow_decimal = unit.allow_decimal;
  unitForm.is_active = unit.is_active;
  unitForm.sort_order = unit.sort_order || 0;
  unitForm.clearErrors();
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingUnit.value = null;
  unitForm.reset();
};

const submitUnit = () => {
  if (editingUnit.value) {
    unitForm.put(route('admin.units.update', editingUnit.value.id), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    unitForm.post(route('admin.units.store'), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  }
};

const confirmDelete = (unit) => {
  unitToDelete.value = unit;
  showDeleteModal.value = true;
};

const deleteUnit = () => {
  router.delete(route('admin.units.destroy', unitToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      unitToDelete.value = null;
    },
  });
};

const conversionPreview = computed(() => {
  if (!conversionForm.from_unit_id || !conversionForm.to_unit_id || !conversionForm.conversion_factor) {
    return null;
  }
  const fromUnit = props.units.data.find(u => u.id == conversionForm.from_unit_id);
  const toUnit = props.units.data.find(u => u.id == conversionForm.to_unit_id);
  if (fromUnit && toUnit) {
    return `1 ${fromUnit.name} = ${conversionForm.conversion_factor} ${toUnit.name}`;
  }
  return null;
});

const submitConversion = () => {
  conversionForm.post(route('admin.units.conversions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      conversionForm.reset();
    },
  });
};

const deleteConversion = (conversion) => {
  if (confirm('Remove this conversion?')) {
    router.delete(route('admin.units.conversions.destroy', conversion.id), {
      preserveScroll: true,
    });
  }
};
</script>

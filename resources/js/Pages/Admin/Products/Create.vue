<template>
  <AdminLayout page-title="Create Product">
    <!-- Error Toast -->
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showErrorToast" class="fixed top-4 right-4 z-50 max-w-sm w-full bg-red-50 dark:bg-red-900/90 border border-red-200 dark:border-red-800 rounded-lg shadow-lg p-4">
        <div class="flex items-start gap-3">
          <div class="shrink-0">
            <i class="fas fa-exclamation-circle text-red-500 dark:text-red-400 text-lg"></i>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-red-800 dark:text-red-200">Validation Error</p>
            <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ errorMessage }}</p>
          </div>
          <button @click="showErrorToast = false" class="shrink-0 text-red-400 hover:text-red-600 dark:hover:text-red-300">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </Transition>

    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link :href="route('admin.products.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Products
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Product</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new product to your inventory</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <div>
                <label class="label">Product Name *</label>
                <input v-model="form.name" type="text" :class="['input-field', form.errors.name && 'border-red-500 focus:border-red-500 focus:ring-red-500']" placeholder="Enter product name" />
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="label">Category</label>
                <div class="flex gap-2">
                  <select v-model="form.category_id" :class="['input-field flex-1', form.errors.category_id && 'border-red-500 focus:border-red-500 focus:ring-red-500']">
                    <option value="">Select category</option>
                    <option v-for="cat in allCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                  <button type="button" @click="openCategoryModal" class="btn-secondary px-3" title="Add new category">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
                <p v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</p>
              </div>
              <div>
                <label class="label">Unit of Measure</label>
                <div class="flex gap-2">
                  <select v-model="form.unit" :class="['input-field flex-1', form.errors.unit && 'border-red-500 focus:border-red-500 focus:ring-red-500']">
                    <option v-for="unit in allUnits" :key="unit.value" :value="unit.value">{{ unit.label }}</option>
                  </select>
                  <button type="button" @click="openUnitModal" class="btn-secondary px-3" title="Add new unit">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
                <p v-if="form.errors.unit" class="text-red-500 text-sm mt-1">{{ form.errors.unit }}</p>
              </div>
            </div>
            <!-- Right Column -->
            <div class="space-y-4">
              <div>
                <label class="label">SKU</label>
                <input v-model="form.sku" type="text" :class="['input-field', form.errors.sku && 'border-red-500 focus:border-red-500 focus:ring-red-500']" placeholder="Auto-generated if empty" />
                <p v-if="form.errors.sku" class="text-red-500 text-sm mt-1">{{ form.errors.sku }}</p>
                <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty to auto-generate</p>
              </div>
              <div>
                <label class="label">Barcode</label>
                <input v-model="form.barcode" type="text" :class="['input-field', form.errors.barcode && 'border-red-500 focus:border-red-500 focus:ring-red-500']" placeholder="Scan or enter barcode" />
                <p v-if="form.errors.barcode" class="text-red-500 text-sm mt-1">{{ form.errors.barcode }}</p>
              </div>
              <div>
                <label class="label">PLU Code</label>
                <input v-model="form.plu_code" type="text" :class="['input-field', form.errors.plu_code && 'border-red-500 focus:border-red-500 focus:ring-red-500']" placeholder="Price look-up code" maxlength="10" />
                <p v-if="form.errors.plu_code" class="text-red-500 text-sm mt-1">{{ form.errors.plu_code }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Image & Pricing Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Product Image -->
          <div class="card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Product Image</h3>
            <div class="flex items-start gap-4">
              <div class="w-28 h-28 bg-gray-100 dark:bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 dark:border-slate-600 shrink-0">
                <img v-if="imagePreview" :src="imagePreview" alt="Product preview" class="w-full h-full object-cover" />
                <i v-else class="fas fa-image text-2xl text-gray-400 dark:text-gray-600"></i>
              </div>
              <div class="flex-1">
                <label class="btn-secondary cursor-pointer inline-flex items-center text-sm">
                  <i class="fas fa-upload mr-2"></i>
                  {{ form.image ? 'Change' : 'Upload' }}
                  <input type="file" @change="handleImageChange" accept="image/*" class="hidden" />
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">PNG, JPG up to 2MB</p>
                <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</p>
              </div>
            </div>
          </div>

          <!-- Pricing -->
          <div class="card p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pricing</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">Cost Price *</label>
                <div class="relative">
                  <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                  <input v-model="form.cost_price" type="number" step="0.01" :class="['input-field pl-8', form.errors.cost_price && 'border-red-500 focus:border-red-500 focus:ring-red-500']" placeholder="0.00" />
                </div>
                <p v-if="form.errors.cost_price" class="text-red-500 text-sm mt-1">{{ form.errors.cost_price }}</p>
              </div>
              <div>
                <label class="label">Selling Price *</label>
                <div class="relative">
                  <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                  <input v-model="form.selling_price" type="number" step="0.01" :class="['input-field pl-8', form.errors.selling_price && 'border-red-500 focus:border-red-500 focus:ring-red-500']" placeholder="0.00" />
                </div>
                <p v-if="form.errors.selling_price" class="text-red-500 text-sm mt-1">{{ form.errors.selling_price }}</p>
              </div>
            </div>
            <!-- Profit Margin Indicator -->
            <div v-if="form.cost_price > 0 && form.selling_price > 0" class="mt-4 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">Profit Margin</span>
                <span :class="profitMargin >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'" class="font-medium">
                  {{ profitMargin.toFixed(1) }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Packaging / Units -->
        <div class="card p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">Packaging Units</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Define how this product is sold (e.g., pack, carton, case)</p>
            </div>
            <button type="button" @click="addPackaging" class="btn-secondary text-sm">
              <i class="fas fa-plus mr-2"></i>Add Packaging
            </button>
          </div>

          <!-- Base Unit Info -->
          <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center gap-2 text-sm text-green-700 dark:text-green-400">
              <i class="fas fa-info-circle"></i>
              <span>Base unit: <strong>{{ getBaseUnitLabel() }}</strong> at ${{ form.selling_price || '0.00' }} each</span>
            </div>
          </div>

          <!-- Packaging List -->
          <div v-if="form.packaging.length > 0" class="space-y-3">
            <div v-for="(pkg, index) in form.packaging" :key="index" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
                <div class="sm:col-span-2">
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Name *</label>
                  <input v-model="pkg.name" type="text" class="input-field text-sm" placeholder="e.g., Pack, Carton" />
                </div>
                <div>
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Abbrev.</label>
                  <input v-model="pkg.abbreviation" type="text" class="input-field text-sm" placeholder="e.g., pk" maxlength="10" />
                </div>
                <div>
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Qty *</label>
                  <input v-model.number="pkg.quantity" type="number" min="1" class="input-field text-sm" placeholder="6" />
                </div>
                <div>
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Price (optional)</label>
                  <input v-model="pkg.selling_price" type="number" step="0.01" class="input-field text-sm" :placeholder="getCalculatedPrice(pkg.quantity)" />
                </div>
                <div class="flex gap-2">
                  <button type="button" @click="removePackaging(index)" class="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 rounded transition-colors" title="Remove">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Barcode (optional)</label>
                  <input v-model="pkg.barcode" type="text" class="input-field text-sm" placeholder="Package barcode" />
                </div>
                <div class="flex items-end">
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    <i class="fas fa-calculator mr-1"></i>
                    {{ pkg.quantity || 0 }} {{ form.unit }} = 1 {{ pkg.name || 'unit' }}
                    <span v-if="pkg.selling_price"> @ ${{ pkg.selling_price }}</span>
                    <span v-else-if="pkg.quantity && form.selling_price"> @ ${{ (pkg.quantity * parseFloat(form.selling_price || 0)).toFixed(2) }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            <i class="fas fa-box-open text-3xl mb-3 opacity-50"></i>
            <p class="text-sm">No additional packaging defined. Click "Add Packaging" to create packs, cartons, etc.</p>
          </div>
        </div>

        <!-- Inventory -->
        <div class="card p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Inventory Settings</h3>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tracking Options -->
            <div>
              <label class="label mb-3">Tracking Options</label>
              <div class="space-y-3">
                <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                  <input type="checkbox" v-model="form.track_stock" class="w-4 h-4 text-emerald-600 rounded" />
                  <div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Track Stock</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monitor inventory levels</p>
                  </div>
                </label>
                <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                  <input type="checkbox" v-model="form.track_expiry" class="w-4 h-4 text-emerald-600 rounded" />
                  <div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Track Expiry</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monitor expiration dates</p>
                  </div>
                </label>
              </div>
            </div>
            <!-- Reorder Settings -->
            <div>
              <label class="label mb-3">Reorder Settings</label>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Reorder Level</label>
                  <input v-model="form.reorder_level" type="number" class="input-field" placeholder="10" />
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Alert when stock falls below</p>
                </div>
                <div>
                  <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Reorder Quantity</label>
                  <input v-model="form.reorder_quantity" type="number" class="input-field" placeholder="50" />
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Suggested order amount</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link :href="route('admin.products.index')" class="btn-secondary">
            <i class="fas fa-times mr-2"></i> Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            <span v-if="form.processing"><i class="fas fa-spinner fa-spin mr-2"></i>Creating...</span>
            <span v-else><i class="fas fa-check mr-2"></i>Create Product</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Create Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="closeCategoryModal"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create Category</h3>
          <button @click="closeCategoryModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="submitCategory" class="space-y-4">
          <div>
            <label class="label">Category Name *</label>
            <input v-model="categoryForm.name" type="text" class="input-field" placeholder="Enter category name" autofocus />
            <p v-if="categoryForm.errors.name" class="text-red-500 text-sm mt-1">{{ categoryForm.errors.name }}</p>
          </div>
          <div>
            <label class="label">Parent Category</label>
            <select v-model="categoryForm.parent_id" class="input-field">
              <option value="">None (Top Level)</option>
              <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Description</label>
            <textarea v-model="categoryForm.description" rows="2" class="input-field" placeholder="Optional description"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
            <button type="button" @click="closeCategoryModal" class="btn-secondary">Cancel</button>
            <button type="submit" :disabled="categoryForm.processing" class="btn-primary">
              <span v-if="categoryForm.processing"><i class="fas fa-spinner fa-spin mr-2"></i>Creating...</span>
              <span v-else><i class="fas fa-plus mr-2"></i>Create</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Unit Modal -->
    <div v-if="showUnitModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="closeUnitModal"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Create Unit of Measure</h3>
          <button @click="closeUnitModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
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
              <input v-model="unitForm.category" type="text" class="input-field" placeholder="e.g., Weight" />
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
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
            <button type="button" @click="closeUnitModal" class="btn-secondary">Cancel</button>
            <button type="submit" :disabled="unitForm.processing" class="btn-primary">
              <span v-if="unitForm.processing"><i class="fas fa-spinner fa-spin mr-2"></i>Creating...</span>
              <span v-else><i class="fas fa-plus mr-2"></i>Create</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ categories: Array, units: Array, parentCategories: Array });

const allCategories = ref([...props.categories]);
const allUnits = ref([...props.units]);
const showCategoryModal = ref(false);
const showUnitModal = ref(false);
const imagePreview = ref(null);
const showErrorToast = ref(false);
const errorMessage = ref('');

// Watch for prop changes from partial reloads
watch(() => props.categories, (newCategories) => {
  allCategories.value = [...newCategories];
}, { deep: true });

watch(() => props.units, (newUnits) => {
  allUnits.value = [...newUnits];
}, { deep: true });

const form = useForm({
  name: '',
  category_id: '',
  sku: '',
  barcode: '',
  plu_code: '',
  unit: 'pcs',
  cost_price: '',
  selling_price: '',
  image: null,
  track_stock: true,
  track_expiry: false,
  reorder_level: 10,
  reorder_quantity: 50,
  packaging: [],
});

const categoryForm = useForm({
  name: '',
  parent_id: '',
  description: '',
  sort_order: 0,
});

const unitForm = useForm({
  name: '',
  abbreviation: '',
  category: '',
  is_base_unit: false,
  allow_decimal: true,
});

// Watch for form errors and show toast
watch(() => form.errors, (errors) => {
  if (Object.keys(errors).length > 0) {
    const firstError = Object.values(errors)[0];
    errorMessage.value = firstError;
    showErrorToast.value = true;
    // Auto-hide after 5 seconds
    setTimeout(() => {
      showErrorToast.value = false;
    }, 5000);
  }
}, { deep: true });

const parentCategories = computed(() => {
  return props.parentCategories || props.categories.filter(c => !c.parent_id);
});

const profitMargin = computed(() => {
  const cost = parseFloat(form.cost_price) || 0;
  const sell = parseFloat(form.selling_price) || 0;
  if (cost === 0) return 0;
  return ((sell - cost) / cost) * 100;
});

const getBaseUnitLabel = () => {
  const unit = allUnits.value.find(u => u.value === form.unit);
  return unit ? unit.label : form.unit;
};

const getCalculatedPrice = (qty) => {
  const basePrice = parseFloat(form.selling_price) || 0;
  return (basePrice * (qty || 1)).toFixed(2);
};

const addPackaging = () => {
  form.packaging.push({
    name: '',
    abbreviation: '',
    quantity: '',
    selling_price: '',
    cost_price: '',
    barcode: '',
  });
};

const removePackaging = (index) => {
  form.packaging.splice(index, 1);
};

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const openCategoryModal = () => {
  categoryForm.reset();
  categoryForm.clearErrors();
  showCategoryModal.value = true;
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
  categoryForm.reset();
};

const submitCategory = () => {
  categoryForm.post(route('admin.categories.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      const createdId = page.props.flash?.created_category_id;
      closeCategoryModal();

      // Partial reload to get updated categories
      router.reload({
        only: ['categories', 'parentCategories'],
        preserveScroll: true,
        onSuccess: () => {
          if (createdId) {
            form.category_id = createdId;
          }
        },
      });
    },
  });
};

const openUnitModal = () => {
  unitForm.reset();
  unitForm.clearErrors();
  showUnitModal.value = true;
};

const closeUnitModal = () => {
  showUnitModal.value = false;
  unitForm.reset();
};

const submitUnit = () => {
  unitForm.post(route('admin.units.store'), {
    preserveScroll: true,
    onSuccess: (page) => {
      const createdAbbreviation = page.props.flash?.created_unit_abbreviation;
      closeUnitModal();

      // Partial reload to get updated units
      router.reload({
        only: ['units'],
        preserveScroll: true,
        onSuccess: () => {
          if (createdAbbreviation) {
            form.unit = createdAbbreviation;
          }
        },
      });
    },
  });
};

const submit = () => {
  form.post(route('admin.products.store'), {
    forceFormData: true,
  });
};
</script>

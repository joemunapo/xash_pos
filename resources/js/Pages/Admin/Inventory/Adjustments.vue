<template>
  <AdminLayout page-title="Stock Adjustments">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Adjustments</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manual inventory corrections and adjustments</p>
        </div>
        <button @click="showModal = true" class="btn-primary">
          <i class="fas fa-plus mr-2"></i>New Adjustment
        </button>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
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
          <div class="flex items-end">
            <button @click="clearFilters" class="btn-secondary w-full">
              <i class="fas fa-times mr-2"></i>Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Branch</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">By</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="adjustments.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <i class="fas fa-clipboard-list text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No Adjustments</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">No stock adjustments have been made</p>
                </td>
              </tr>
              <tr v-for="adjustment in adjustments.data" :key="adjustment.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  {{ formatDate(adjustment.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ adjustment.product?.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ adjustment.product?.sku }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-700 rounded">
                    {{ adjustment.branch?.name }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span :class="['text-lg font-semibold', adjustment.quantity >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400']">
                    {{ adjustment.quantity >= 0 ? '+' : '' }}{{ adjustment.quantity }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm text-gray-900 dark:text-white">{{ adjustment.reference || '-' }}</p>
                  <p v-if="adjustment.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ adjustment.notes }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-900 dark:text-white">{{ adjustment.user?.name }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="adjustments.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ adjustments.from }}</span> to <span class="font-medium">{{ adjustments.to }}</span> of
              <span class="font-medium">{{ adjustments.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in adjustments.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 text-sm rounded-lg border', link.active ? 'bg-emerald-600 text-white border-emerald -600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
                <span v-else :class="['px-3 py-1 text-sm rounded-lg border opacity-50 cursor-not-allowed bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Adjustment Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-xl p-6 max-w-md w-full shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Stock Adjustment</h3>
        <form @submit.prevent="submitAdjustment" class="space-y-4">
          <div>
            <label class="label">Product *</label>
            <select v-model="form.product_id" class="input-field" required>
              <option value="">Select Product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} ({{ product.sku }})</option>
            </select>
          </div>
          <div>
            <label class="label">Branch *</label>
            <select v-model="form.branch_id" class="input-field" required>
              <option value="">Select Branch</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Quantity *</label>
            <input v-model="form.quantity" type="number" step="0.01" class="input-field" placeholder="e.g., 10 or -5" required />
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Use negative numbers to reduce stock</p>
          </div>
          <div>
            <label class="label">Reason *</label>
            <input v-model="form.reason" type="text" class="input-field" placeholder="e.g., Damage, Loss, Found" required />
          </div>
          <div>
            <label class="label">Notes</label>
            <textarea v-model="form.notes" class="input-field" rows="2" placeholder="Additional details..."></textarea>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" class="btn-primary flex-1">
              <i class="fas fa-save mr-2"></i>Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  adjustments: Object,
  branches: Array,
  products: Array,
  filters: Object,
});

const showModal = ref(false);
const filterForm = reactive({
  branch_id: props.filters?.branch_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const form = reactive({
  product_id: '',
  branch_id: '',
  quantity: '',
  reason: '',
  notes: '',
});

const applyFilters = () => {
  router.get(route('admin.inventory.adjustments'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.branch_id = '';
  filterForm.date_from = '';
  filterForm.date_to = '';
  applyFilters();
};

const submitAdjustment = () => {
  router.post(route('admin.inventory.adjustments.store'), form, {
    onSuccess: () => {
      showModal.value = false;
      Object.keys(form).forEach(key => form[key] = '');
    },
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <AdminLayout page-title="Stock Transfers">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Transfers</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Move inventory between branches</p>
        </div>
        <button @click="showModal = true" class="btn-primary">
          <i class="fas fa-exchange-alt mr-2"></i>New Transfer
        </button>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="filterForm.status" class="input-field" @change="applyFilters">
              <option value="">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="clearFilters" class="btn-secondary w-full">
              <i class="fas fa-times mr-2"></i>Clear Filters
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">From</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">To</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">By</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="transfers.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <i class="fas fa-truck text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No Transfers</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">No stock transfers have been made</p>
                </td>
              </tr>
              <tr v-for="transfer in transfers.data" :key="transfer.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  {{ formatDate(transfer.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <p class="font-medium text-gray-900 dark:text-white">{{ transfer.product?.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ transfer.product?.sku }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded">
                    <i class="fas fa-arrow-up mr-1"></i>{{ transfer.branch?.name }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded">
                    <i class="fas fa-arrow-down mr-1"></i>{{ transfer.toBranch?.name || 'N/A' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ Math.abs(transfer.quantity) }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-900 dark:text-white">{{ transfer.user?.name }}</span>
                  <p v-if="transfer.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ transfer.notes }}</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="transfers.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ transfers.from }}</span> to <span class="font-medium">{{ transfers.to }}</span> of
              <span class="font-medium">{{ transfers.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in transfers.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 text-sm rounded-lg border', link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
                <span v-else :class="['px-3 py-1 text-sm rounded-lg border opacity-50 cursor-not-allowed bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- New Transfer Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-xl p-6 max-w-md w-full shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Stock Transfer</h3>
        <form @submit.prevent="submitTransfer" class="space-y-4">
          <div>
            <label class="label">Product *</label>
            <select v-model="form.product_id" class="input-field" required>
              <option value="">Select Product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }} ({{ product.sku }})</option>
            </select>
          </div>
          <div>
            <label class="label">From Branch *</label>
            <select v-model="form.from_branch_id" class="input-field" required>
              <option value="">Select Source Branch</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">To Branch *</label>
            <select v-model="form.to_branch_id" class="input-field" required>
              <option value="">Select Destination Branch</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id" :disabled="branch.id === form.from_branch_id">
                {{ branch.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="label">Quantity *</label>
            <input v-model="form.quantity" type="number" step="0.01" min="0.01" class="input-field" placeholder="e.g., 10" required />
          </div>
          <div>
            <label class="label">Notes</label>
            <textarea v-model="form.notes" class="input-field" rows="2" placeholder="Transfer reason or notes..."></textarea>
          </div>
          <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="btn-secondary flex-1">Cancel</button>
            <button type="submit" class="btn-primary flex-1">
              <i class="fas fa-exchange-alt mr-2"></i>Transfer
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
  transfers: Object,
  branches: Array,
  products: Array,
  filters: Object,
});

const showModal = ref(false);
const error = ref('');

const filterForm = reactive({
  branch_id: props.filters?.branch_id || '',
  status: props.filters?.status || '',
});

const form = reactive({
  product_id: '',
  from_branch_id: '',
  to_branch_id: '',
  quantity: '',
  notes: '',
});

const applyFilters = () => {
  router.get(route('admin.inventory.transfers'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  filterForm.branch_id = '';
  filterForm.status = '';
  applyFilters();
};

const submitTransfer = () => {
  error.value = '';
  
  if (form.from_branch_id === form.to_branch_id) {
    error.value = 'Source and destination branches must be different';
    return;
  }
  
  router.post(route('admin.inventory.transfers.store'), form, {
    onSuccess: () => {
      showModal.value = false;
      Object.keys(form).forEach(key => form[key] = '');
      error.value = '';
    },
    onError: (errors) => {
      error.value = errors.message || 'Transfer failed. Please check the form.';
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

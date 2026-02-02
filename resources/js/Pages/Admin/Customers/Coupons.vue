<template>
  <AdminLayout page-title="Coupons & Vouchers">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Coupons & Vouchers</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage discount coupons and promotional vouchers</p>
        </div>
        <button @click="openCreateModal" class="btn-primary">
          <i class="fas fa-plus mr-2"></i>Create Coupon
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Coupons</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_coupons }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-ticket-alt text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Coupons</p>
              <p class="text-3xl font-bold text-brand-600 dark:text-brand-400">{{ stats.active_coupons }}</p>
            </div>
            <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-check-circle text-brand-600 dark:text-brand-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Uses</p>
              <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ stats.total_uses }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-shopping-cart text-orange-600 dark:text-orange-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Expired</p>
              <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ stats.expired }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="fas fa-clock text-red-600 dark:text-red-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="label">Search</label>
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
              <input v-model="filterForm.search" type="text" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Search by code or name..." @input="debouncedFilter" />
            </div>
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="filterForm.status" class="input-field" @change="applyFilters">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="expired">Expired</option>
            </select>
          </div>
          <div>
            <label class="label">Type</label>
            <select v-model="filterForm.type" class="input-field" @change="applyFilters">
              <option value="">All Types</option>
              <option value="percentage">Percentage</option>
              <option value="fixed">Fixed Amount</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Coupons Table -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Coupon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Value</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Usage</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Valid Period</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-28">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-if="coupons.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <i class="fas fa-ticket-alt text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-1">No coupons found</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Create your first coupon to get started</p>
                  <button @click="openCreateModal" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>Create Coupon
                  </button>
                </td>
              </tr>
              <tr v-for="coupon in coupons.data" :key="coupon.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4">
                  <div>
                    <p class="font-mono font-bold text-brand-600 dark:text-brand-400">{{ coupon.code }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ coupon.name }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', coupon.type === 'percentage' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400']">
                    {{ coupon.type === 'percentage' ? 'Percentage' : 'Fixed' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ coupon.type === 'percentage' ? coupon.value + '%' : '$' + Number(coupon.value).toFixed(2) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ coupon.times_used }}<span v-if="coupon.usage_limit"> / {{ coupon.usage_limit }}</span>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <p v-if="coupon.start_date || coupon.end_date" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ formatDate(coupon.start_date) }} - {{ formatDate(coupon.end_date) }}
                  </p>
                  <p v-else class="text-sm text-gray-500 dark:text-gray-500">No expiry</p>
                </td>
                <td class="px-6 py-4 text-center">
                  <span :class="getStatusClass(coupon)">
                    {{ getStatusText(coupon) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openEditModal(coupon)" class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                      <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button @click="confirmDelete(coupon)" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="coupons.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700 dark:text-gray-300">
              Showing <span class="font-medium">{{ coupons.from }}</span> to <span class="font-medium">{{ coupons.to }}</span> of
              <span class="font-medium">{{ coupons.total }}</span> results
            </p>
            <div class="flex gap-2">
              <template v-for="link in coupons.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 text-sm rounded-lg border', link.active ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
                <span v-else :class="['px-3 py-1 text-sm rounded-lg border opacity-50 cursor-not-allowed bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-slate-600']" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-xl p-6 max-w-2xl w-full shadow-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          {{ editingCoupon ? 'Edit Coupon' : 'Create New Coupon' }}
        </h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Coupon Code *</label>
              <div class="flex gap-2">
                <input v-model="form.code" type="text" class="input-field flex-1 uppercase" placeholder="e.g., SAVE20" required />
                <button type="button" @click="generateCode" class="btn-secondary text-sm">
                  <i class="fas fa-random"></i>
                </button>
              </div>
              <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</p>
            </div>
            <div>
              <label class="label">Name *</label>
              <input v-model="form.name" type="text" class="input-field" placeholder="e.g., Summer Sale 20%" required />
              <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
            </div>
          </div>

          <div>
            <label class="label">Description</label>
            <textarea v-model="form.description" class="input-field" rows="2" placeholder="Optional description..."></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Discount Type *</label>
              <select v-model="form.type" class="input-field" required>
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount ($)</option>
              </select>
            </div>
            <div>
              <label class="label">Value *</label>
              <input v-model="form.value" type="number" step="0.01" min="0" class="input-field" :placeholder="form.type === 'percentage' ? 'e.g., 20' : 'e.g., 10.00'" required />
              <p v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Minimum Order Amount</label>
              <input v-model="form.min_order_amount" type="number" step="0.01" min="0" class="input-field" placeholder="e.g., 50.00" />
            </div>
            <div v-if="form.type === 'percentage'">
              <label class="label">Maximum Discount</label>
              <input v-model="form.max_discount" type="number" step="0.01" min="0" class="input-field" placeholder="e.g., 100.00" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Usage Limit (Total)</label>
              <input v-model="form.usage_limit" type="number" min="1" class="input-field" placeholder="Unlimited" />
            </div>
            <div>
              <label class="label">Usage Per Customer</label>
              <input v-model="form.usage_per_customer" type="number" min="1" class="input-field" placeholder="Unlimited" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="label">Start Date</label>
              <input v-model="form.start_date" type="date" class="input-field" />
            </div>
            <div>
              <label class="label">End Date</label>
              <input v-model="form.end_date" type="date" class="input-field" />
            </div>
          </div>

          <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="w-5 h-5 text-brand-600 rounded border-gray-300 focus:ring-brand-500" />
            <label for="is_active" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">Active</label>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="btn-secondary flex-1 flex items-center justify-center">Cancel</button>
            <button type="submit" class="btn-primary flex-1 flex items-center justify-center" :disabled="form.processing">
              <i class="fas fa-save mr-2"></i>{{ form.processing ? 'Saving...' : 'Save Coupon' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { confirmAction } from '@/composables/useFlashMessages';

const props = defineProps({
  coupons: Object,
  stats: Object,
  filters: Object,
});

const showModal = ref(false);
const editingCoupon = ref(null);

const filterForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  type: props.filters?.type || '',
});

const form = useForm({
  code: '',
  name: '',
  description: '',
  type: 'percentage',
  value: '',
  min_order_amount: '',
  max_discount: '',
  usage_limit: '',
  usage_per_customer: '',
  start_date: '',
  end_date: '',
  is_active: true,
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.coupons.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const openCreateModal = () => {
  editingCoupon.value = null;
  form.reset();
  form.is_active = true;
  showModal.value = true;
};

const openEditModal = (coupon) => {
  editingCoupon.value = coupon;
  form.code = coupon.code;
  form.name = coupon.name;
  form.description = coupon.description || '';
  form.type = coupon.type;
  form.value = coupon.value;
  form.min_order_amount = coupon.min_order_amount || '';
  form.max_discount = coupon.max_discount || '';
  form.usage_limit = coupon.usage_limit || '';
  form.usage_per_customer = coupon.usage_per_customer || '';
  form.start_date = coupon.start_date || '';
  form.end_date = coupon.end_date || '';
  form.is_active = coupon.is_active;
  showModal.value = true;
};

const submitForm = () => {
  if (editingCoupon.value) {
    form.put(route('admin.coupons.update', editingCoupon.value.id), {
      onSuccess: () => {
        showModal.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('admin.coupons.store'), {
      onSuccess: () => {
        showModal.value = false;
        form.reset();
      },
    });
  }
};

const generateCode = async () => {
  const response = await fetch(route('admin.coupons.generate-code'));
  const data = await response.json();
  form.code = data.code;
};

const confirmDelete = async (coupon) => {
  const result = await confirmAction({
    title: 'Delete Coupon?',
    text: `Are you sure you want to delete "${coupon.code}"? This action cannot be undone.`,
    icon: 'warning',
    confirmButtonText: 'Yes, delete it!',
  });

  if (result.isConfirmed) {
    router.delete(route('admin.coupons.destroy', coupon.id), {
      preserveScroll: true,
    });
  }
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getStatusText = (coupon) => {
  if (!coupon.is_active) return 'Inactive';
  if (coupon.end_date && new Date(coupon.end_date) < new Date()) return 'Expired';
  if (coupon.usage_limit && coupon.times_used >= coupon.usage_limit) return 'Used Up';
  return 'Active';
};

const getStatusClass = (coupon) => {
  const base = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium';
  const status = getStatusText(coupon);
  switch (status) {
    case 'Active':
      return `${base} bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400`;
    case 'Inactive':
      return `${base} bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400`;
    case 'Expired':
    case 'Used Up':
      return `${base} bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400`;
    default:
      return `${base} bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400`;
  }
};
</script>

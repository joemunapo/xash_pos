<template>
  <AdminLayout page-title="Purchase Orders">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Orders</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage orders to suppliers</p>
        </div>
        <Link :href="route('admin.purchase-orders.create')" class="btn-primary">
          <i class="fas fa-plus mr-2"></i>Create PO
        </Link>
      </div>

      <!-- Filters -->
      <div class="card p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="label">Search</label>
            <input v-model="filterForm.search" type="text" class="input-field" placeholder="PO number..." @input="debouncedFilter" />
          </div>
          <div>
            <label class="label">Status</label>
            <select v-model="filterForm.status" class="input-field" @change="applyFilters">
              <option value="">All Status</option>
              <option value="draft">Draft</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="ordered">Ordered</option>
              <option value="received">Received</option>
            </select>
          </div>
          <div>
            <label class="label">Supplier</label>
            <select v-model="filterForm.supplier_id" class="input-field" @change="applyFilters">
              <option value="">All Suppliers</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Empty State / Coming Soon -->
      <div class="card p-12 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 rounded-full flex items-center justify-center">
          <i class="fas fa-file-invoice text-3xl text-blue-600 dark:text-blue-400"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ready to Create Purchase Orders!</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Click the "Create PO" button above to start ordering from suppliers</p>
        <div class="max-w-2xl mx-auto text-left space-y-3 mb-8">
          <div class="flex items-start gap-3">
            <i class="fas fa-check-circle text-brand-500 mt-1"></i>
            <span class="text-gray-700 dark:text-gray-300">Select supplier and add multiple products</span>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-check-circle text-brand-500 mt-1"></i>
            <span class="text-gray-700 dark:text-gray-300">Automatic PO number generation (PO-YYYYMM-####)</span>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-check-circle text-brand-500 mt-1"></i>
            <span class="text-gray-700 dark:text-gray-300">Track status from draft to received</span>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-check-circle text-brand-500 mt-1"></i>
            <span class="text-gray-700 dark:text-gray-300">Link to goods received notes for stock updates</span>
          </div>
        </div>
        <div class="flex items-center justify-center gap-4">
          <Link :href="route('admin.suppliers.index')" class="btn-secondary">
            <i class="fas fa-truck mr-2"></i>Manage Suppliers
          </Link>
          <Link :href="route('admin.purchase-orders.create')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Create First PO
          </Link>
        </div>
      </div>

      <!-- Note about database -->
      <div class="card p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
          <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
          <div class="flex-1">
            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Database Setup Pending</p>
            <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
              The Purchase Orders tables are ready to be migrated. Once the database migration is run, you'll be able to create and manage purchase orders.
            </p>
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

const props = defineProps({
  purchaseOrders: Object,
  suppliers: Array,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  supplier_id: props.filters?.supplier_id || '',
  branch_id: props.filters?.branch_id || '',
});

let debounceTimeout = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.purchase-orders.index'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>

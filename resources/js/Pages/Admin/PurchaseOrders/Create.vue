<template>
  <AdminLayout page-title="Create Purchase Order">
    <div class="max-w-6xl space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Purchase Order</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Order products from supplier</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Information -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
            Order Information
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="label">Supplier *</label>
              <select v-model="form.supplier_id" class="input-field" required>
                <option value="">Select Supplier</option>
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                  {{ supplier.name }}
                </option>
              </select>
              <p v-if="form.errors.supplier_id" class="text-red-500 text-sm mt-1">{{ form.errors.supplier_id }}</p>
            </div>
            <div>
              <label class="label">Branch *</label>
              <select v-model="form.branch_id" class="input-field" required>
                <option value="">Select Branch</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ branch.name }}
                </option>
              </select>
              <p v-if="form.errors.branch_id" class="text-red-500 text-sm mt-1">{{ form.errors.branch_id }}</p>
            </div>
            <div>
              <label class="label">Order Date *</label>
              <input v-model="form.order_date" type="date" class="input-field" required />
              <p v-if="form.errors.order_date" class="text-red-500 text-sm mt-1">{{ form.errors.order_date }}</p>
            </div>
            <div>
              <label class="label">Expected Delivery</label>
              <input v-model="form.expected_delivery" type="date" class="input-field" />
              <p v-if="form.errors.expected_delivery" class="text-red-500 text-sm mt-1">{{ form.errors.expected_delivery }}</p>
            </div>
          </div>
        </div>

        <!-- Products -->
        <div class="card p-6">
          <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Products</h2>
            <button type="button" @click="addItem" class="btn-secondary text-sm">
              <i class="fas fa-plus mr-2"></i>Add Product
            </button>
          </div>

          <div class="space-y-4">
            <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                  <label class="label text-xs">Product *</label>
                  <select v-model="item.product_id" class="input-field text-sm" required @change="updateItemPrice(index)">
                    <option value="">Select Product</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }} ({{ product.sku }})
                    </option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="label text-xs">Quantity *</label>
                  <input v-model="item.quantity" type="number" step="0.01" min="0.01" class="input-field text-sm" required @input="calculateItemTotal(index)" />
                </div>
                <div class="md:col-span-2">
                  <label class="label text-xs">Unit Price *</label>
                  <input v-model="item.unit_price" type="number" step="0.01" min="0" class="input-field text-sm" required @input="calculateItemTotal(index)" />
                </div>
                <div class="md:col-span-2">
                  <label class="label text-xs">Total</label>
                  <input :value="(item.quantity * item.unit_price).toFixed(2)" type="text" class="input-field text-sm bg-gray-100 dark:bg-slate-700" readonly />
                </div>
                <div class="md:col-span-2 flex items-end">
                  <button type="button" @click="removeItem(index)" class="btn-secondary text-sm w-full text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
              <p>No products added. Click "Add Product" to start.</p>
            </div>
          </div>

          <!-- Totals -->
          <div v-if="form.items.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
            <div class="flex justify-end">
              <div class="w-full md:w-1/3 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                  <span class="font-semibold text-gray-900 dark:text-white">${{ calculateSubtotal().toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-slate-700">
                  <span class="text-gray-900 dark:text-white">Total:</span>
                  <span class="text-emerald-600 dark:text-emerald-400">${{ calculateTotal().toFixed(2) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Notes</h2>
          <textarea v-model="form.notes" class="input-field" rows="3" placeholder="Any special instructions or notes..."></textarea>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('admin.purchase-orders.index')" class="btn-secondary">
            <i class="fas fa-times mr-2"></i>Cancel
          </Link>
          <button type="submit" class="btn-primary" :disabled="form.processing || form.items.length === 0">
            <i class="fas fa-save mr-2"></i>{{ form.processing ? 'Creating...' : 'Create Purchase Order' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  suppliers: Array,
  branches: Array,
  products: Array,
});

const form = useForm({
  supplier_id: '',
  branch_id: '',
  order_date: new Date().toISOString().split('T')[0],
  expected_delivery: '',
  items: [],
  notes: '',
});

const addItem = () => {
  form.items.push({
    product_id: '',
    quantity: 1,
    unit_price: 0,
  });
};

const removeItem = (index) => {
  form.items.splice(index, 1);
};

const updateItemPrice = (index) => {
  const item = form.items[index];
  const product = props.products.find(p => p.id === item.product_id);
  if (product) {
    item.unit_price = parseFloat(product.cost_price || 0);
  }
  calculateItemTotal(index);
};

const calculateItemTotal = (index) => {
  const item = form.items[index];
  item.total = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
};

const calculateSubtotal = () => {
  return form.items.reduce((sum, item) => {
    return sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0));
  }, 0);
};

const calculateTotal = () => {
  return calculateSubtotal();
};

const submit = () => {
  form.post(route('admin.purchase-orders.store'));
};

// Add first item by default
if (form.items.length === 0) {
  addItem();
}
</script>

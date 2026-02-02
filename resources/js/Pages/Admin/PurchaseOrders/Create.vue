<template>
  <AdminLayout page-title="Create Purchase Order">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Purchase Order</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Order products from supplier</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Order Information - Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left: Supplier & Branch -->
          <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
              <i class="fas fa-truck mr-2 text-brand-500"></i>Supplier & Destination
            </h2>
            <div class="space-y-4">
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
                <label class="label">Receiving Branch *</label>
                <select v-model="form.branch_id" class="input-field" required>
                  <option value="">Select Branch</option>
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }}
                  </option>
                </select>
                <p v-if="form.errors.branch_id" class="text-red-500 text-sm mt-1">{{ form.errors.branch_id }}</p>
              </div>
            </div>
          </div>

          <!-- Right: Dates & Notes -->
          <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
              <i class="fas fa-calendar mr-2 text-brand-500"></i>Order Details
            </h2>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
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
              <div>
                <label class="label">Notes</label>
                <textarea v-model="form.notes" class="input-field" rows="2" placeholder="Any special instructions..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Section - Full Width -->
        <div class="card p-6">
          <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              <i class="fas fa-boxes mr-2 text-brand-500"></i>Products
            </h2>
            <button type="button" @click="addItem" class="btn-secondary text-sm">
              <i class="fas fa-plus mr-2"></i>Add Product
            </button>
          </div>

          <div class="space-y-3">
            <div v-for="(item, index) in form.items" :key="index" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
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
                  <input :value="'$' + (item.quantity * item.unit_price).toFixed(2)" type="text" class="input-field text-sm bg-gray-100 dark:bg-slate-700 font-semibold" readonly />
                </div>
                <div class="md:col-span-1">
                  <button type="button" @click="removeItem(index)" class="w-full h-[42px] flex items-center justify-center rounded-lg text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 transition-colors">
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
              <div class="w-full md:w-64 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                  <span class="font-semibold text-gray-900 dark:text-white">${{ calculateSubtotal().toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-slate-700">
                  <span class="text-gray-900 dark:text-white">Total:</span>
                  <span class="text-brand-600 dark:text-brand-400">${{ calculateTotal().toFixed(2) }}</span>
                </div>
              </div>
            </div>
          </div>
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

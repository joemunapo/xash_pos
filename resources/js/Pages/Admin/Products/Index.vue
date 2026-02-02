<template>
  <AdminLayout page-title="Products">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Products</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your product catalog</p>
        </div>
        <Link :href="route('admin.products.create')" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors">
          <i class="fas fa-plus mr-2"></i>Add Product
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="flex-1">
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                @input="debouncedSearch"
              />
            </div>
          </div>
          <select v-model="categoryFilter" @change="applyFilters" class="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white sm:w-44">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <select v-model="statusFilter" @change="applyFilters" class="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white sm:w-32">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <!-- View Toggle -->
          <div class="flex rounded-lg border border-gray-300 dark:border-slate-600 overflow-hidden">
            <button
              @click="viewMode = 'grid'"
              :class="['px-3 py-2 text-sm transition-colors', viewMode === 'grid' ? 'bg-brand-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700']"
              title="Grid View"
            >
              <i class="fas fa-th-large"></i>
            </button>
            <button
              @click="viewMode = 'table'"
              :class="['px-3 py-2 text-sm transition-colors border-l border-gray-300 dark:border-slate-600', viewMode === 'table' ? 'bg-brand-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700']"
              title="Table View"
            >
              <i class="fas fa-list"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Products Grid View -->
      <div v-if="viewMode === 'grid' && products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div v-for="product in products.data" :key="product.id" class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-4 hover:shadow-md transition-shadow">
          <div class="aspect-square bg-gray-100 dark:bg-slate-800 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
            <img v-if="product.image && product.image.length > 1" :src="`/storage/${product.image}`" :alt="product.name" class="w-full h-full object-cover" />
            <i v-else class="fas fa-box text-4xl text-gray-300 dark:text-gray-600"></i>
          </div>
          <div class="space-y-2">
            <div class="flex items-start justify-between gap-2">
              <h3 class="font-medium text-gray-900 dark:text-white text-sm line-clamp-1">{{ product.name }}</h3>
              <span :class="['px-2 py-0.5 text-xs font-medium rounded', product.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                {{ product.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.category?.name || 'Uncategorized' }}</p>
            <div class="flex items-center justify-between">
              <p class="text-lg font-bold text-brand-600 dark:text-brand-400">${{ Number(product.selling_price).toFixed(2) }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ product.sku || '-' }}</p>
            </div>
            <div class="flex gap-2 pt-2">
              <Link :href="route('admin.products.edit', product.id)" class="flex-1 text-center px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                <i class="fas fa-edit mr-1"></i>Edit
              </Link>
              <button @click="confirmDelete(product)" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Table View -->
      <div v-if="viewMode === 'table' && products.data.length > 0" class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Product</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">SKU</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Category</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Cost</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 dark:bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                      <img v-if="product.image && product.image.length > 1" :src="`/storage/${product.image}`" :alt="product.name" class="w-full h-full object-cover" />
                      <i v-else class="fas fa-box text-gray-400 dark:text-gray-600"></i>
                    </div>
                    <div class="min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</p>
                      <p v-if="product.barcode" class="text-xs text-gray-500 dark:text-gray-400">{{ product.barcode }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ product.sku || '-' }}</span>
                </td>
                <td class="px-4 py-3">
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ product.category?.name || 'Uncategorized' }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                  <span class="text-sm text-gray-600 dark:text-gray-400">${{ Number(product.cost_price).toFixed(2) }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                  <span class="text-sm font-semibold text-brand-600 dark:text-brand-400">${{ Number(product.selling_price).toFixed(2) }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', product.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                    {{ product.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="route('admin.products.edit', product.id)" class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                      <i class="fas fa-edit mr-1"></i>Edit
                    </Link>
                    <button @click="confirmDelete(product)" class="px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                      <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="products.data.length === 0" class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 text-center py-12">
        <i class="fas fa-box text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No products found</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">Start building your product catalog.</p>
        <Link :href="route('admin.products.create')" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors">
          <i class="fas fa-plus mr-2"></i>Add Product
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="products.data.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Showing {{ products.from }} to {{ products.to }} of {{ products.total }} products
        </p>
        <div class="flex gap-2">
          <Link
            v-for="link in products.links"
            :key="link.label"
            :href="link.url || '#'"
            :class="['px-3 py-1 text-sm rounded-lg transition-colors', link.active ? 'bg-brand-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700', !link.url && 'opacity-50 cursor-not-allowed']"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Product</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          Are you sure you want to delete <strong>{{ productToDelete?.name }}</strong>?
        </p>
        <div class="flex justify-end gap-3">
          <button @click="showDeleteModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Cancel</button>
          <button @click="deleteProduct" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Delete</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useFlashMessages } from '@/Composables/useFlashMessages';

useFlashMessages();

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category_id || '');
const statusFilter = ref(props.filters?.status || '');
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const viewMode = ref('grid');

// Load view preference from localStorage
onMounted(() => {
  const savedView = localStorage.getItem('productsViewMode');
  if (savedView) {
    viewMode.value = savedView;
  }
});

// Save view preference to localStorage
watch(viewMode, (newValue) => {
  localStorage.setItem('productsViewMode', newValue);
});

let searchTimeout = null;

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 300);
};

const applyFilters = () => {
  router.get(route('admin.products.index'), {
    search: searchQuery.value || undefined,
    category_id: categoryFilter.value || undefined,
    status: statusFilter.value || undefined,
  }, { preserveState: true, preserveScroll: true });
};

const confirmDelete = (product) => {
  productToDelete.value = product;
  showDeleteModal.value = true;
};

const deleteProduct = () => {
  router.delete(route('admin.products.destroy', productToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      productToDelete.value = null;
    }
  });
};
</script>




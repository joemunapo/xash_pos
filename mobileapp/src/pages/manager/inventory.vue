<template>
  <div class="space-y-6">
    <!-- Toast Notification -->
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast.show"
        class="fixed top-4 right-4 z-[60] max-w-sm w-full rounded-lg shadow-lg p-4"
        :class="toast.type === 'success' ? 'bg-success-light border border-success' : 'bg-danger-light border border-danger'"
      >
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <i
              :class="toast.type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-danger'"
              class="text-lg"
            ></i>
          </div>
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium"
              :class="toast.type === 'success' ? 'text-success' : 'text-danger'"
            >
              {{ toast.title }}
            </p>
            <p
              class="text-sm mt-1"
              :class="toast.type === 'success' ? 'text-success' : 'text-danger'"
            >
              {{ toast.message }}
            </p>
          </div>
          <button
            @click="toast.show = false"
            class="flex-shrink-0"
            :class="toast.type === 'success' ? 'text-success' : 'text-danger'"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Inventory Management</h1>
        <p class="text-sm text-gray-600">Track and manage stock levels</p>
      </div>
      <button
        @click="fetchInventory"
        class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all shadow-sm flex items-center gap-2 text-sm font-medium"
      >
        <i class="fas fa-sync-alt text-sm" :class="{ 'fa-spin': loading }"></i>
        <span class="hidden md:inline">Refresh</span>
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4">
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5">
        <div class="flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 mb-1">Total Products</p>
            <p class="text-xl md:text-2xl font-bold text-gray-800 truncate">{{ summary.total_products || 0 }}</p>
          </div>
          <div class="w-10 h-10 md:w-12 md:h-12 bg-primary-light rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-box text-primary text-base md:text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5">
        <div class="flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 mb-1">Stock Value</p>
            <p class="text-xl md:text-2xl font-bold text-success truncate">${{ formatMoney(summary.total_value || 0) }}</p>
          </div>
          <div class="w-10 h-10 md:w-12 md:h-12 bg-success-light rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-dollar-sign text-success text-base md:text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5">
        <div class="flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 mb-1">Low Stock</p>
            <p class="text-xl md:text-2xl font-bold text-accent truncate">{{ summary.low_stock_count || 0 }}</p>
          </div>
          <div class="w-10 h-10 md:w-12 md:h-12 bg-accent-light rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-accent text-base md:text-xl"></i>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5">
        <div class="flex items-start justify-between gap-2">
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 mb-1">Out of Stock</p>
            <p class="text-xl md:text-2xl font-bold text-danger truncate">{{ summary.out_of_stock_count || 0 }}</p>
          </div>
          <div class="w-10 h-10 md:w-12 md:h-12 bg-danger-light rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-times-circle text-danger text-base md:text-xl"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4">
      <div class="flex items-center gap-2 md:gap-3">
        <div class="relative flex-1">
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
          <input
            v-model="searchQuery"
            @input="fetchInventory"
            type="text"
            placeholder="Search..."
            class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs md:text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
          />
        </div>

        <div class="relative min-w-[100px] md:min-w-[140px]">
          <select
            v-model="filters.stock_status"
            @change="fetchInventory"
            class="w-full appearance-none px-3 py-2 pr-8 bg-gray-50 border border-gray-200 rounded-lg text-xs md:text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all cursor-pointer"
          >
            <option value="">All Items</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
            <option value="good">Good Stock</option>
          </select>
          <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
        </div>

        <button
          @click="fetchInventory"
          class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all shadow-sm flex items-center gap-2 text-xs md:text-sm font-medium flex-shrink-0"
        >
          <i class="fas fa-filter"></i>
          <span class="hidden md:inline">Apply</span>
        </button>
      </div>
    </div>

    <!-- Inventory DataTable -->
    <DataTable
      :data="inventory"
      :columns="columns"
      :loading="loading"
      :pagination="true"
      :searchable="false"
      :initial-per-page="20"
      :actions="true"
      empty-message="No inventory items found"
      empty-icon="fas fa-warehouse"
    >
      <!-- Custom Image Cell -->
      <template #cell-image="{ row }">
        <div class="flex items-center justify-center">
          <div v-if="row.image_url" class="w-10 h-10 md:w-12 md:h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
            <img :src="row.image_url" :alt="row.product_name" class="w-full h-full object-cover" />
          </div>
          <div v-else class="w-10 h-10 md:w-12 md:h-12 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-box text-gray-400 text-sm"></i>
          </div>
        </div>
      </template>

      <!-- Custom Quantity Cell -->
      <template #cell-quantity="{ row, value }">
        <div class="flex items-center justify-end gap-2">
          <span class="font-bold text-gray-900">{{ Math.floor(value) }}</span>
          <span class="text-xs text-gray-500">{{ row.unit }}</span>
        </div>
      </template>

      <!-- Custom Status Cell -->
      <template #cell-status="{ row }">
        <span
          class="px-3 py-1 text-xs font-medium rounded-full whitespace-nowrap"
          :class="getStatusClass(row.quantity, row.reorder_level)"
        >
          {{ getStatus(row.quantity, row.reorder_level) }}
        </span>
      </template>

      <!-- Custom Stock Value Cell -->
      <template #cell-stock_value="{ value }">
        <span class="font-semibold text-success">${{ formatMoney(value) }}</span>
      </template>

      <!-- Actions Column -->
      <template #actions="{ row }">
        <div class="flex items-center justify-center gap-1 md:gap-2">
          <button
            @click="viewProduct(row)"
            class="px-2 py-1 md:px-3 md:py-1.5 text-xs font-medium bg-gray-600 text-white rounded hover:bg-gray-700 transition"
            title="View Product"
          >
            <i class="fas fa-eye"></i>
          </button>
          <button
            @click="openAdjustModal(row, 'add')"
            class="px-2 py-1 md:px-3 md:py-1.5 text-xs font-medium bg-success text-white rounded hover:bg-success hover:opacity-90 transition"
            title="Add Stock"
          >
            <i class="fas fa-plus"></i>
          </button>
          <button
            @click="openAdjustModal(row, 'remove')"
            class="px-2 py-1 md:px-3 md:py-1.5 text-xs font-medium bg-danger text-white rounded hover:bg-danger hover:opacity-90 transition"
            title="Remove Stock"
          >
            <i class="fas fa-minus"></i>
          </button>
          <button
            @click="openAdjustModal(row, 'set')"
            class="px-2 py-1 md:px-3 md:py-1.5 text-xs font-medium bg-gray-600 text-white rounded hover:bg-gray-700 transition"
            title="Set Stock"
          >
            <i class="fas fa-edit"></i>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Stock Adjustment Modal -->
    <div
      v-if="showAdjustModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeAdjustModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-100">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div
                class="w-12 h-12 rounded-lg flex items-center justify-center"
                :class="{
                  'bg-success-light': adjustType === 'add',
                  'bg-red-100': adjustType === 'remove',
                  'bg-gray-100': adjustType === 'set'
                }"
              >
                <i
                  class="text-xl"
                  :class="{
                    'fas fa-plus text-success': adjustType === 'add',
                    'fas fa-minus text-red-600': adjustType === 'remove',
                    'fas fa-edit text-gray-600': adjustType === 'set'
                  }"
                ></i>
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-800">
                  {{ adjustType === 'add' ? 'Add Stock' : adjustType === 'remove' ? 'Remove Stock' : 'Set Stock' }}
                </h2>
                <p class="text-sm text-gray-500">{{ selectedItem?.product_name }}</p>
              </div>
            </div>
            <button @click="closeAdjustModal" class="text-gray-400 hover:text-gray-600 p-2">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>

        <!-- Current Stock Info -->
        <div class="px-6 pt-4">
          <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 uppercase font-medium">Current Stock</p>
                <p class="text-2xl font-black text-gray-900 mt-1">
                  {{ parseFloat(selectedItem?.quantity || 0).toFixed(0) }} <span class="text-sm font-medium text-gray-600">{{ selectedItem?.unit }}</span>
                </p>
              </div>
              <div class="text-right">
                <p class="text-xs text-gray-500 uppercase font-medium">Reorder Level</p>
                <p class="text-lg font-bold text-gray-900 mt-1">{{ selectedItem?.reorder_level || 'Not set' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitAdjustment" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ adjustType === 'set' ? 'New Quantity *' : 'Quantity to ' + (adjustType === 'add' ? 'Add' : 'Remove') + ' *' }}
            </label>
            <input
              v-model="adjustForm.quantity"
              type="number"
              min="0"
              required
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all text-lg font-semibold text-center"
              placeholder="0"
            />
          </div>

          <!-- Preview New Quantity -->
          <div v-if="adjustForm.quantity" class="p-4 rounded-lg border" :class="previewClass">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">New Stock Level</span>
              <span class="text-xl font-bold">{{ Math.floor(previewQuantity) }} {{ selectedItem?.unit }}</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Reason (Optional)</label>
            <textarea
              v-model="adjustForm.reason"
              rows="2"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all resize-none"
              placeholder="e.g., Received shipment, Damaged goods, Stock count correction"
            ></textarea>
          </div>

          <!-- Quick Adjust Buttons -->
          <div v-if="adjustType !== 'set'" class="space-y-2">
            <p class="text-xs text-gray-500 font-medium uppercase">Quick Adjust</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="qty in [1, 5, 10, 25, 50, 100]"
                :key="qty"
                type="button"
                @click="adjustForm.quantity = qty"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium"
                :class="{ 'bg-primary-light text-primary': adjustForm.quantity == qty }"
              >
                {{ adjustType === 'add' ? '+' : '-' }}{{ qty }}
              </button>
            </div>
          </div>

          <!-- Error Display -->
          <div v-if="adjustError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-600">{{ adjustError }}</p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeAdjustModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="adjusting || !adjustForm.quantity"
              class="flex-1 px-4 py-3 text-white rounded-lg transition font-medium flex items-center justify-center gap-2 disabled:cursor-not-allowed"
              :class="{
                'bg-success hover:bg-success hover:opacity-90 disabled:opacity-50': adjustType === 'add',
                'bg-danger hover:bg-danger hover:opacity-90 disabled:opacity-50': adjustType === 'remove',
                'bg-gray-600 hover:bg-gray-700 disabled:opacity-50': adjustType === 'set'
              }"
            >
              <i v-if="adjusting" class="fas fa-spinner fa-spin"></i>
              {{ adjusting ? 'Adjusting...' : 'Confirm' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { fetchWrapper } from '@/helpers';
import DataTable from '@/components/DataTable.vue';

const router = useRouter();
const baseUrl = import.meta.env.VITE_API_URL;
const loading = ref(true);
const inventory = ref([]);
const searchQuery = ref('');
const summary = ref({});

const filters = ref({
  stock_status: '',
});

// Modal states
const showAdjustModal = ref(false);
const selectedItem = ref(null);
const adjustType = ref('add');
const adjusting = ref(false);
const adjustError = ref('');

const adjustForm = ref({
  quantity: '',
  reason: ''
});

// Toast notification
const toast = ref({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

function showToast(type, title, message) {
  toast.value = { show: true, type, title, message };
  setTimeout(() => {
    toast.value.show = false;
  }, 4000);
}

// Table columns
const columns = [
  {
    key: 'image',
    label: 'Image',
    sortable: false,
    align: 'center'
  },
  {
    key: 'product_name',
    label: 'Product',
    sortable: true,
    align: 'left',
    format: (value) => `<span class="font-semibold text-gray-800 text-xs md:text-sm truncate block max-w-[150px] md:max-w-none">${value}</span>`
  },
  {
    key: 'quantity',
    label: 'Current Stock',
    sortable: true,
    align: 'right'
  },
  {
    key: 'stock_value',
    label: 'Stock Value',
    sortable: true,
    align: 'right'
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false,
    align: 'center'
  }
];

// Computed
const previewQuantity = computed(() => {
  if (!selectedItem.value || !adjustForm.value.quantity) return 0;
  const current = parseFloat(selectedItem.value.quantity) || 0;
  const qty = parseFloat(adjustForm.value.quantity) || 0;

  if (adjustType.value === 'add') return current + qty;
  if (adjustType.value === 'remove') return Math.max(0, current - qty);
  return Math.max(0, qty); // set
});

const previewClass = computed(() => {
  if (!selectedItem.value) return 'bg-gray-50 border-gray-200';
  const reorder = selectedItem.value.reorder_level || 0;
  if (previewQuantity.value === 0) return 'bg-red-50 border-red-200';
  if (reorder && previewQuantity.value <= reorder) return 'bg-amber-50 border-amber-200';
  return 'bg-success-light border-success';
});

// Methods
async function fetchInventory() {
  loading.value = true;

  try {
    const params = new URLSearchParams({
      search: searchQuery.value,
      ...filters.value
    });

    const response = await fetchWrapper.get(`${baseUrl}/manager/inventory?${params}`);
    inventory.value = response.items || [];
    summary.value = response.summary || {};
  } catch (error) {
    console.error('Failed to fetch inventory:', error);
    showToast('error', 'Error', 'Failed to load inventory data');
  } finally {
    loading.value = false;
  }
}

function getStatus(quantity, reorderLevel) {
  if (!quantity || quantity === 0) return 'Out of Stock';
  if (reorderLevel && quantity <= reorderLevel) return 'Low Stock';
  return 'In Stock';
}

function getStatusClass(quantity, reorderLevel) {
  if (!quantity || quantity === 0) return 'bg-red-100 text-red-700';
  if (reorderLevel && quantity <= reorderLevel) return 'bg-amber-100 text-amber-700';
  return 'bg-success-light text-success';
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

// Modal functions
function openAdjustModal(item, type) {
  selectedItem.value = item;
  adjustType.value = type;
  adjustForm.value = {
    quantity: type === 'set' ? item.quantity : '',
    reason: ''
  };
  adjustError.value = '';
  showAdjustModal.value = true;
}

function closeAdjustModal() {
  showAdjustModal.value = false;
  selectedItem.value = null;
  adjustError.value = '';
}

async function submitAdjustment() {
  if (!adjustForm.value.quantity) {
    adjustError.value = 'Please enter a quantity';
    return;
  }

  adjusting.value = true;
  adjustError.value = '';

  // Save values before closing modal
  const productName = selectedItem.value.product_name;
  const productId = selectedItem.value.id;
  const currentType = adjustType.value;

  try {
    await fetchWrapper.post(`${baseUrl}/manager/inventory/${productId}/adjust`, {
      quantity: parseInt(adjustForm.value.quantity),
      type: currentType,
      reason: adjustForm.value.reason || null
    });

    // Update local inventory
    const index = inventory.value.findIndex(i => i.id === productId);
    if (index !== -1) {
      inventory.value[index].quantity = previewQuantity.value;
      // Recalculate stock value
      const costPrice = inventory.value[index].stock_value / (selectedItem.value.quantity || 1);
      inventory.value[index].stock_value = previewQuantity.value * costPrice;
    }

    closeAdjustModal();

    const actionText = currentType === 'add' ? 'added to' : currentType === 'remove' ? 'removed from' : 'set for';
    showToast('success', 'Stock Adjusted', `Stock ${actionText} ${productName}`);

    // Refresh summary
    fetchInventory();
  } catch (error) {
    console.error('Failed to adjust inventory:', error);
    adjustError.value = error.message || 'Failed to adjust inventory. Please try again.';
    showToast('error', 'Error', 'Failed to adjust stock');
  } finally {
    adjusting.value = false;
  }
}

function viewProduct(item) {
  router.push(`/manager/products/${item.id}`);
}

onMounted(() => {
  fetchInventory();
});
</script>

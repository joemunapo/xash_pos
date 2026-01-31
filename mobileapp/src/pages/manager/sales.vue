<template>
  <div class="space-y-3 md:space-y-4">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Sales Management</h1>
        <p class="text-gray-600 text-sm">{{ formatDate(filters.start_date) }} - {{ formatDate(filters.end_date) }}</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="fetchSales"
          class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all shadow-sm flex items-center gap-2 text-sm font-medium"
          :disabled="loading"
        >
          <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'" class="text-sm"></i>
          <span class="hidden md:inline">Refresh</span>
        </button>
      </div>
    </div>

    <!-- Quick Filters + More Filters Button -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-4">
      <div class="flex items-center gap-2 md:gap-3">
        <!-- Status Filter -->
        <div class="relative flex-1 md:flex-initial md:min-w-[140px]">
          <select
            v-model="filters.status"
            @change="fetchSales"
            class="w-full appearance-none px-3 py-2 pr-8 bg-gray-50 border border-gray-200 rounded-lg text-xs md:text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all cursor-pointer"
          >
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
        </div>

        <!-- Payment Method Filter -->
        <div class="relative flex-1 md:flex-initial md:min-w-[160px]">
          <select
            v-model="filters.payment_method"
            @change="fetchSales"
            class="w-full appearance-none px-3 py-2 pr-8 bg-gray-50 border border-gray-200 rounded-lg text-xs md:text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all cursor-pointer"
          >
            <option value="">All Payments</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="mobile">Mobile Money</option>
          </select>
          <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
        </div>

        <!-- More Filters Button -->
        <button
          @click="showFiltersModal = true"
          class="relative px-3 py-2 md:px-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2 text-sm font-medium flex-shrink-0"
        >
          <i class="fas fa-sliders-h text-sm"></i>
          <span class="hidden md:inline">Filters</span>
          <span v-if="activeFilterCount > 0" class="absolute -top-1 -right-1 bg-accent text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
            {{ activeFilterCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="relative">
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by receipt number, cashier..."
        class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm"
      />
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-3 gap-2 md:gap-3">
      <div class="bg-white rounded-lg border border-gray-100 p-3 text-center">
        <p class="text-gray-500 text-xs mb-1">Total Sales</p>
        <p class="text-primary text-lg md:text-xl font-bold">{{ filteredSales.length }}</p>
      </div>
      <div class="bg-white rounded-lg border border-gray-100 p-3 text-center">
        <p class="text-gray-500 text-xs mb-1">Revenue</p>
        <p class="text-success text-lg md:text-xl font-bold">${{ totalRevenue }}</p>
      </div>
      <div class="bg-white rounded-lg border border-gray-100 p-3 text-center">
        <p class="text-gray-500 text-xs mb-1">Avg. Sale</p>
        <p class="text-accent text-lg md:text-xl font-bold">${{ avgSale }}</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
    </div>

    <!-- Sales Cards -->
    <div v-else-if="filteredSales.length > 0" class="space-y-2 md:space-y-3">
      <div
        v-for="sale in filteredSales"
        :key="sale.id"
        @click="showSaleDetail(sale)"
        class="bg-white rounded-lg border border-gray-100 p-3 md:p-4 hover:shadow-md transition-shadow cursor-pointer"
      >
        <div class="flex items-start justify-between gap-3">
          <!-- Left: Main Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <p class="font-semibold text-gray-700 text-sm md:text-base truncate">{{ sale.receipt_number }}</p>
              <span
                class="px-2 py-0.5 text-xs font-medium rounded-full flex-shrink-0"
                :class="sale.status === 'completed' ? 'status-success' : 'status-failed'"
              >
                {{ sale.status }}
              </span>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
              <i class="fas fa-clock"></i>
              <span>{{ formatDateTime(sale.created_at) }}</span>
            </div>
            <div class="flex items-center gap-3 text-xs">
              <div class="flex items-center gap-1 text-gray-400">
                <i class="fas fa-user"></i>
                <span class="truncate">{{ sale.user?.name || 'Unknown' }}</span>
              </div>
              <!-- Payment Method - Show split details if applicable -->
              <div v-if="sale.payment_method === 'split' && sale.payments && sale.payments.length > 0" class="flex items-center gap-1 text-gray-400">
                <i class="fas fa-credit-card"></i>
                <span class="truncate">{{ sale.payments.map(p => formatPaymentMethod(p.method)).join(' + ') }}</span>
              </div>
              <div v-else class="flex items-center gap-1 text-gray-400">
                <i :class="getPaymentIcon(sale.payment_method)"></i>
                <span class="capitalize">{{ formatPaymentMethod(sale.payment_method) }}</span>
              </div>
            </div>
          </div>

          <!-- Right: Amount -->
          <div class="text-right flex-shrink-0">
            <p class="text-success font-bold text-lg md:text-xl">${{ formatMoney(sale.total_amount) }}</p>
            <p class="text-gray-400 text-xs">{{ sale.items?.length || 0 }} items</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg border border-gray-100 p-12 text-center">
      <i class="fas fa-receipt text-4xl text-gray-300 mb-3"></i>
      <p class="text-gray-500 text-lg">No sales found</p>
      <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
    </div>

    <!-- Bottom Filters Modal -->
    <Transition name="slide-up">
      <div v-if="showFiltersModal" class="fixed inset-0 z-50 flex items-end md:items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="showFiltersModal = false"></div>
        <div class="relative bg-white rounded-t-2xl md:rounded-2xl w-full md:max-w-2xl max-h-[85vh] overflow-hidden shadow-xl">
          <!-- Modal Header -->
          <div class="bg-primary text-white px-4 md:px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <div>
              <h3 class="text-lg font-bold">Advanced Filters</h3>
              <p class="text-primary-light text-sm">Refine your sales search</p>
            </div>
            <button @click="showFiltersModal = false" class="p-2 hover:bg-white/10 rounded-lg transition">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <!-- Modal Content -->
          <div class="overflow-y-auto max-h-[calc(85vh-140px)] p-4 md:p-6 space-y-4">
            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input
                  v-model="filters.start_date"
                  type="date"
                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input
                  v-model="filters.end_date"
                  type="date"
                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                />
              </div>
            </div>

            <!-- Time Range -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                <input
                  v-model="filters.start_time"
                  type="time"
                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                <input
                  v-model="filters.end_time"
                  type="time"
                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                />
              </div>
            </div>

            <!-- Cashier Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cashier</label>
              <div class="relative">
                <select
                  v-model="filters.cashier_id"
                  class="w-full appearance-none px-3 py-2 pr-10 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer"
                >
                  <option value="">All Cashiers</option>
                  <option v-for="cashier in cashiers" :key="cashier.id" :value="cashier.id">
                    {{ cashier.name }}
                  </option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
              </div>
            </div>

            <!-- Branch Filter (if multi-branch) -->
            <div v-if="branches.length > 1">
              <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
              <div class="relative">
                <select
                  v-model="filters.branch_id"
                  class="w-full appearance-none px-3 py-2 pr-10 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all cursor-pointer"
                >
                  <option value="">All Branches</option>
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }}
                  </option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="border-t p-4 md:p-6 flex gap-3 sticky bottom-0 bg-white">
            <button
              @click="resetFilters"
              class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition text-sm"
            >
              <i class="fas fa-redo mr-2"></i>
              Reset
            </button>
            <button
              @click="applyFilters"
              class="flex-1 px-4 py-2.5 bg-primary text-white rounded-lg font-medium hover:bg-primary-dark transition text-sm"
            >
              <i class="fas fa-check mr-2"></i>
              Apply Filters
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Sale Detail Modal -->
    <Transition name="fade">
      <div v-if="showDetailModal && selectedSale" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeDetailModal"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-lg max-h-[90vh] overflow-hidden shadow-xl">
          <!-- Modal Header -->
          <div class="bg-header text-white px-6 py-4 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold">Sale Details</h3>
              <p class="text-sm text-primary-light">{{ selectedSale.receipt_number }}</p>
            </div>
            <button @click="closeDetailModal" class="p-2 hover:bg-white/10 rounded-lg transition">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <!-- Modal Content -->
          <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6 space-y-4">
            <!-- Basic Info -->
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">Date & Time</p>
                <p class="font-medium text-gray-800">{{ formatDateTime(selectedSale.created_at) }}</p>
              </div>
              <div>
                <p class="text-gray-500">Cashier</p>
                <p class="font-medium text-gray-800">{{ selectedSale.user?.name || 'Unknown' }}</p>
              </div>
              <div>
                <p class="text-gray-500">Status</p>
                <span
                  class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                  :class="selectedSale.status === 'completed' ? 'status-success' : 'status-failed'"
                >
                  {{ selectedSale.status }}
                </span>
              </div>
              <div>
                <p class="text-gray-500">Payment Method</p>
                <p class="font-medium text-gray-800 capitalize">
                  {{ selectedSale.payment_method === 'split' ? 'Split Payment' : selectedSale.payment_method }}
                </p>
              </div>
            </div>

            <!-- Items -->
            <div>
              <h4 class="font-semibold text-gray-800 mb-2">Items</h4>
              <div class="bg-gray-50 rounded-lg divide-y divide-gray-200">
                <div
                  v-for="item in selectedSale.items"
                  :key="item.id"
                  class="p-3 flex justify-between items-center"
                >
                  <div>
                    <p class="font-medium text-gray-800">{{ item.product?.name || 'Unknown Product' }}</p>
                    <p class="text-sm text-gray-500">{{ item.quantity }} x ${{ formatMoney(item.unit_price) }}</p>
                  </div>
                  <p class="font-semibold text-gray-800">${{ formatMoney(item.line_total) }}</p>
                </div>
              </div>
            </div>

            <!-- Totals -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span class="text-gray-800">${{ formatMoney(selectedSale.subtotal) }}</span>
              </div>
              <div v-if="selectedSale.discount_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-500">Discount</span>
                <span class="text-red-600">-${{ formatMoney(selectedSale.discount_amount) }}</span>
              </div>
              <div v-if="selectedSale.tax_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-500">Tax</span>
                <span class="text-gray-800">${{ formatMoney(selectedSale.tax_amount) }}</span>
              </div>
              <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-2">
                <span class="text-gray-800">Total</span>
                <span class="text-success">${{ formatMoney(selectedSale.total_amount) }}</span>
              </div>
            </div>

            <!-- Payment Details -->
            <div>
              <h4 class="font-semibold text-gray-800 mb-2">Payment Details</h4>
              <div class="bg-blue-50 rounded-lg p-4 space-y-2">
                <!-- Split Payments -->
                <template v-if="selectedSale.payments && selectedSale.payments.length > 0">
                  <div
                    v-for="payment in selectedSale.payments"
                    :key="payment.id"
                    class="flex justify-between items-center text-sm"
                  >
                    <span class="flex items-center gap-2 text-blue-800">
                      <i :class="getPaymentIcon(payment.method)"></i>
                      <span class="capitalize">{{ formatPaymentMethod(payment.method) }}</span>
                    </span>
                    <span class="font-semibold text-blue-900">${{ formatMoney(payment.amount) }}</span>
                  </div>
                  <div class="border-t border-blue-200 pt-2 mt-2">
                    <div class="flex justify-between text-sm font-semibold">
                      <span class="text-blue-800">Total Paid</span>
                      <span class="text-blue-900">${{ formatMoney(selectedSale.amount_paid) }}</span>
                    </div>
                  </div>
                </template>

                <!-- Single Payment -->
                <template v-else>
                  <div class="flex justify-between items-center text-sm">
                    <span class="flex items-center gap-2 text-blue-800">
                      <i :class="getPaymentIcon(selectedSale.payment_method)"></i>
                      <span class="capitalize">{{ formatPaymentMethod(selectedSale.payment_method) }}</span>
                    </span>
                    <span class="font-semibold text-blue-900">${{ formatMoney(selectedSale.amount_paid) }}</span>
                  </div>
                </template>

                <!-- Change -->
                <div v-if="selectedSale.change_amount > 0" class="flex justify-between text-sm border-t border-blue-200 pt-2 mt-2">
                  <span class="text-blue-800">Change Given</span>
                  <span class="font-semibold text-success">${{ formatMoney(selectedSale.change_amount) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="border-t p-4">
            <button
              @click="closeDetailModal"
              class="w-full py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { fetchWrapper } from '@/helpers';

const baseUrl = import.meta.env.VITE_API_URL;
const loading = ref(true);
const sales = ref([]);
const cashiers = ref([]);
const branches = ref([]);
const showDetailModal = ref(false);
const showFiltersModal = ref(false);
const selectedSale = ref(null);
const searchQuery = ref('');

// Initialize filters with today's date
const today = new Date().toISOString().split('T')[0];
const filters = ref({
  status: '',
  payment_method: '',
  start_date: today,
  end_date: today,
  start_time: '',
  end_time: '',
  cashier_id: '',
  branch_id: '',
});

// Computed: Active filter count (excluding default today's date)
const activeFilterCount = computed(() => {
  let count = 0;
  if (filters.value.start_time) count++;
  if (filters.value.end_time) count++;
  if (filters.value.cashier_id) count++;
  if (filters.value.branch_id) count++;
  // Only count date if it's not today's default
  if (filters.value.start_date !== today || filters.value.end_date !== today) count++;
  return count;
});

// Computed: Filtered sales based on search
const filteredSales = computed(() => {
  if (!searchQuery.value) return sales.value;
  
  const query = searchQuery.value.toLowerCase();
  return sales.value.filter(sale => {
    return (
      sale.receipt_number?.toLowerCase().includes(query) ||
      sale.user?.name?.toLowerCase().includes(query) ||
      sale.payment_method?.toLowerCase().includes(query)
    );
  });
});

// Computed: Total revenue
const totalRevenue = computed(() => {
  const total = filteredSales.value.reduce((sum, sale) => sum + parseFloat(sale.total_amount || 0), 0);
  return formatMoney(total);
});

// Computed: Average sale
const avgSale = computed(() => {
  if (filteredSales.value.length === 0) return '0.00';
  const avg = filteredSales.value.reduce((sum, sale) => sum + parseFloat(sale.total_amount || 0), 0) / filteredSales.value.length;
  return formatMoney(avg);
});

async function fetchSales() {
  loading.value = true;

  try {
    const params = new URLSearchParams();
    
    // Add all filters to params
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key]);
      }
    });

    const response = await fetchWrapper.get(`${baseUrl}/manager/sales?${params}`);
    sales.value = response.data || [];
    
    // Fetch cashiers and branches if not already loaded
    if (cashiers.value.length === 0 && response.cashiers) {
      cashiers.value = response.cashiers;
    }
    if (branches.value.length === 0 && response.branches) {
      branches.value = response.branches;
    }
  } catch (error) {
    console.error('Failed to fetch sales:', error);
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  showFiltersModal.value = false;
  fetchSales();
}

function resetFilters() {
  filters.value = {
    status: '',
    payment_method: '',
    start_date: today,
    end_date: today,
    start_time: '',
    end_time: '',
    cashier_id: '',
    branch_id: '',
  };
  fetchSales();
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function showSaleDetail(sale) {
  selectedSale.value = sale;
  showDetailModal.value = true;
}

function closeDetailModal() {
  showDetailModal.value = false;
  selectedSale.value = null;
}

function getPaymentIcon(method) {
  const icons = {
    cash: 'fas fa-money-bill',
    ecocash: 'fas fa-mobile-alt',
    swipe: 'fas fa-credit-card',
    card: 'fas fa-credit-card',
    mobile_money: 'fas fa-mobile-alt',
  };
  return icons[method] || 'fas fa-money-bill';
}

function formatPaymentMethod(method) {
  const methods = {
    cash: 'Cash',
    ecocash: 'Ecocash',
    swipe: 'Card/Swipe',
    card: 'Card',
    mobile_money: 'Mobile Money',
  };
  return methods[method] || method;
}

onMounted(() => {
  fetchSales();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-leave-active {
  transition: transform 0.25s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
</style>

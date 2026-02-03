<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-header text-white px-4 py-3 flex items-center sticky top-0 z-40 pt-safe">
      <router-link to="/pos/dashboard" class="p-1 mr-3">
        <i class="fas fa-arrow-left"></i>
      </router-link>
      <h1 class="text-lg font-bold">Sales History</h1>
    </div>

    <!-- Date Filter -->
    <div class="px-4 py-3 bg-white shadow-sm">
      <input
        v-model="selectedDate"
        type="date"
        class="w-full px-4 py-2 bg-gray-100 border-0 rounded-lg focus:ring-2 focus:ring-primary outline-none"
        @change="fetchSales"
      />
      <div class="relative mt-2">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-300"></i>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by receipt number..."
          class="w-full pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-lg focus:ring-2 focus:ring-primary outline-none"
        />
      </div>
    </div>

    <!-- Summary Card -->
    <div class="px-4 py-3">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex">
          <div class="flex-1 text-center mr-2">
            <p class="text-2xl font-bold text-primary">{{ summary.total_sales }}</p>
            <p class="text-xs text-gray-500">Total Sales</p>
          </div>
          <div class="flex-1 text-center ml-2">
            <p class="text-2xl font-bold text-success">${{ formatMoney(summary.total_revenue) }}</p>
            <p class="text-xs text-gray-500">Revenue</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Sales List -->
    <div class="px-4">
      <div v-if="loading" class="flex items-center justify-center py-20">
        <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
      </div>

      <div v-else-if="filteredSales.length === 0" class="text-center py-20 text-gray-500">
        <i class="fas fa-receipt text-5xl mb-3 text-gray-300"></i>
        <p>{{ searchQuery ? 'No matching receipts found' : 'No sales found for this date' }}</p>
      </div>

      <div v-else class="space-y-3 pb-6">
        <div
          v-for="sale in filteredSales"
          :key="sale.id"
          @click="viewSale(sale)"
          class="bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-lg transition"
        >
          <div class="flex items-center justify-between mb-2">
            <div>
              <p class="font-medium text-gray-800">{{ sale.receipt_number }}</p>
              <p class="text-xs text-gray-500">{{ formatTime(sale.created_at) }}</p>
            </div>
            <div class="text-right">
              <p class="font-bold text-success">${{ formatMoney(sale.total_amount) }}</p>
              <span
                :class="[
                  'text-xs px-2 py-0.5 rounded-full',
                  sale.status === 'pending_sync' ? 'status-pending' :
                  sale.status === 'completed' ? 'status-success' :
                  sale.status === 'cancelled' ? 'status-failed' :
                  'bg-gray-100 text-gray-700'
                ]"
              >
                {{ sale.status === 'pending_sync' ? 'Pending Sync' : sale.status }}
              </span>
            </div>
          </div>
          <div class="flex items-center justify-between text-sm text-gray-500">
            <span>{{ sale.items?.length || 0 }} items</span>
            <span class="capitalize">{{ sale.payment_method }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sale Detail Modal -->
    <Transition name="fade">
      <div v-if="selectedSale" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-overlay" @click="selectedSale = null"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-lg max-h-[80vh] overflow-y-auto">
          <div class="sticky top-0 bg-white px-4 py-3 border-b flex items-center justify-between">
            <h2 class="text-lg font-bold">Receipt Details</h2>
            <button @click="selectedSale = null" class="p-2">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div class="p-4">
            <div class="text-center mb-4">
              <p class="text-sm text-gray-500">Receipt #</p>
              <p class="text-lg font-bold">{{ selectedSale.receipt_number }}</p>
              <p class="text-xs text-gray-500">{{ formatDateTime(selectedSale.created_at) }}</p>
            </div>

            <div class="border-t border-b py-3 mb-4">
              <div
                v-for="item in selectedSale.items"
                :key="item.id"
                class="flex justify-between py-2"
              >
                <div>
                  <p class="font-medium">{{ item.product_name }}</p>
                  <p class="text-sm text-gray-500">{{ item.quantity }} x ${{ formatMoney(item.unit_price) }}</p>
                </div>
                <p class="font-medium">${{ formatMoney(item.line_total) }}</p>
              </div>
            </div>

            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span>${{ formatMoney(selectedSale.subtotal) }}</span>
              </div>
              <div v-if="selectedSale.tax_amount > 0" class="flex justify-between text-sm">
                <span class="text-gray-500">Tax</span>
                <span>${{ formatMoney(selectedSale.tax_amount) }}</span>
              </div>
              <div v-if="selectedSale.discount_amount > 0" class="flex justify-between text-sm text-red-500">
                <span>Discount</span>
                <span>-${{ formatMoney(selectedSale.discount_amount) }}</span>
              </div>
              <div class="flex justify-between font-bold text-lg pt-2 border-t">
                <span>Total</span>
                <span class="text-success">${{ formatMoney(selectedSale.total_amount) }}</span>
              </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Payment Method</span>
                <span class="capitalize font-medium">{{ selectedSale.payment_method }}</span>
              </div>
              <div class="flex justify-between text-sm mt-1">
                <span class="text-gray-500">Amount Paid</span>
                <span class="font-medium">${{ formatMoney(selectedSale.amount_paid) }}</span>
              </div>
              <div v-if="selectedSale.change_amount > 0" class="flex justify-between text-sm mt-1">
                <span class="text-gray-500">Change</span>
                <span class="font-medium">${{ formatMoney(selectedSale.change_amount) }}</span>
              </div>
            </div>

            <div class="space-y-2">
              <button
                @click="selectedSale = null"
                class="w-full py-3 bg-primary text-white rounded-lg font-medium"
              >
                Close
              </button>
              <button
                v-if="selectedSale.status === 'completed' && !selectedSale.is_pending"
                @click="showVoidConfirm = true"
                class="w-full py-3 btn-danger rounded-lg font-medium"
              >
                <i class="fas fa-ban mr-2"></i>Void Sale
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Void Confirmation Modal -->
    <Transition name="fade">
      <div v-if="showVoidConfirm" class="modal-backdrop" style="z-index: 60;">
        <div class="absolute inset-0 bg-overlay" @click="showVoidConfirm = false"></div>
        <div class="modal-content">
          <div class="text-center">
            <div class="modal-icon modal-icon-danger">
              <i class="fas fa-ban text-xl"></i>
            </div>
            <h3 class="modal-title">Void Sale?</h3>
            <p class="modal-text">
              This will cancel receipt <strong>{{ selectedSale?.receipt_number }}</strong> and restore stock. This action cannot be undone.
            </p>
            <div v-if="voidError" class="text-sm text-danger mb-3">{{ voidError }}</div>
            <div class="modal-actions">
              <button @click="showVoidConfirm = false" class="btn btn-secondary" :disabled="voidingInProgress">
                Cancel
              </button>
              <button @click="voidSale" class="btn btn-danger" :disabled="voidingInProgress">
                {{ voidingInProgress ? 'Voiding...' : 'Yes, Void' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { fetchWrapper } from '@/helpers';
import { useSyncStore } from '@/stores/sync.store';
import { isTempReceipt } from '@/utils/receipt-generator';

const baseUrl = import.meta.env.VITE_API_URL;
const syncStore = useSyncStore();

const loading = ref(true);
const sales = ref([]);
const summary = ref({ total_sales: 0, total_revenue: 0 });
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedSale = ref(null);
const searchQuery = ref('');
const showVoidConfirm = ref(false);
const voidingInProgress = ref(false);
const voidError = ref('');

const filteredSales = computed(() => {
  if (!searchQuery.value.trim()) {
    return sales.value;
  }
  const query = searchQuery.value.toLowerCase().trim();
  return sales.value.filter(sale =>
    sale.receipt_number?.toLowerCase().includes(query)
  );
});

async function fetchSales() {
  loading.value = true;
  try {
    const [salesRes, summaryRes] = await Promise.all([
      fetchWrapper.get(`${baseUrl}/pos/sales?date=${selectedDate.value}`),
      fetchWrapper.get(`${baseUrl}/pos/sales/summary?date=${selectedDate.value}`)
    ]);

    const onlineSales = salesRes.data || [];

    // Get pending sales from sync store (only for today)
    const today = new Date().toISOString().split('T')[0];
    let pendingSales = [];

    if (selectedDate.value === today) {
      await syncStore.getPendingSales();
      pendingSales = syncStore.pendingSales.map(sale => ({
        id: sale.temp_receipt_number,
        receipt_number: sale.temp_receipt_number,
        created_at: sale.created_at,
        total_amount: sale.total_amount,
        payment_method: sale.payment_method,
        status: 'pending_sync',
        items: sale.items || [],
        subtotal: sale.subtotal,
        tax_amount: sale.tax_amount,
        discount_amount: sale.discount_amount,
        amount_paid: sale.amount_paid,
        change_amount: sale.change_amount,
        is_pending: true
      }));
    }

    // Merge pending sales at the top
    sales.value = [...pendingSales, ...onlineSales];

    // Update summary to include pending sales
    const pendingRevenue = pendingSales.reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
    summary.value = {
      total_sales: (summaryRes.stats?.total_sales || 0) + pendingSales.length,
      total_revenue: (summaryRes.stats?.total_revenue || 0) + pendingRevenue
    };
  } catch (error) {
    console.error('Failed to fetch sales:', error);
    // If API fails, still try to show pending sales
    const today = new Date().toISOString().split('T')[0];
    if (selectedDate.value === today) {
      await syncStore.getPendingSales();
      const pendingSales = syncStore.pendingSales.map(sale => ({
        id: sale.temp_receipt_number,
        receipt_number: sale.temp_receipt_number,
        created_at: sale.created_at,
        total_amount: sale.total_amount,
        payment_method: sale.payment_method,
        status: 'pending_sync',
        items: sale.items || [],
        is_pending: true
      }));
      sales.value = pendingSales;
      const pendingRevenue = pendingSales.reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);
      summary.value = {
        total_sales: pendingSales.length,
        total_revenue: pendingRevenue
      };
    }
  } finally {
    loading.value = false;
  }
}

async function viewSale(sale) {
  try {
    // If it's a pending sale, use the data we already have
    if (sale.is_pending || sale.status === 'pending_sync') {
      selectedSale.value = sale;
    } else {
      const response = await fetchWrapper.get(`${baseUrl}/pos/sales/${sale.id}`);
      selectedSale.value = response.sale;
    }
  } catch (error) {
    console.error('Failed to fetch sale details:', error);
  }
}

async function voidSale() {
  if (!selectedSale.value) return;
  voidingInProgress.value = true;
  voidError.value = '';
  try {
    await fetchWrapper.post(`${baseUrl}/pos/sales/${selectedSale.value.id}/cancel`);
    // Update the sale in the local list
    const idx = sales.value.findIndex(s => s.id === selectedSale.value.id);
    if (idx !== -1) {
      sales.value[idx].status = 'cancelled';
    }
    selectedSale.value.status = 'cancelled';
    showVoidConfirm.value = false;
    selectedSale.value = null;
    // Refresh summary
    try {
      const summaryRes = await fetchWrapper.get(`${baseUrl}/pos/sales/summary?date=${selectedDate.value}`);
      summary.value = {
        total_sales: summaryRes.stats?.total_sales || 0,
        total_revenue: summaryRes.stats?.total_revenue || 0
      };
    } catch (e) {
      // Summary refresh failed, not critical
    }
  } catch (error) {
    voidError.value = error?.message || 'Failed to void sale. You may not have permission.';
  } finally {
    voidingInProgress.value = false;
  }
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

function formatTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
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
</style>

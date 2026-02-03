<template>
  <div class="min-h-screen bg-page pb-20">
    <!-- Header -->
    <div class="bg-header text-white px-4 py-6 pt-safe">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h1 class="text-xl font-bold mt-4">Welcome back,</h1>
          <p class="text-primary-light">{{ user?.name || 'Cashier' }}</p>
        </div>
        <button @click="showLogoutModal = true" class="p-2 rounded-lg bg-overlay-light">
          <i class="fas fa-sign-out-alt"></i>
        </button>
      </div>
      <div class="rounded-lg p-2.5 bg-overlay-light">
        <div class="flex items-center">
          <i class="fas fa-store mr-2 icon-primary-light"></i>
          <span class="text-sm">{{ branch?.name || 'No branch assigned' }}</span>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="px-4 mt-negative">
      <div class="card shadow-lg p-4">
        <h2 class="section-subtitle">Today's Summary</h2>
        <div class="flex">
          <div class="flex-1 stat-card mr-2">
            <p class="stat-value">{{ stats.today?.total_sales || 0 }}</p>
            <p class="stat-label">Sales</p>
          </div>
          <div class="flex-1 stat-card ml-2">
            <p class="stat-value">${{ formatMoney(stats.today?.total_revenue || 0) }}</p>
            <p class="stat-label">Revenue</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4 mt-6">
      <h2 class="section-title">Quick Actions</h2>
      <div class="flex flex-wrap">
        <div class="w-1/2 p-1">
          <router-link to="/pos/sell" class="card-action-primary">
            <i class="fas fa-cash-register text-3xl mb-2"></i>
            <span class="font-medium">New Sale</span>
          </router-link>
        </div>

        <div class="w-1/2 p-1">
          <router-link to="/pos/sales" class="card-action">
            <i class="fas fa-history text-3xl mb-2 icon-primary"></i>
            <span class="font-medium">Sales History</span>
          </router-link>
        </div>

        <div class="w-1/2 p-1">
          <router-link to="/pos/products" class="card-action">
            <i class="fas fa-boxes text-3xl mb-2 icon-primary"></i>
            <span class="font-medium">Products</span>
          </router-link>
        </div>

        <div class="w-1/2 p-1">
          <router-link to="/pos/profile" class="card-action">
            <i class="fas fa-user text-3xl mb-2 icon-primary"></i>
            <span class="font-medium">Profile</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Recent Sales -->
    <div class="px-4 mt-6">
      <h2 class="section-title">Recent Sales</h2>
      <div v-if="stats.recent_sales?.length">
        <div
          v-for="sale in stats.recent_sales"
          :key="sale.id"
          @click="viewSale(sale)"
          class="sale-item cursor-pointer hover:shadow-md transition"
        >
          <div>
            <p class="font-medium text-gray-800">{{ sale.receipt_number }}</p>
            <p class="text-xs text-gray-500">{{ formatTime(sale.created_at) }}</p>
          </div>
          <div class="text-right">
            <p class="font-bold text-primary-dark">${{ formatMoney(sale.total_amount) }}</p>
            <span
              :class="[
                'text-xs px-2 py-0.5 rounded-full',
                sale.status === 'pending_sync' ? 'status-pending' :
                sale.status === 'completed' ? 'status-success' :
                sale.status === 'cancelled' ? 'status-failed' :
                'bg-gray-100 text-gray-700'
              ]"
            >
              {{ sale.status === 'pending_sync' ? 'Pending' : sale.payment_method }}
            </span>
          </div>
        </div>
      </div>
      <div v-else class="card shadow p-6 text-center text-gray-500">
        <i class="fas fa-receipt text-4xl mb-2 icon-gray"></i>
        <p>No sales yet today</p>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <Transition name="fade">
      <div v-if="showLogoutModal" class="modal-backdrop">
        <div class="absolute inset-0 bg-overlay" @click="showLogoutModal = false"></div>
        <div class="modal-content">
          <div class="text-center">
            <div class="modal-icon modal-icon-danger">
              <i class="fas fa-sign-out-alt text-xl"></i>
            </div>
            <h3 class="modal-title">Confirm Logout</h3>
            <p class="modal-text">
              Are you sure you want to log out? Any unsaved work may be lost.
            </p>
            <div class="modal-actions">
              <button @click="showLogoutModal = false" class="btn btn-secondary">
                Cancel
              </button>
              <button @click="confirmLogout" class="btn btn-danger">
                Yes, Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

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

          <div v-if="saleLoading" class="flex items-center justify-center py-20">
            <i class="fas fa-spinner fa-spin text-3xl icon-primary"></i>
          </div>

          <div v-else class="p-4">
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

    <!-- Loading overlay -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin text-3xl icon-primary"></i>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores';
import { useSyncStore } from '@/stores/sync.store';
import { useNetworkStore } from '@/stores/network.store';
import { fetchWrapper } from '@/helpers';

const authStore = useAuthStore();
const syncStore = useSyncStore();
const networkStore = useNetworkStore();
const loading = ref(true);
const stats = ref({});
const showLogoutModal = ref(false);
const selectedSale = ref(null);
const saleLoading = ref(false);
const showVoidConfirm = ref(false);
const voidingInProgress = ref(false);
const voidError = ref('');

const baseUrl = import.meta.env.VITE_API_URL;

const user = computed(() => authStore.user);
const branch = computed(() => authStore.user?.branch);

async function fetchDashboard() {
  loading.value = true;

  // Always load pending sales from local storage first
  await syncStore.getPendingSales();
  const pendingSales = syncStore.pendingSales.map(sale => ({
    id: sale.temp_receipt_number,
    receipt_number: sale.temp_receipt_number,
    created_at: sale.created_at,
    total_amount: sale.total_amount,
    payment_method: sale.payment_method,
    status: 'pending_sync',
    is_pending: true
  }));
  const pendingRevenue = pendingSales.reduce((sum, s) => sum + parseFloat(s.total_amount || 0), 0);

  // Check if user is logged in and online before making API call
  if (!authStore.token) {
    console.log('Not logged in, showing only local data');
    stats.value = {
      today: {
        total_sales: pendingSales.length,
        total_revenue: pendingRevenue
      },
      recent_sales: pendingSales.slice(0, 5)
    };
    loading.value = false;
    return;
  }

  // If offline, show only local data
  if (!networkStore.isOnline) {
    console.log('Offline mode: showing local data only');
    stats.value = {
      today: {
        total_sales: pendingSales.length,
        total_revenue: pendingRevenue
      },
      recent_sales: pendingSales.slice(0, 5)
    };
    loading.value = false;
    return;
  }

  // Online and authenticated - fetch from API
  try {
    const response = await fetchWrapper.get(`${baseUrl}/pos/dashboard`);

    // Update stats to include pending sales
    stats.value = {
      today: {
        total_sales: (response.today?.total_sales || 0) + pendingSales.length,
        total_revenue: (response.today?.total_revenue || 0) + pendingRevenue
      },
      // Merge pending sales at the top of recent sales
      recent_sales: [
        ...pendingSales,
        ...(response.recent_sales || [])
      ].slice(0, 5) // Limit to 5 most recent
    };
  } catch (error) {
    // API call failed - this is expected when offline/no auth
    // Show pending sales only (graceful fallback)
    stats.value = {
      today: {
        total_sales: pendingSales.length,
        total_revenue: pendingRevenue
      },
      recent_sales: pendingSales.slice(0, 5)
    };
  } finally {
    loading.value = false;
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

async function viewSale(sale) {
  selectedSale.value = sale;
  // If it's a pending sale (local), use what we have
  if (sale.is_pending || sale.status === 'pending_sync') {
    return;
  }
  // Fetch full details from API
  saleLoading.value = true;
  try {
    const response = await fetchWrapper.get(`${baseUrl}/pos/sales/${sale.id}`);
    selectedSale.value = response.sale;
  } catch (error) {
    console.error('Failed to fetch sale details:', error);
  } finally {
    saleLoading.value = false;
  }
}

async function voidSale() {
  if (!selectedSale.value) return;
  voidingInProgress.value = true;
  voidError.value = '';
  try {
    await fetchWrapper.post(`${baseUrl}/pos/sales/${selectedSale.value.id}/cancel`);
    // Update the sale in recent_sales
    const idx = stats.value.recent_sales?.findIndex(s => s.id === selectedSale.value.id);
    if (idx !== -1 && stats.value.recent_sales) {
      stats.value.recent_sales[idx].status = 'cancelled';
    }
    showVoidConfirm.value = false;
    selectedSale.value = null;
    // Refresh dashboard to update stats
    await fetchDashboard();
  } catch (error) {
    voidError.value = error?.message || 'Failed to void sale. You may not have permission.';
  } finally {
    voidingInProgress.value = false;
  }
}

async function confirmLogout() {
  showLogoutModal.value = false;
  await authStore.logout();
}

onMounted(() => {
  fetchDashboard();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

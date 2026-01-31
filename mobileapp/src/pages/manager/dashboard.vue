<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Page Header -->
    <div class="mb-4 md:mb-8">
      <div class="flex items-start md:items-center justify-between gap-2">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-xs md:text-sm text-gray-600 mt-1">Welcome back! Here's what's happening today</p>
        </div>
        <button
          @click="fetchDashboard"
          class="px-2 md:px-4 py-1.5 md:py-2 bg-white rounded-lg hover:shadow-md transition-all duration-200 flex items-center gap-1 md:gap-2 text-gray-700 hover:text-primary flex-shrink-0"
        >
          <i class="fas fa-sync-alt text-xs md:text-sm" :class="{ 'fa-spin': loading }"></i>
          <span class="text-xs md:text-sm font-medium hidden md:inline">Refresh</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !stats.today" class="flex items-center justify-center py-24">
      <div class="text-center">
        <i class="fas fa-spinner fa-spin text-5xl text-primary mb-4"></i>
        <p class="text-gray-600">Loading dashboard...</p>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="space-y-3 md:space-y-6">
      <!-- Today's Key Metrics -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <!-- Total Sales Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex-1 min-w-0">
              <p class="text-gray-500 text-xs font-medium mb-1">Total Sales</p>
              <p class="text-primary text-xl md:text-2xl font-bold truncate">{{ stats.today?.total_sales || 0 }}</p>
            </div>
            <div class="bg-primary-light rounded-lg p-2 flex-shrink-0">
              <i class="fas fa-shopping-cart text-primary text-base md:text-lg"></i>
            </div>
          </div>
          <p class="text-gray-600 text-xs">Today's transactions</p>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex-1 min-w-0">
              <p class="text-gray-500 text-xs font-medium mb-1">Revenue</p>
              <p class="text-success text-xl md:text-2xl font-bold truncate">${{ formatMoney(stats.today?.total_revenue || 0) }}</p>
            </div>
            <div class="bg-success-light rounded-lg p-2 flex-shrink-0">
              <i class="fas fa-dollar-sign text-success text-base md:text-lg"></i>
            </div>
          </div>
          <p class="text-gray-600 text-xs">Today's earnings</p>
        </div>

        <!-- Average Sale Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex-1 min-w-0">
              <p class="text-gray-500 text-xs font-medium mb-1">Avg. Sale</p>
              <p class="text-accent text-xl md:text-2xl font-bold truncate">${{ formatMoney(stats.today?.average_sale || 0) }}</p>
            </div>
            <div class="bg-accent-light rounded-lg p-2 flex-shrink-0">
              <i class="fas fa-chart-line text-accent text-base md:text-lg"></i>
            </div>
          </div>
          <p class="text-gray-600 text-xs">Per transaction</p>
        </div>

        <!-- Active Cashiers Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 md:p-5 hover:shadow-md transition-shadow duration-200">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex-1 min-w-0">
              <p class="text-gray-500 text-xs font-medium mb-1">Active Cashiers</p>
              <p class="text-primary-dark text-xl md:text-2xl font-bold truncate">{{ stats.active_cashiers || 0 }}</p>
            </div>
            <div class="bg-primary-light rounded-lg p-2 flex-shrink-0">
              <i class="fas fa-users text-primary-dark text-base md:text-lg"></i>
            </div>
          </div>
          <p class="text-gray-600 text-xs">On duty now</p>
        </div>
      </div>

      <!-- Period Comparison -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 md:gap-6">
        <!-- Week Summary -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-100">
          <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-6">
            <div class="bg-primary-light rounded-lg p-2 md:p-3 flex-shrink-0">
              <i class="fas fa-calendar-week text-primary text-lg md:text-xl"></i>
            </div>
            <div>
              <h3 class="text-base md:text-lg font-semibold text-gray-800">This Week</h3>
              <p class="text-xs md:text-sm text-gray-500">Last 7 days performance</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2 md:gap-4">
            <div class="bg-gray-50 rounded-lg p-3 md:p-4">
              <p class="text-xs md:text-sm text-gray-600 mb-1">Sales</p>
              <p class="text-2xl md:text-3xl font-bold text-gray-900">{{ stats.week?.total_sales || 0 }}</p>
            </div>
            <div class="bg-success-light rounded-lg p-3 md:p-4">
              <p class="text-xs md:text-sm text-gray-600 mb-1">Revenue</p>
              <p class="text-2xl md:text-3xl font-bold text-success">${{ formatMoney(stats.week?.total_revenue || 0) }}</p>
            </div>
          </div>
        </div>

        <!-- Month Summary -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-100">
          <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-6">
            <div class="bg-primary-light rounded-lg p-2 md:p-3 flex-shrink-0">
              <i class="fas fa-calendar-alt text-primary text-lg md:text-xl"></i>
            </div>
            <div>
              <h3 class="text-base md:text-lg font-semibold text-gray-800">This Month</h3>
              <p class="text-xs md:text-sm text-gray-500">Monthly performance</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2 md:gap-4">
            <div class="bg-gray-50 rounded-lg p-3 md:p-4">
              <p class="text-xs md:text-sm text-gray-600 mb-1">Sales</p>
              <p class="text-2xl md:text-3xl font-bold text-gray-900">{{ stats.month?.total_sales || 0 }}</p>
            </div>
            <div class="bg-success-light rounded-lg p-3 md:p-4">
              <p class="text-xs md:text-sm text-gray-600 mb-1">Revenue</p>
              <p class="text-2xl md:text-3xl font-bold text-success">${{ formatMoney(stats.month?.total_revenue || 0) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-6">
        <!-- Cashier Leaderboard -->
        <div class="lg:col-span-2 bg-white rounded-lg p-4 md:p-6 border border-gray-100">
          <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-6">
            <div>
              <h3 class="text-base md:text-lg font-semibold text-gray-800">Top Performers</h3>
              <p class="text-xs md:text-sm text-gray-500">Today's leaderboard</p>
            </div>
          </div>

          <div v-if="stats.cashier_performance && stats.cashier_performance.length > 0" class="space-y-2 md:space-y-3">
            <div
              v-for="(cashier, index) in stats.cashier_performance.slice(0, 5)"
              :key="index"
              class="flex items-center gap-2 md:gap-4 p-2 md:p-4 bg-gradient-to-r hover:from-primary-light hover:to-transparent rounded-lg transition-all duration-200 border border-gray-100"
            >
              <!-- Rank Badge -->
              <div class="flex-shrink-0">
                <div
                  class="w-8 h-8 md:w-12 md:h-12 rounded-lg flex items-center justify-center font-bold text-sm md:text-lg"
                  :class="{
                    'bg-primary text-white': index === 0,
                    'bg-success text-white': index === 1,
                    'bg-accent text-white': index === 2,
                    'bg-gray-100 text-gray-600': index > 2,
                  }"
                >
                  {{ index + 1 }}
                </div>
              </div>

              <!-- Cashier Info -->
              <div class="flex-1 min-w-0">
                <p class="text-xs md:text-sm font-semibold text-gray-900 truncate">{{ cashier.cashier_name }}</p>
                <p class="text-xs text-gray-500">{{ cashier.sales_count }} transactions</p>
              </div>

              <!-- Revenue -->
              <div class="text-right flex-shrink-0">
                <p class="text-sm md:text-xl font-bold text-success">${{ formatMoney(cashier.total_amount) }}</p>
                <p class="text-xs text-gray-500">revenue</p>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-6 md:py-8">
            <i class="fas fa-users text-3xl md:text-4xl text-gray-300 mb-2 md:mb-3"></i>
            <p class="text-xs md:text-sm text-gray-500">No cashier activity yet</p>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200">
          <div class="flex items-center gap-2 md:gap-3 mb-3 md:mb-6">
            <div class="bg-primary-light rounded-lg p-2 md:p-3 flex-shrink-0">
              <i class="fas fa-clock text-primary text-lg md:text-xl"></i>
            </div>
            <div>
              <h3 class="text-base md:text-lg font-semibold text-gray-800">Recent Sales</h3>
              <p class="text-xs md:text-sm text-gray-500">Latest transactions</p>
            </div>
          </div>

          <div v-if="stats.recent_sales && stats.recent_sales.length > 0" class="space-y-2 md:space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
            <div
              v-for="sale in stats.recent_sales.slice(0, 10)"
              :key="sale.id"
              class="p-2 md:p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150 border border-gray-100"
            >
              <div class="flex items-start justify-between mb-1 md:mb-2">
                <div class="flex-1 min-w-0">
                  <p class="text-xs md:text-sm font-semibold text-gray-900 truncate">{{ sale.receipt_number }}</p>
                  <p class="text-xs text-gray-500 mt-0.5 truncate">{{ sale.user?.name || 'Unknown' }}</p>
                </div>
                <span class="text-xs md:text-sm font-bold text-success flex-shrink-0 ml-1">${{ formatMoney(sale.total_amount) }}</span>
              </div>
              <div class="flex items-center justify-between text-xs gap-1">
                <span class="text-gray-500">{{ formatTime(sale.created_at) }}</span>
                <span class="px-1.5 py-0.5 bg-success-light text-success rounded text-xs capitalize whitespace-nowrap">{{ sale.payment_method }}</span>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-6 md:py-8">
            <i class="fas fa-receipt text-3xl md:text-4xl text-gray-300 mb-2 md:mb-3"></i>
            <p class="text-xs md:text-sm text-gray-500">No recent sales</p>
          </div>
        </div>
      </div>

      <!-- Quick Stats Bar -->
      <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
          <div class="text-center">
            <div class="bg-primary-light rounded-full w-12 h-12 md:w-16 md:h-16 flex items-center justify-center mx-auto mb-2 md:mb-3">
              <i class="fas fa-cash-register text-primary text-lg md:text-2xl"></i>
            </div>
            <p class="text-lg md:text-2xl font-bold text-gray-900">{{ stats.today?.total_sales || 0 }}</p>
            <p class="text-xs md:text-sm text-gray-500">Transactions</p>
          </div>
          <div class="text-center">
            <div class="bg-success-light rounded-full w-12 h-12 md:w-16 md:h-16 flex items-center justify-center mx-auto mb-2 md:mb-3">
              <i class="fas fa-money-bill-wave text-success text-lg md:text-2xl"></i>
            </div>
            <p class="text-lg md:text-2xl font-bold text-gray-900">${{ formatMoney(stats.today?.total_revenue || 0) }}</p>
            <p class="text-xs md:text-sm text-gray-500">Today's Total</p>
          </div>
          <div class="text-center">
            <div class="bg-accent-light rounded-full w-12 h-12 md:w-16 md:h-16 flex items-center justify-center mx-auto mb-2 md:mb-3">
              <i class="fas fa-percentage text-accent text-lg md:text-2xl"></i>
            </div>
            <p class="text-lg md:text-2xl font-bold text-gray-900">${{ formatMoney(stats.today?.average_sale || 0) }}</p>
            <p class="text-xs md:text-sm text-gray-500">Avg Value</p>
          </div>
          <div class="text-center">
            <div class="bg-primary-light rounded-full w-12 h-12 md:w-16 md:h-16 flex items-center justify-center mx-auto mb-2 md:mb-3">
              <i class="fas fa-user-check text-primary text-lg md:text-2xl"></i>
            </div>
            <p class="text-lg md:text-2xl font-bold text-gray-900">{{ stats.active_cashiers || 0 }}</p>
            <p class="text-xs md:text-sm text-gray-500">Active Staff</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { fetchWrapper } from '@/helpers';

const loading = ref(true);
const stats = ref({});

const baseUrl = import.meta.env.VITE_API_URL;

async function fetchDashboard() {
  loading.value = true;

  try {
    const response = await fetchWrapper.get(`${baseUrl}/manager/dashboard`);
    stats.value = response;
  } catch (error) {
    console.error('Failed to fetch dashboard:', error);
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

onMounted(() => {
  fetchDashboard();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: var(--bg-gray-50, #F4F6F8);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--bg-primary, #1669C5);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: var(--bg-primary-dark, #1254A3);
}
</style>

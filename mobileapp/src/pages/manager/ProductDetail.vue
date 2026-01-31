<template>
  <div class="space-y-6">
    <!-- Back Button & Header -->
    <div class="flex items-center gap-4">
      <button
        @click="$router.back()"
        class="p-2 rounded-lg hover:bg-gray-100 transition"
      >
        <i class="fas fa-arrow-left text-gray-600"></i>
      </button>
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Product Details</h1>
        <p class="text-gray-600">View product information and sales statistics</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <i class="fas fa-spinner fa-spin text-4xl text-green-600"></i>
    </div>

    <!-- Product Not Found -->
    <div v-else-if="!product" class="text-center py-20">
      <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
      <p class="text-gray-500">Product not found</p>
    </div>

    <!-- Product Details -->
    <template v-else>
      <!-- Product Info Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex items-start gap-6">
          <!-- Product Image -->
          <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
            <img
              v-if="product.image"
              :src="getImageUrl(product.image)"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <i v-else class="fas fa-box text-gray-400 text-3xl"></i>
          </div>

          <!-- Product Info -->
          <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800">{{ product.name }}</h2>
            <p class="text-sm text-gray-500 mt-1">SKU: {{ product.sku || 'N/A' }}</p>
            <p v-if="product.barcode" class="text-sm text-gray-500">Barcode: {{ product.barcode }}</p>
            <div class="flex items-center gap-4 mt-3">
              <span class="text-2xl font-bold text-green-600">${{ formatMoney(product.selling_price) }}</span>
              <span class="text-sm text-gray-500">Cost: ${{ formatMoney(product.cost_price) }}</span>
            </div>
          </div>
        </div>

        <!-- Product Meta -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
          <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Category</p>
            <p class="text-sm font-semibold text-gray-800 mt-1">{{ product.category?.name || 'Uncategorized' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Unit</p>
            <p class="text-sm font-semibold text-gray-800 mt-1">{{ product.unit || 'Piece' }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Tax Rate</p>
            <p class="text-sm font-semibold text-gray-800 mt-1">{{ product.tax_rate || 0 }}%</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase font-medium">Status</p>
            <span
              class="inline-block px-2 py-1 text-xs font-medium rounded-full mt-1"
              :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ product.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Stock & Sales Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 mb-1">Current Stock</p>
              <p class="text-3xl font-bold text-gray-900">{{ Math.floor(stats.current_stock || 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-warehouse text-blue-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 mb-1">Total Sold</p>
              <p class="text-3xl font-bold text-green-600">{{ Math.floor(stats.total_sold || 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
              <p class="text-3xl font-bold text-green-600">${{ formatMoney(stats.total_revenue || 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500 mb-1">Total Profit</p>
              <p class="text-3xl font-bold" :class="stats.total_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                ${{ formatMoney(stats.total_profit || 0) }}
              </p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-line text-amber-600 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Sales Period Filter -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-sm text-gray-500 font-medium">Period:</span>
          <button
            v-for="period in periods"
            :key="period.value"
            @click="changePeriod(period.value)"
            class="px-4 py-2 text-sm font-medium rounded-lg transition"
            :class="selectedPeriod === period.value
              ? 'bg-green-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            {{ period.label }}
          </button>
        </div>
      </div>

      <!-- Recent Sales -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
          <h3 class="text-lg font-semibold text-gray-800">Recent Sales</h3>
          <p class="text-sm text-gray-500">Sales transactions for this product</p>
        </div>

        <div v-if="recentSales.length === 0" class="p-12 text-center">
          <i class="fas fa-receipt text-4xl text-gray-300 mb-3"></i>
          <p class="text-gray-500">No sales found for this period</p>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div
            v-for="sale in recentSales"
            :key="sale.id"
            class="p-4 hover:bg-gray-50 transition"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-800">{{ sale.receipt_number }}</p>
                <p class="text-sm text-gray-500">{{ formatDateTime(sale.created_at) }}</p>
                <p class="text-xs text-gray-400">Cashier: {{ sale.cashier_name }}</p>
              </div>
              <div class="text-right">
                <p class="font-bold text-gray-900">{{ sale.quantity }} {{ product.unit }}</p>
                <p class="text-sm text-green-600">${{ formatMoney(sale.line_total) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Load More -->
        <div v-if="hasMoreSales" class="p-4 border-t border-gray-100">
          <button
            @click="loadMoreSales"
            :disabled="loadingSales"
            class="w-full py-2 text-sm font-medium text-green-600 hover:bg-green-50 rounded-lg transition"
          >
            <i v-if="loadingSales" class="fas fa-spinner fa-spin mr-2"></i>
            {{ loadingSales ? 'Loading...' : 'Load More' }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { fetchWrapper } from '@/helpers';

const route = useRoute();
const baseUrl = import.meta.env.VITE_API_URL;

const loading = ref(true);
const product = ref(null);
const stats = ref({});
const recentSales = ref([]);
const loadingSales = ref(false);
const hasMoreSales = ref(false);
const salesPage = ref(1);
const selectedPeriod = ref('30');

const periods = [
  { value: '7', label: '7 Days' },
  { value: '30', label: '30 Days' },
  { value: '90', label: '90 Days' },
  { value: 'all', label: 'All Time' }
];

async function fetchProduct() {
  loading.value = true;

  try {
    const productId = route.params.id;
    const response = await fetchWrapper.get(`${baseUrl}/manager/products/${productId}?period=${selectedPeriod.value}`);

    product.value = response.product;
    stats.value = response.stats || {};
    recentSales.value = response.recent_sales || [];
    hasMoreSales.value = response.has_more_sales || false;
    salesPage.value = 1;
  } catch (error) {
    console.error('Failed to fetch product:', error);
    product.value = null;
  } finally {
    loading.value = false;
  }
}

async function loadMoreSales() {
  if (loadingSales.value) return;

  loadingSales.value = true;
  salesPage.value++;

  try {
    const productId = route.params.id;
    const response = await fetchWrapper.get(
      `${baseUrl}/manager/products/${productId}/sales?page=${salesPage.value}&period=${selectedPeriod.value}`
    );

    recentSales.value = [...recentSales.value, ...(response.sales || [])];
    hasMoreSales.value = response.has_more || false;
  } catch (error) {
    console.error('Failed to load more sales:', error);
  } finally {
    loadingSales.value = false;
  }
}

function changePeriod(period) {
  selectedPeriod.value = period;
  fetchProduct();
}

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  const backendUrl = import.meta.env.VITE_API_URL.replace('/api', '');
  return `${backendUrl}/storage/${path}`;
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

onMounted(() => {
  fetchProduct();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-header text-white px-4 py-3 flex items-center sticky top-0 z-40 pt-safe">
      <router-link to="/pos/dashboard" class="p-1 mr-3">
        <i class="fas fa-arrow-left"></i>
      </router-link>
      <h1 class="text-lg font-bold">Products</h1>
    </div>

    <!-- Search -->
    <div class="px-4 py-3 bg-white shadow-sm">
      <div class="relative">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
          class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-0 rounded-lg focus:ring-2 focus:ring-primary outline-none"
        />
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
      </div>
    </div>

    <!-- Categories Filter -->
    <div class="px-4 py-2 bg-white border-b overflow-x-auto">
      <div class="flex">
        <button
          @click="selectedCategory = null"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition mr-2',
            !selectedCategory ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'
          ]"
        >
          All
        </button>
        <button
          v-for="category in categories"
          :key="category.id"
          @click="selectedCategory = category.id"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition mr-2',
            selectedCategory === category.id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'
          ]"
        >
          {{ category.name }}
        </button>
      </div>
    </div>

    <!-- Products List -->
    <div class="p-4">
      <div v-if="loading" class="flex items-center justify-center py-20">
        <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
      </div>

      <div v-else-if="filteredProducts.length === 0" class="text-center py-20 text-gray-500">
        <i class="fas fa-box-open text-5xl mb-3 text-gray-300"></i>
        <p>No products found</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          class="bg-white rounded-lg shadow p-4 hover:shadow-md transition"
        >
          <div class="flex">
            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 p-2 mr-3">
              <img
                v-if="getImageUrl(product.id)"
                :src="getImageUrl(product.id)"
                :alt="product.name"
                class="w-full h-full object-contain"
                @error="handleImageError"
                :data-product-id="product.id"
              />
              <i v-if="!getImageUrl(product.id) || imageErrors[product.id]" class="fas fa-box text-3xl text-gray-300"></i>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-gray-800 mb-1 truncate">{{ product.name }}</h3>
              <p class="text-xs text-gray-500 mb-2">{{ product.category?.name || 'Uncategorized' }}</p>

              <div class="flex items-center justify-between">
                <span class="text-lg font-bold text-success">${{ formatMoney(product.selling_price) }}</span>
                <span
                  v-if="product.track_stock"
                  :class="[
                    'text-xs px-2.5 py-1 rounded-full font-medium',
                    product.stock_quantity > 10 ? 'status-success' :
                    product.stock_quantity > 0 ? 'status-pending' :
                    'status-failed'
                  ]"
                >
                  <i class="fas fa-box mr-1"></i>{{ Math.floor(product.stock_quantity) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { fetchWrapper } from '@/helpers';
import { useOfflineStore } from '@/stores/offline.store';
import { useNetworkStore } from '@/stores/network.store';
import { useAlertStore } from '@/stores/alert.store';
import { useProductImage } from '@/composables/useProductImage';

const offlineStore = useOfflineStore();
const networkStore = useNetworkStore();
const alertStore = useAlertStore();
const { getProductImageUrl } = useProductImage();
const baseUrl = import.meta.env.VITE_API_URL;

function getImageUrl(productId) {
  return productImageUrls.value[productId] || null;
}

async function loadProductImages(productsList) {
  for (const product of productsList) {
    if (product.image && product.id) {
      try {
        const imageUrl = await getProductImageUrl(product.image);
        if (imageUrl) {
          productImageUrls.value[product.id] = imageUrl;
        }
      } catch (error) {
        // Silently fail
      }
    }
  }
}

function handleImageError(e) {
  const productId = e.target.getAttribute('data-product-id');
  if (productId) {
    imageErrors.value[productId] = true;
  }
}

const loading = ref(true);
const products = ref([]);
const categories = ref([]);
const productImageUrls = ref({});
const imageErrors = ref({});
const searchQuery = ref('');
const selectedCategory = ref(null);

const filteredProducts = computed(() => {
  let filtered = products.value;

  if (selectedCategory.value) {
    filtered = filtered.filter(p => p.category_id === selectedCategory.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.sku?.toLowerCase().includes(query) ||
      p.barcode?.toString().includes(query)
    );
  }

  return filtered;
});

async function fetchData() {
  loading.value = true;
  try {
    // Use offline store - it handles online/offline automatically
    const [productsData, categoriesData] = await Promise.all([
      offlineStore.getProducts(),
      offlineStore.getCategories()
    ]);

    products.value = productsData || [];
    categories.value = categoriesData || [];

    // Load product images
    await loadProductImages(products.value);

    if (products.value.length === 0 && !networkStore.isOnline) {
      alertStore.warning('You are offline. No cached products available. Please connect to internet.');
    }
  } catch (error) {
    console.error('Failed to fetch data:', error);
  } finally {
    loading.value = false;
  }
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

onMounted(() => {
  fetchData();
});
</script>

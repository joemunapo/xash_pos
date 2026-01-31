<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6">
        <div class="flex items-center gap-4 mb-4">
          <button @click="goBack" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fa-solid fa-arrow-left text-white text-lg"></i>
          </button>
          <div class="mb-5">
            <h1 class="text-white text-xl font-semibold">Buy Airtime</h1>
            <p class="text-white/60 text-sm">Instant airtime top-up</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-12  relative z-20">
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <form @submit.prevent="handlePurchase">
          <!-- General Error Message -->
          <div v-if="generalError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-circle-exclamation text-red-500"></i>
              <p class="text-sm text-red-700">{{ generalError }}</p>
            </div>
          </div>

          <!-- Country Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Select Country
            </label>
            <div class="relative">
              <select
                v-model="selectedCountry"
                @change="onCountryChange"
                class="w-full px-4 py-3 pr-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none appearance-none"
                :class="{
                  'border-red-500': errors.country,
                  'text-gray-400': !selectedCountry,
                  'text-[var(--fluxr-dark)]': selectedCountry
                }"
              >
                <option value="" disabled hidden>Select Country</option>
                <option v-for="country in countries" :key="country.code" :value="country.code" class="text-[var(--fluxr-dark)]">
                  {{ country.flag }} {{ country.name }}
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-chevron-down text-gray-400 text-sm"></i>
              </div>
            </div>
            <p v-if="errors.country" class="mt-1 text-sm text-red-500">{{ errors.country }}</p>
          </div>

          <!-- Main Provider Selection -->
          <div v-if="selectedCountry" class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Service Provider
            </label>
            <div class="relative">
              <select
                v-model="selectedProvider"
                @change="onProviderChange"
                class="w-full px-4 py-3 pr-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none appearance-none"
                :class="{
                  'border-red-500': errors.provider,
                  'text-gray-400': !selectedProvider,
                  'text-[var(--fluxr-dark)]': selectedProvider
                }"
              >
                <option value="" disabled hidden>Select Provider</option>
                <option v-for="provider in filteredProviders" :key="provider.id" :value="provider.id" class="text-[var(--fluxr-dark)]">
                  {{ provider.name }}
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <i class="fa-solid fa-chevron-down text-gray-400 text-sm"></i>
              </div>
            </div>
            <p v-if="errors.provider" class="mt-1 text-sm text-red-500">{{ errors.provider }}</p>
          </div>

          <!-- Kazang Provider Selection (shown when Kazang is selected) -->
          <div v-if="selectedProvider === 'kazang'" class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-3">
              Select Network Provider
            </label>

            <!-- Loading State -->
            <div v-if="loadingProducts" class="flex items-center justify-center py-8">
              <i class="fa-solid fa-spinner fa-spin text-[var(--fluxr-green-dark)] text-2xl"></i>
              <span class="ml-2 text-gray-600">Loading providers...</span>
            </div>

            <!-- Provider Cards -->
            <div v-else-if="kazangProviders.length > 0" class="grid grid-cols-2 gap-3 mb-4">
              <button
                v-for="provider in kazangProviders"
                :key="provider.name"
                type="button"
                @click="selectKazangProvider(provider.name)"
                class="p-4 rounded-lg border-2 transition-all text-left"
                :class="{
                  'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': selectedKazangProvider === provider.name,
                  'border-gray-200 hover:border-[var(--fluxr-green-dark)]/50': selectedKazangProvider !== provider.name
                }"
              >
                <div class="font-semibold text-[var(--fluxr-dark)]">{{ provider.displayName }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ provider.count }} options</div>
              </button>
            </div>

            <!-- No Providers -->
            <div v-else class="text-center py-6 text-gray-500">
              <i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
              <p>No providers available</p>
            </div>

            <p v-if="errors.provider && selectedProvider === 'kazang'" class="mt-1 text-sm text-red-500">{{ errors.provider }}</p>
          </div>

          <!-- Kazang Product Selection (shown when provider is selected) -->
          <div v-if="selectedProvider === 'kazang' && selectedKazangProvider" class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-3">
              Select Airtime Product
            </label>

            <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto">
              <button
                v-for="product in filteredKazangProducts"
                :key="product.product_id"
                type="button"
                @click="selectKazangProduct(product)"
                class="p-3 rounded-lg border-2 transition-all text-left"
                :class="{
                  'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': selectedProduct === product.product_id,
                  'border-gray-200 hover:border-[var(--fluxr-green-dark)]/50': selectedProduct !== product.product_id
                }"
              >
                <div class="font-medium text-[var(--fluxr-dark)] text-sm">{{ product.product_description }}</div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ product.isPinless ? 'Any Amount' : `R${product.product_value}` }}
                </div>
              </button>
            </div>

            <p v-if="errors.product" class="mt-2 text-sm text-red-500">{{ errors.product }}</p>
          </div>

          <!-- Phone Number -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Phone Number
            </label>
            <input
              v-model="phoneNumber"
              type="tel"
              :placeholder="selectedProvider === 'kazang' ? '+27831234567' : '+263771234567'"
              @input="errors.phoneNumber = ''"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.phoneNumber }"
            />
            <p v-if="errors.phoneNumber" class="mt-1 text-sm text-red-500">{{ errors.phoneNumber }}</p>
          </div>

          <!-- Amount -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Amount ({{ selectedProvider === 'kazang' ? 'ZAR' : 'USD' }})
            </label>
            <input
              v-model="amount"
              type="number"
              step="0.01"
              placeholder="5.00"
              @input="errors.amount = ''"
              :readonly="selectedProductData && !selectedProductData.isPinless"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{
                'border-red-500': errors.amount,
                'bg-gray-100 cursor-not-allowed': selectedProductData && !selectedProductData.isPinless
              }"
            />
            <p v-if="selectedProductData && !selectedProductData.isPinless" class="mt-1 text-xs text-gray-500">
              Amount is fixed for this product
            </p>
            <p v-if="errors.amount" class="mt-1 text-sm text-red-500">{{ errors.amount }}</p>
          </div>

          <!-- Quick Amount Buttons (only for pinless products or non-kazang providers) -->
          <div v-if="!selectedProductData || selectedProductData.isPinless" class="mb-6">
            <p class="text-sm font-medium text-[var(--fluxr-dark)] mb-2">Quick Select</p>
            <div class="grid grid-cols-4 gap-2">
              <button
                v-for="quickAmount in (selectedProvider === 'kazang' ? [10, 20, 50, 100] : [1, 2, 5, 10])"
                :key="quickAmount"
                type="button"
                @click="amount = quickAmount; errors.amount = ''"
                class="py-2 px-3 rounded-lg border-2 border-gray-200 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/10 transition-all text-sm font-medium"
                :class="{ 'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': amount == quickAmount }"
              >
                {{ selectedProvider === 'kazang' ? 'R' : '$' }}{{ quickAmount }}
              </button>
            </div>
          </div>

          <!-- Purchase Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Processing...' : 'Purchase Airtime' }}
          </button>
        </form>
      </div>

      <!-- Success Modal -->
      <div v-if="showSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 max-w-sm w-full">
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fa-solid fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-[var(--fluxr-dark)] mb-2">Purchase Successful!</h3>
            <p class="text-gray-600 mb-6">Your airtime has been sent successfully</p>
            <button
              @click="resetForm"
              class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-lg transition-all"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchWrapper } from '@/helpers'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

// Countries data
const countries = ref([
  { code: 'ZW', name: 'Zimbabwe', flag: '🇿🇼', currency: 'USD' },
  { code: 'ZA', name: 'South Africa', flag: '🇿🇦', currency: 'ZAR' }
])

// All providers with country mapping
const providers = ref([
  { id: 'econet', name: 'Econet', code: 'ECONET', country: 'ZW', type: 'direct' },
  { id: 'netone', name: 'NetOne', code: 'NETONE', country: 'ZW', type: 'direct' },
  { id: 'kazang', name: 'Kazang Airtime', code: 'KAZANG', country: 'ZA', type: 'aggregator' }
])

const kazangProducts = ref([])
const kazangProviders = ref([])
const selectedCountry = ref('')
const selectedProvider = ref('')
const selectedKazangProvider = ref('')
const selectedProduct = ref('')
const selectedProductData = ref(null)
const phoneNumber = ref('')
const amount = ref('')
const loading = ref(false)
const loadingProducts = ref(false)
const showSuccess = ref(false)
const generalError = ref('')
const errors = ref({
  country: '',
  provider: '',
  product: '',
  phoneNumber: '',
  amount: ''
})

// Filter providers based on selected country
const filteredProviders = computed(() => {
  if (!selectedCountry.value) return []
  return providers.value.filter(p => p.country === selectedCountry.value)
})

// Filter Kazang products by selected provider
const filteredKazangProducts = computed(() => {
  if (!selectedKazangProvider.value) return []
  return kazangProducts.value.filter(p => p.product_provider === selectedKazangProvider.value)
})

const goBack = () => {
  router.back()
}

const onCountryChange = () => {
  errors.value.country = ''
  errors.value.provider = ''
  generalError.value = ''
  selectedProvider.value = ''
  selectedKazangProvider.value = ''
  selectedProduct.value = ''
  selectedProductData.value = null
}

const groupProductsByProvider = (products) => {
  const grouped = {}
  products.forEach(product => {
    const provider = product.product_provider
    if (!grouped[provider]) {
      grouped[provider] = []
    }
    // Add isPinless flag for easier handling
    product.isPinless = product.method_name === 'directRechargeAirtime'
    grouped[provider].push(product)
  })

  // Convert to array and create display names
  return Object.keys(grouped).map(providerName => {
    // Clean up provider name for display
    let displayName = providerName.replace(' SA', '').replace(' Pinless', '')

    return {
      name: providerName,
      displayName: displayName,
      count: grouped[providerName].length,
      products: grouped[providerName]
    }
  }).sort((a, b) => {
    // Sort pinless providers first, then by name
    const aPinless = a.name.includes('Pinless')
    const bPinless = b.name.includes('Pinless')
    if (aPinless && !bPinless) return -1
    if (!aPinless && bPinless) return 1
    return a.displayName.localeCompare(b.displayName)
  })
}

const loadKazangProducts = async () => {
  loadingProducts.value = true
  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/kazang/airtime/products`)
    kazangProducts.value = response.products || []
    kazangProviders.value = groupProductsByProvider(kazangProducts.value)
  } catch (error) {
    console.error('Failed to load Kazang products:', error)
    alertStore.error(error.message || 'Failed to load network providers')
  } finally {
    loadingProducts.value = false
  }
}

const onProviderChange = () => {
  errors.value.provider = ''
  generalError.value = ''
  selectedKazangProvider.value = ''
  selectedProduct.value = ''
  selectedProductData.value = null

  if (selectedProvider.value === 'kazang') {
    loadKazangProducts()
  }
}

const selectKazangProvider = (providerName) => {
  selectedKazangProvider.value = providerName
  selectedProduct.value = ''
  selectedProductData.value = null
  errors.value.product = ''
}

const selectKazangProduct = (product) => {
  selectedProduct.value = product.product_id
  selectedProductData.value = product
  errors.value.product = ''

  // For fixed-value products, pre-fill the amount
  if (!product.isPinless && product.product_value) {
    amount.value = parseFloat(product.product_value)
  }
}

const validateForm = () => {
  errors.value = {
    country: '',
    provider: '',
    product: '',
    phoneNumber: '',
    amount: ''
  }

  let isValid = true

  if (!selectedCountry.value) {
    errors.value.country = 'Please select a country'
    isValid = false
  }

  if (!selectedProvider.value) {
    errors.value.provider = 'Please select a provider'
    isValid = false
  }

  if (selectedProvider.value === 'kazang' && !selectedProduct.value) {
    errors.value.product = 'Please select a network'
    isValid = false
  }

  if (!phoneNumber.value) {
    errors.value.phoneNumber = 'Phone number is required'
    isValid = false
  } else {
    // Different validation for Kazang (SA numbers) vs others
    if (selectedProvider.value === 'kazang') {
      if (!/^\+27[0-9]{9}$/.test(phoneNumber.value)) {
        errors.value.phoneNumber = 'Enter a valid SA phone number (e.g. +27831234567)'
        isValid = false
      }
    } else {
      if (!/^\+[0-9]{10,15}$/.test(phoneNumber.value)) {
        errors.value.phoneNumber = 'Enter a valid phone number (e.g. +263771234567)'
        isValid = false
      }
    }
  }

  if (!amount.value) {
    errors.value.amount = 'Amount is required'
    isValid = false
  } else if (amount.value <= 0) {
    errors.value.amount = 'Amount must be greater than 0'
    isValid = false
  }

  return isValid
}

const handlePurchase = async () => {
  if (!validateForm()) return

  loading.value = true
  generalError.value = ''

  try {
    let response

    // Use separate API for NetOne
    if (selectedProvider.value === 'netone') {
      const payload = {
        phone_number: phoneNumber.value,
        amount: parseFloat(amount.value)
      }
      response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/netone/airtime/purchase`, payload)
    }
    // Econet (placeholder - needs backend implementation)
    else if (selectedProvider.value === 'econet') {
      // TODO: Implement Econet airtime purchase endpoint
      alertStore.error('Econet airtime purchase coming soon!')
      loading.value = false
      return
    }
    else if (selectedProvider.value === 'kazang') {
      // Use Kazang airtime API
      const payload = {
        product_id: parseInt(selectedProduct.value),
        target_phone_number: phoneNumber.value,
        amount: parseFloat(amount.value)
      }

      console.log('Kazang purchase payload:', payload)
      response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/kazang/airtime/purchase`, payload)
    }
    else {
      // Fallback for other providers
      alertStore.error('This provider is not yet supported')
      loading.value = false
      return
    }

    showSuccess.value = true
    alertStore.success(response.message || 'Airtime purchased successfully')
  } catch (error) {
    console.error('Purchase failed:', error)

    // Get error message
    const errorMessage = error.message || 'Purchase failed. Please try again.'

    // Show toast notification
    alertStore.error(errorMessage)

    // Set general error for inline display
    generalError.value = errorMessage

    // Handle field-specific validation errors from backend
    if (error.errors) {
      if (error.errors.provider) {
        errors.value.provider = error.errors.provider[0]
      }
      if (error.errors.product_id) {
        errors.value.product = error.errors.product_id[0]
      }
      if (error.errors.target_phone_number || error.errors.phone_number) {
        errors.value.phoneNumber = (error.errors.target_phone_number || error.errors.phone_number)[0]
      }
      if (error.errors.amount) {
        errors.value.amount = error.errors.amount[0]
      }
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  showSuccess.value = false
  selectedCountry.value = ''
  selectedProvider.value = ''
  selectedKazangProvider.value = ''
  selectedProduct.value = ''
  selectedProductData.value = null
  phoneNumber.value = ''
  amount.value = ''
  router.push('/dashboard')
}
</script>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>

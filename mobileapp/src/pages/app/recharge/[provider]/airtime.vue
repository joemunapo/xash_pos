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
            <h1 class="text-white text-xl font-semibold">{{ providerInfo.name }} Airtime</h1>
            <p class="text-white/60 text-sm">Top up your phone balance</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-12 relative z-20">
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <form @submit.prevent="handlePurchase">
          <!-- General Error Message -->
          <div v-if="generalError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-circle-exclamation text-red-500"></i>
              <p class="text-sm text-red-700">{{ generalError }}</p>
            </div>
          </div>

          <!-- Kazang Provider Selection -->
          <div v-if="providerId === 'kazang'" class="mb-4">
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

            <p v-if="errors.provider" class="mt-1 text-sm text-red-500">{{ errors.provider }}</p>
          </div>

          <!-- Kazang Product Selection (shown when provider is selected) -->
          <div v-if="providerId === 'kazang' && selectedKazangProvider" class="mb-4">
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
              :placeholder="providerInfo.placeholder"
              @input="onPhoneInput"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.phoneNumber }"
            />
            <p v-if="errors.phoneNumber" class="mt-1 text-sm text-red-500">{{ errors.phoneNumber }}</p>
          </div>

          <!-- Amount -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Amount ({{ providerId === 'kazang' ? 'ZAR' : 'USD' }})
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
                v-for="quickAmount in (providerId === 'kazang' ? [10, 20, 50, 100] : [1, 2, 5, 10])"
                :key="quickAmount"
                type="button"
                @click="amount = quickAmount; errors.amount = ''"
                class="py-2 px-3 rounded-lg border-2 border-gray-200 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/10 transition-all text-sm font-medium"
                :class="{ 'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': amount == quickAmount }"
              >
                {{ providerId === 'kazang' ? 'R' : '$' }}{{ quickAmount }}
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
import { useRouter, useRoute } from 'vue-router'
import { fetchWrapper } from '@/helpers'
import { useAlertStore } from '@/stores'

const router = useRouter()
const route = useRoute()
const alertStore = useAlertStore()

const providerId = computed(() => route.params.provider)

const providerData = {
  econet: {
    name: 'Econet',
    placeholder: '0771234567',
    prefix: '077'
  },
  kazang: {
    name: 'Kazang',
    placeholder: '+27831234567',
    prefix: '+27'
  }
}

const providerInfo = computed(() => {
  return providerData[providerId.value] || {
    name: 'Provider',
    placeholder: '0771234567',
    prefix: ''
  }
})

const kazangProducts = ref([])
const kazangProviders = ref([])
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
  provider: '',
  product: '',
  phoneNumber: '',
  amount: ''
})

// Filter Kazang products by selected provider
const filteredKazangProducts = computed(() => {
  if (!selectedKazangProvider.value) return []
  return kazangProducts.value.filter(p => p.product_provider === selectedKazangProvider.value)
})

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

const onPhoneInput = () => {
  // Remove spaces from phone number
  phoneNumber.value = phoneNumber.value.replace(/\s/g, '')
  errors.value.phoneNumber = ''
}

const goBack = () => {
  router.back()
}

const validateForm = () => {
  errors.value = {
    provider: '',
    product: '',
    phoneNumber: '',
    amount: ''
  }

  let isValid = true

  // Validate Kazang provider and product selection
  if (providerId.value === 'kazang') {
    if (!selectedKazangProvider.value) {
      errors.value.provider = 'Please select a network provider'
      isValid = false
    }
    if (!selectedProduct.value) {
      errors.value.product = 'Please select a product'
      isValid = false
    }
  }

  if (!phoneNumber.value) {
    errors.value.phoneNumber = 'Phone number is required'
    isValid = false
  } else {
    // Different validation for Kazang (South Africa)
    if (providerId.value === 'kazang') {
      if (!/^\+27[0-9]{9}$/.test(phoneNumber.value)) {
        errors.value.phoneNumber = 'Enter a valid SA phone number (e.g. +27831234567)'
        isValid = false
      }
    } else {
      // Zimbabwe numbers
      if (!/^(\+263|0)[0-9]{9}$/.test(phoneNumber.value)) {
        errors.value.phoneNumber = 'Enter a valid phone number (e.g. 0771234567)'
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

    // Econet - coming soon
    if (providerId.value === 'econet') {
      alertStore.error('Econet airtime purchase coming soon!')
      loading.value = false
      return
    }
    // Kazang - South Africa networks
    else if (providerId.value === 'kazang') {
      const payload = {
        product_id: parseInt(selectedProduct.value),
        target_phone_number: phoneNumber.value,
        amount: parseFloat(amount.value)
      }

      console.log('Kazang purchase payload:', payload)
      response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/kazang/airtime/purchase`, payload)
    }
    else {
      // Other providers - fallback
      alertStore.error(`${providerInfo.value.name} airtime purchase not yet implemented`)
      loading.value = false
      return
    }

    showSuccess.value = true
    alertStore.success(response.message || 'Airtime purchased successfully')
  } catch (error) {
    console.error('Purchase failed:', error)
    const errorMessage = error.message || 'Purchase failed. Please try again.'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors) {
      if (error.errors.product_id) {
        errors.value.product = error.errors.product_id[0]
      }
      if (error.errors.target_phone_number) {
        errors.value.phoneNumber = error.errors.target_phone_number[0]
      }
      if (error.errors.amount) {
        errors.value.amount = error.errors.amount[0]
      }
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  // Load Kazang products if provider is Kazang
  if (providerId.value === 'kazang') {
    loadKazangProducts()
  }
})

const resetForm = () => {
  showSuccess.value = false
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

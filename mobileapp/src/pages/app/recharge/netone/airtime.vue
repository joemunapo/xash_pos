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
            <h1 class="text-white text-xl font-semibold">NetOne Airtime</h1>
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

          <!-- Phone Number -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Phone Number
            </label>
            <input
              v-model="phoneNumber"
              type="tel"
              placeholder="0712345678"
              @input="onPhoneInput"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.phoneNumber }"
            />
            <p v-if="errors.phoneNumber" class="mt-1 text-sm text-red-500">{{ errors.phoneNumber }}</p>
          </div>

          <!-- Bundle Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Select Amount(s)
            </label>
            <p class="text-xs text-gray-500 mb-3">Tap to add, tap again to remove</p>

            <!-- Loading State -->
            <div v-if="loadingBundles" class="flex items-center justify-center py-8">
              <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i>
            </div>

            <!-- Bundles Grid -->
            <div v-else-if="bundles.length > 0" class="grid grid-cols-3 gap-3">
              <button
                v-for="bundle in bundles"
                :key="bundle.product_code"
                type="button"
                @click="toggleBundle(bundle)"
                class="p-3 rounded-xl border-2 text-center transition-all relative"
                :class="isBundleSelected(bundle.product_code)
                  ? 'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10'
                  : 'border-gray-200 hover:border-[var(--fluxr-green-dark)]'"
              >
                <p class="text-lg font-bold text-[var(--fluxr-dark)]">${{ bundle.amount }}</p>
                <!-- Selection indicator -->
                <div
                  v-if="getBundleCount(bundle.product_code) > 0"
                  class="absolute -top-2 -right-2 w-5 h-5 bg-[var(--fluxr-green-dark)] rounded-full flex items-center justify-center"
                >
                  <span class="text-xs text-white font-bold">{{ getBundleCount(bundle.product_code) }}</span>
                </div>
              </button>
            </div>

            <!-- No Bundles -->
            <div v-else class="text-center py-8">
              <i class="fa-solid fa-exclamation-circle text-gray-300 text-3xl mb-2"></i>
              <p class="text-gray-500 text-sm">No bundles available</p>
            </div>

            <p v-if="errors.amount" class="mt-2 text-sm text-red-500">{{ errors.amount }}</p>
          </div>

          <!-- Selected Summary -->
          <div v-if="selectedBundles.length > 0" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-600">Selected:</span>
              <button
                type="button"
                @click="clearSelection"
                class="text-xs text-red-500 hover:text-red-700"
              >
                Clear all
              </button>
            </div>
            <div class="flex flex-wrap gap-2 mb-3">
              <span
                v-for="(bundle, index) in selectedBundles"
                :key="index"
                class="px-2 py-1 bg-[var(--fluxr-green)]/20 text-[var(--fluxr-dark)] text-sm rounded"
              >
                ${{ bundle.amount }}
              </span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
              <span class="font-medium text-[var(--fluxr-dark)]">Total:</span>
              <span class="text-xl font-bold text-[var(--fluxr-green-dark)]">${{ totalAmount.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Purchase Button -->
          <button
            type="submit"
            :disabled="loading || selectedBundles.length === 0"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Processing...' : `Purchase Airtime${selectedBundles.length > 0 ? ` ($${totalAmount.toFixed(2)})` : ''}` }}
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

const phoneNumber = ref('')
const bundles = ref([])
const selectedBundles = ref([])
const loading = ref(false)
const loadingBundles = ref(false)
const showSuccess = ref(false)
const generalError = ref('')
const errors = ref({
  phoneNumber: '',
  amount: ''
})

const totalAmount = computed(() => {
  return selectedBundles.value.reduce((sum, bundle) => sum + bundle.amount, 0)
})

const loadBundles = async () => {
  loadingBundles.value = true
  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/netone/data/bundles?currency=usd`)
    // Filter bundles to show only specific amounts: $0.50, $1, $2, $5, $10, $20, $50
    const allowedAmounts = [0.5, 1, 2, 5, 10, 20, 50]
    const allBundles = response.data || []
    bundles.value = allBundles
      .filter(b => allowedAmounts.includes(b.amount))
      .sort((a, b) => a.amount - b.amount)
  } catch (error) {
    console.error('Failed to load bundles:', error)
    alertStore.error('Failed to load airtime bundles')
  } finally {
    loadingBundles.value = false
  }
}

const toggleBundle = (bundle) => {
  const index = selectedBundles.value.findIndex(b => b.product_code === bundle.product_code)
  if (index > -1) {
    // Remove one instance
    selectedBundles.value.splice(index, 1)
  } else {
    // Add bundle
    selectedBundles.value.push({ ...bundle })
  }
  errors.value.amount = ''
}

const isBundleSelected = (productCode) => {
  return selectedBundles.value.some(b => b.product_code === productCode)
}

const getBundleCount = (productCode) => {
  return selectedBundles.value.filter(b => b.product_code === productCode).length
}

const clearSelection = () => {
  selectedBundles.value = []
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
    phoneNumber: '',
    amount: ''
  }

  let isValid = true

  if (!phoneNumber.value) {
    errors.value.phoneNumber = 'Phone number is required'
    isValid = false
  } else if (!/^(\+263|0)[0-9]{9}$/.test(phoneNumber.value)) {
    errors.value.phoneNumber = 'Enter a valid phone number (e.g. 0712345678)'
    isValid = false
  }

  if (selectedBundles.value.length === 0) {
    errors.value.amount = 'Please select at least one amount'
    isValid = false
  }

  return isValid
}

const handlePurchase = async () => {
  if (!validateForm()) return

  loading.value = true
  generalError.value = ''

  try {
    // Format phone number
    let formattedPhone = phoneNumber.value
    if (formattedPhone.startsWith('0')) {
      formattedPhone = '+263' + formattedPhone.substring(1)
    }

    // Send single request with all bundles
    const payload = {
      phone_number: formattedPhone,
      bundles: selectedBundles.value.map(bundle => ({
        product_code: bundle.product_code,
        amount: bundle.amount
      })),
      currency: 'usd'
    }

    const response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/netone/airtime/purchase-multiple`, payload)

    // Check for partial success
    if (response.failed && response.failed.length > 0) {
      alertStore.warning(response.message)
    } else {
      alertStore.success(response.message || 'Airtime purchased successfully')
    }

    showSuccess.value = true
  } catch (error) {
    console.error('Purchase failed:', error)
    const errorMessage = error.message || 'Purchase failed. Please try again.'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors) {
      if (error.errors.phone_number) {
        errors.value.phoneNumber = error.errors.phone_number[0]
      }
      if (error.errors.bundles) {
        errors.value.amount = error.errors.bundles[0]
      }
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  showSuccess.value = false
  phoneNumber.value = ''
  selectedBundles.value = []
  router.push('/dashboard')
}

onMounted(() => {
  loadBundles()
})
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

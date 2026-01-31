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
            <h1 class="text-white text-xl font-semibold">{{ providerInfo.name }} Data</h1>
            <p class="text-white/60 text-sm">Buy data bundles</p>
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
              :placeholder="providerInfo.placeholder"
              @input="errors.phoneNumber = ''"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.phoneNumber }"
            />
            <p v-if="errors.phoneNumber" class="mt-1 text-sm text-red-500">{{ errors.phoneNumber }}</p>
          </div>

          <!-- Data Bundle Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Select Bundle
            </label>

            <!-- Loading State -->
            <div v-if="loadingBundles" class="flex items-center justify-center py-8">
              <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i>
            </div>

            <!-- Bundles Grid -->
            <div v-else-if="bundles.length > 0" class="grid grid-cols-2 gap-3">
              <button
                v-for="bundle in bundles"
                :key="bundle.product_code"
                type="button"
                @click="selectBundle(bundle)"
                class="p-4 rounded-xl border-2 text-left transition-all"
                :class="selectedBundle?.product_code === bundle.product_code
                  ? 'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10'
                  : 'border-gray-200 hover:border-[var(--fluxr-green-dark)]'"
              >
                <p class="font-semibold text-[var(--fluxr-dark)]">{{ bundle.name }}</p>
                <p class="text-lg font-bold text-[var(--fluxr-green-dark)]">${{ bundle.amount }}</p>
                <p v-if="bundle.validity" class="text-xs text-gray-500">{{ bundle.validity }}</p>
              </button>
            </div>

            <!-- No Bundles -->
            <div v-else class="text-center py-8">
              <i class="fa-solid fa-wifi-slash text-gray-300 text-3xl mb-2"></i>
              <p class="text-gray-500 text-sm">No bundles available</p>
            </div>

            <p v-if="errors.bundle" class="mt-2 text-sm text-red-500">{{ errors.bundle }}</p>
          </div>

          <!-- Selected Bundle Summary -->
          <div v-if="selectedBundle" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Selected Bundle:</span>
              <span class="font-semibold text-[var(--fluxr-dark)]">{{ selectedBundle.name }}</span>
            </div>
            <div class="flex justify-between items-center mt-1">
              <span class="text-sm text-gray-600">Amount:</span>
              <span class="font-bold text-[var(--fluxr-green-dark)]">${{ selectedBundle.amount }}</span>
            </div>
          </div>

          <!-- Purchase Button -->
          <button
            type="submit"
            :disabled="loading || !selectedBundle"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Processing...' : 'Purchase Data' }}
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
            <p class="text-gray-600 mb-6">Your data bundle has been activated</p>
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
  netone: {
    name: 'NetOne',
    placeholder: '0712345678',
    prefix: '071'
  }
}

const providerInfo = computed(() => {
  return providerData[providerId.value] || {
    name: 'Provider',
    placeholder: '0771234567',
    prefix: ''
  }
})

const phoneNumber = ref('')
const bundles = ref([])
const selectedBundle = ref(null)
const loading = ref(false)
const loadingBundles = ref(false)
const showSuccess = ref(false)
const generalError = ref('')
const errors = ref({
  phoneNumber: '',
  bundle: ''
})

const goBack = () => {
  router.back()
}

const loadBundles = async () => {
  loadingBundles.value = true
  try {
    let response

    if (providerId.value === 'netone') {
      // Use NetOne API for bundles
      response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/netone/data/bundles?currency=usd`)
      bundles.value = response.data || []
    } else if (providerId.value === 'econet') {
      // Use general bundles API for Econet
      response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/airtime/bundles?provider=kazang`)
      bundles.value = response.data?.bundles || []
    }
  } catch (error) {
    console.error('Failed to load bundles:', error)
    alertStore.error('Failed to load data bundles')
  } finally {
    loadingBundles.value = false
  }
}

const selectBundle = (bundle) => {
  selectedBundle.value = bundle
  errors.value.bundle = ''
}

const validateForm = () => {
  errors.value = {
    phoneNumber: '',
    bundle: ''
  }

  let isValid = true

  if (!phoneNumber.value) {
    errors.value.phoneNumber = 'Phone number is required'
    isValid = false
  } else if (!/^(\+263|0)[0-9]{9}$/.test(phoneNumber.value)) {
    errors.value.phoneNumber = 'Enter a valid phone number (e.g. 0771234567)'
    isValid = false
  }

  if (!selectedBundle.value) {
    errors.value.bundle = 'Please select a data bundle'
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

    // Format phone number
    let formattedPhone = phoneNumber.value
    if (formattedPhone.startsWith('0')) {
      formattedPhone = '+263' + formattedPhone.substring(1)
    }

    if (providerId.value === 'netone') {
      // Use NetOne API
      const payload = {
        phone_number: formattedPhone,
        product_code: selectedBundle.value.product_code,
        amount: selectedBundle.value.amount,
        currency: 'usd'
      }
      response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/netone/data/purchase`, payload)
    } else if (providerId.value === 'econet') {
      // Use Kazang API for Econet
      const payload = {
        provider: 'kazang',
        target_phone_number: formattedPhone,
        product_code: selectedBundle.value.product_code,
        amount: selectedBundle.value.amount
      }
      response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/airtime/purchase-data`, payload)
    } else {
      throw new Error('Provider not supported')
    }

    showSuccess.value = true
    alertStore.success(response.message || 'Data bundle purchased successfully')
  } catch (error) {
    console.error('Purchase failed:', error)
    const errorMessage = error.message || 'Purchase failed. Please try again.'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors) {
      if (error.errors.phone_number || error.errors.target_phone_number) {
        errors.value.phoneNumber = (error.errors.phone_number || error.errors.target_phone_number)[0]
      }
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  showSuccess.value = false
  phoneNumber.value = ''
  selectedBundle.value = null
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

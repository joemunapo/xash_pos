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
          <div>
            <h1 class="text-white text-xl font-semibold">Buy Data</h1>
            <p class="text-white/60 text-sm">Get data bundles instantly</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-4 relative z-20">
      <div class="bg-white rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="handlePurchase">
          <!-- Provider Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Network Provider
            </label>
            <select
              v-model="selectedProvider"
              @change="loadBundles"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.provider }"
            >
              <option value="">Select Provider</option>
              <option v-for="provider in providers" :key="provider.code" :value="provider.code">
                {{ provider.name }}
              </option>
            </select>
            <p v-if="errors.provider" class="mt-1 text-sm text-red-500">{{ errors.provider }}</p>
          </div>

          <!-- Phone Number -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Phone Number
            </label>
            <input
              v-model="phoneNumber"
              type="tel"
              placeholder="+263771234567"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.phoneNumber }"
            />
            <p v-if="errors.phoneNumber" class="mt-1 text-sm text-red-500">{{ errors.phoneNumber }}</p>
          </div>

          <!-- Data Bundle Selection -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Select Bundle
            </label>
            <div v-if="loadingBundles" class="text-center py-4">
              <i class="fa-solid fa-spinner fa-spin text-[var(--fluxr-dark)] text-2xl"></i>
            </div>
            <div v-else-if="bundles.length === 0 && selectedProvider" class="text-center py-4 text-gray-500">
              No bundles available
            </div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto">
              <label
                v-for="bundle in bundles"
                :key="bundle.id"
                class="flex items-center justify-between p-3 border-2 rounded-xl cursor-pointer hover:border-[var(--fluxr-green-dark)] transition-all"
                :class="{
                  'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': selectedBundle?.id === bundle.id,
                  'border-gray-200': selectedBundle?.id !== bundle.id
                }"
              >
                <input
                  type="radio"
                  :value="bundle"
                  v-model="selectedBundle"
                  class="hidden"
                />
                <div>
                  <p class="font-medium text-[var(--fluxr-dark)]">{{ bundle.name }}</p>
                  <p class="text-xs text-gray-500">{{ bundle.description || bundle.validity }}</p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-[var(--fluxr-green-dark)]">${{ bundle.price }}</p>
                </div>
              </label>
            </div>
            <p v-if="errors.bundle" class="mt-1 text-sm text-red-500">{{ errors.bundle }}</p>
          </div>

          <!-- Purchase Button -->
          <button
            type="submit"
            :disabled="loading || !selectedBundle"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Processing...' : `Purchase ${selectedBundle ? '$' + selectedBundle.price : 'Data'}` }}
          </button>
        </form>
      </div>

      <!-- Success Modal -->
      <div v-if="showSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fa-solid fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-[var(--fluxr-dark)] mb-2">Purchase Successful!</h3>
            <p class="text-gray-600 mb-6">Your data bundle has been activated</p>
            <button
              @click="resetForm"
              class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-xl transition-all"
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchWrapper } from '@/helpers'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

const providers = ref([])
const bundles = ref([])
const selectedProvider = ref('')
const selectedBundle = ref(null)
const phoneNumber = ref('')
const loading = ref(false)
const loadingBundles = ref(false)
const showSuccess = ref(false)
const errors = ref({
  provider: '',
  phoneNumber: '',
  bundle: ''
})

const goBack = () => {
  router.back()
}

const loadProviders = async () => {
  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/airtime/providers`)
    providers.value = response.providers || response.data || []
  } catch (error) {
    console.error('Failed to load providers:', error)
    alertStore.error('Failed to load providers')
  }
}

const loadBundles = async () => {
  if (!selectedProvider.value) {
    bundles.value = []
    return
  }

  loadingBundles.value = true
  selectedBundle.value = null

  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/airtime/bundles?provider=${selectedProvider.value}&type=data`)
    bundles.value = response.bundles || response.data || []
  } catch (error) {
    console.error('Failed to load bundles:', error)
    alertStore.error('Failed to load bundles')
    bundles.value = []
  } finally {
    loadingBundles.value = false
  }
}

const validateForm = () => {
  errors.value = {
    provider: '',
    phoneNumber: '',
    bundle: ''
  }

  let isValid = true

  if (!selectedProvider.value) {
    errors.value.provider = 'Please select a provider'
    isValid = false
  }

  if (!phoneNumber.value) {
    errors.value.phoneNumber = 'Phone number is required'
    isValid = false
  } else if (!/^\+[0-9]{10,15}$/.test(phoneNumber.value)) {
    errors.value.phoneNumber = 'Enter a valid phone number (e.g. +263771234567)'
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

  try {
    const response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}/airtime/purchase-data`, {
      provider: selectedProvider.value,
      phone_number: phoneNumber.value,
      bundle_id: selectedBundle.value.id,
      amount: parseFloat(selectedBundle.value.price)
    })

    showSuccess.value = true
    alertStore.success(response.message || 'Data bundle purchased successfully')
  } catch (error) {
    console.error('Purchase failed:', error)
    alertStore.error(error.message || 'Purchase failed')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  showSuccess.value = false
  selectedProvider.value = ''
  selectedBundle.value = null
  phoneNumber.value = ''
  bundles.value = []
  router.push('/dashboard')
}

onMounted(() => {
  loadProviders()
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

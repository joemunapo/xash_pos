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
            <h1 class="text-white text-xl font-semibold">Pay Bills</h1>
            <p class="text-white/60 text-sm">Pay DStv, GOtv and more</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-4 relative z-20">
      <!-- Service Selection -->
      <div v-if="step === 0" class="space-y-3">
        <button
          @click="selectService('dstv')"
          class="w-full bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex items-center justify-between"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-tv text-blue-600 text-xl"></i>
            </div>
            <div class="text-left">
              <p class="font-semibold text-[var(--fluxr-dark)]">DStv</p>
              <p class="text-sm text-gray-600">Pay your DStv subscription</p>
            </div>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-400"></i>
        </button>

        <button
          @click="selectService('gotv')"
          class="w-full bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex items-center justify-between"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-tv text-green-600 text-xl"></i>
            </div>
            <div class="text-left">
              <p class="font-semibold text-[var(--fluxr-dark)]">GOtv</p>
              <p class="text-sm text-gray-600">Pay your GOtv subscription</p>
            </div>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-400"></i>
        </button>
      </div>

      <!-- Payment Form -->
      <div v-else class="bg-white rounded-2xl p-6 shadow-sm">
        <!-- Progress Indicator -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-500">Step {{ step }} of 3</span>
            <span class="text-xs font-medium text-[var(--fluxr-green-dark)]">{{ getStepLabel() }}</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div
              class="bg-[var(--fluxr-green-dark)] h-2 rounded-full transition-all duration-300"
              :style="{ width: `${(step / 3) * 100}%` }"
            ></div>
          </div>
        </div>

        <!-- Step 1: Account Details -->
        <form v-if="step === 1" @submit.prevent="initiatePayment">
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              {{ selectedService === 'dstv' ? 'Smartcard Number' : 'IUC Number' }}
            </label>
            <input
              v-model="accountNumber"
              type="text"
              :placeholder="selectedService === 'dstv' ? 'Enter smartcard number' : 'Enter IUC number'"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.accountNumber }"
            />
            <p v-if="errors.accountNumber" class="mt-1 text-sm text-red-500">{{ errors.accountNumber }}</p>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Validating...' : 'Continue' }}
          </button>
        </form>

        <!-- Step 2: Customer Details & Amount -->
        <div v-if="step === 2">
          <!-- Customer Info -->
          <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl">
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-circle-check text-green-600 text-xl mt-1"></i>
              <div class="flex-1">
                <p class="font-medium text-[var(--fluxr-dark)]">{{ customerInfo.customer_name }}</p>
                <p class="text-sm text-gray-600">{{ customerInfo.account_number }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ customerInfo.bouquet_name }}</p>
                <p v-if="customerInfo.due_date" class="text-xs text-gray-500">Due: {{ customerInfo.due_date }}</p>
              </div>
            </div>
          </div>

          <!-- Amount -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Amount (USD)
            </label>
            <input
              v-model="amount"
              type="number"
              step="0.01"
              placeholder="Enter amount"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.amount }"
            />
            <p v-if="errors.amount" class="mt-1 text-sm text-red-500">{{ errors.amount }}</p>
          </div>

          <div class="flex gap-3">
            <button
              @click="step = 1"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-xl transition-all"
            >
              Back
            </button>
            <button
              @click="submitAmount"
              :disabled="loading"
              class="flex-1 bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center"
            >
              <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
              {{ loading ? 'Processing...' : 'Continue' }}
            </button>
          </div>
        </div>

        <!-- Step 3: Confirmation -->
        <div v-if="step === 3">
          <div class="mb-6 space-y-3">
            <div class="p-4 bg-gray-50 rounded-xl">
              <p class="text-xs text-gray-500 mb-1">Customer</p>
              <p class="font-medium text-[var(--fluxr-dark)]">{{ customerInfo.customer_name }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl">
              <p class="text-xs text-gray-500 mb-1">Account Number</p>
              <p class="font-medium text-[var(--fluxr-dark)]">{{ customerInfo.account_number }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl">
              <p class="text-xs text-gray-500 mb-1">Package</p>
              <p class="font-medium text-[var(--fluxr-dark)]">{{ customerInfo.bouquet_name }}</p>
            </div>

            <div class="p-4 bg-[var(--fluxr-green)]/10 rounded-xl">
              <p class="text-xs text-gray-500 mb-1">Amount to Pay</p>
              <p class="font-bold text-2xl text-[var(--fluxr-dark)]">${{ parseFloat(amount).toFixed(2) }}</p>
            </div>
          </div>

          <div class="flex gap-3">
            <button
              @click="step = 2"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-xl transition-all"
            >
              Back
            </button>
            <button
              @click="confirmPayment"
              :disabled="loading"
              class="flex-1 bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] disabled:opacity-50 text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center"
            >
              <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
              {{ loading ? 'Processing Payment...' : 'Confirm & Pay' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Success Modal -->
      <div v-if="showSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fa-solid fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-[var(--fluxr-dark)] mb-2">Payment Successful!</h3>
            <p class="text-gray-600 mb-4">Your payment has been processed successfully</p>

            <div v-if="paymentResult" class="bg-gray-50 rounded-xl p-4 mb-4 text-left">
              <p class="text-xs text-gray-500">Reference</p>
              <p class="font-mono text-sm font-medium text-[var(--fluxr-dark)]">{{ paymentResult.reference }}</p>
            </div>

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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { fetchWrapper } from '@/helpers'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

const step = ref(0)
const selectedService = ref('')
const accountNumber = ref('')
const customerInfo = ref(null)
const amount = ref('')
const loading = ref(false)
const showSuccess = ref(false)
const paymentResult = ref(null)
const sessionId = ref('')
const errors = ref({
  accountNumber: '',
  amount: ''
})

const goBack = () => {
  if (step.value > 0) {
    step.value = 0
    selectedService.value = ''
    resetFormData()
  } else {
    router.back()
  }
}

const selectService = (service) => {
  selectedService.value = service
  step.value = 1
}

const getStepLabel = () => {
  switch (step.value) {
    case 1: return 'Account Details'
    case 2: return 'Enter Amount'
    case 3: return 'Confirm Payment'
    default: return ''
  }
}

const initiatePayment = async () => {
  if (!accountNumber.value) {
    errors.value.accountNumber = 'Account number is required'
    return
  }

  loading.value = true
  errors.value.accountNumber = ''

  try {
    const endpoint = selectedService.value === 'dstv'
      ? '/bill-payments/dstv/initiate'
      : '/bill-payments/gotv/initiate'

    const response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}${endpoint}`, {
      account_number: accountNumber.value
    })

    customerInfo.value = response.data || response.customer_info
    sessionId.value = response.session_id || response.data?.session_id || ''
    step.value = 2
    alertStore.success('Account validated successfully')
  } catch (error) {
    console.error('Initiation failed:', error)
    errors.value.accountNumber = error.message || 'Invalid account number'
    alertStore.error(error.message || 'Account validation failed')
  } finally {
    loading.value = false
  }
}

const submitAmount = async () => {
  if (!amount.value) {
    errors.value.amount = 'Amount is required'
    return
  }

  if (amount.value <= 0) {
    errors.value.amount = 'Amount must be greater than 0'
    return
  }

  loading.value = true
  errors.value.amount = ''

  try {
    const endpoint = selectedService.value === 'dstv'
      ? '/bill-payments/dstv/submit-amount'
      : '/bill-payments/gotv/submit-amount'

    const payload = {
      account_number: accountNumber.value,
      amount: parseFloat(amount.value)
    }

    if (sessionId.value) {
      payload.session_id = sessionId.value
    }

    await fetchWrapper.post(`${import.meta.env.VITE_API_URL}${endpoint}`, payload)

    step.value = 3
  } catch (error) {
    console.error('Amount submission failed:', error)
    alertStore.error(error.message || 'Failed to process amount')
  } finally {
    loading.value = false
  }
}

const confirmPayment = async () => {
  loading.value = true

  try {
    const endpoint = selectedService.value === 'dstv'
      ? '/bill-payments/dstv/confirm'
      : '/bill-payments/gotv/confirm'

    const payload = {
      account_number: accountNumber.value,
      amount: parseFloat(amount.value)
    }

    if (sessionId.value) {
      payload.session_id = sessionId.value
    }

    const response = await fetchWrapper.post(`${import.meta.env.VITE_API_URL}${endpoint}`, payload)

    paymentResult.value = response.data || response
    showSuccess.value = true
    alertStore.success(response.message || 'Payment successful')
  } catch (error) {
    console.error('Payment confirmation failed:', error)
    alertStore.error(error.message || 'Payment failed')
  } finally {
    loading.value = false
  }
}

const resetFormData = () => {
  accountNumber.value = ''
  customerInfo.value = null
  amount.value = ''
  sessionId.value = ''
  errors.value = {
    accountNumber: '',
    amount: ''
  }
}

const resetForm = () => {
  showSuccess.value = false
  paymentResult.value = null
  step.value = 0
  selectedService.value = ''
  resetFormData()
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

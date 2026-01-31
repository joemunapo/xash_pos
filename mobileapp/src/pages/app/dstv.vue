<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6 ">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-4">
            <button @click="goBack" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
              <i class="fa-solid fa-arrow-left text-white text-lg"></i>
            </button>
            <div>
              <h1 class="text-white text-xl font-semibold">DStv Payment</h1>
              <p class="text-white/60 text-sm">Pay your DStv subscription</p>
            </div>
          </div>
          <button
            @click="fetchHistory"
            :disabled="loadingHistory"
            class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors"
          >
            <i v-if="loadingHistory" class="fa-solid fa-spinner fa-spin text-white text-lg"></i>
            <i v-else class="fa-solid fa-clock-rotate-left text-white text-lg"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-12 relative z-20 ">
      <div class="bg-white glass-shadow rounded-lg p-6 ">
        <!-- General Error Message -->
        <div v-if="generalError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <p class="text-sm text-red-700">{{ generalError }}</p>
          </div>
        </div>

        <!-- Step 1: Enter Smartcard -->
        <div v-if="step === 1">
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Smartcard Number
            </label>
            <input
              v-model="smartcardNumber"
              type="text"
              placeholder="Enter your smartcard number"
              @input="onSmartcardInput"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.smartcard }"
            />
            <p v-if="errors.smartcard" class="mt-1 text-sm text-red-500">{{ errors.smartcard }}</p>
          </div>

          <button
            @click="lookupCustomer"
            :disabled="loadingLookup || !smartcardNumber"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
          >
            <i v-if="loadingLookup" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loadingLookup ? 'Looking up...' : 'Find Account' }}
          </button>
        </div>

        <!-- Step 2: Customer Details & Payment -->
        <div v-if="step === 2">
          <!-- Customer Info Card -->
          <div class="mb-6 p-4 bg-pink-50 border border-pink-100 rounded-lg">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center">
                <i class="fa-solid fa-user text-pink-600"></i>
              </div>
              <div>
                <p class="font-semibold text-[var(--fluxr-dark)]">{{ customer.name }}</p>
                <p class="text-sm text-gray-500">{{ smartcardNumber }}</p>
              </div>
            </div>
            <div v-if="customer.amount_due" class="flex justify-between items-center pt-3 border-t border-pink-200">
              <span class="text-sm text-gray-600">Amount Due:</span>
              <span class="font-bold text-pink-600">${{ customer.amount_due.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Amount Input -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Payment Amount (USD)
            </label>
            <input
              v-model="amount"
              type="number"
              step="0.01"
              :placeholder="customer.amount_due ? customer.amount_due.toFixed(2) : '50.00'"
              @input="errors.amount = ''"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.amount }"
            />
            <p v-if="errors.amount" class="mt-1 text-sm text-red-500">{{ errors.amount }}</p>
          </div>

          <!-- Quick Amount Buttons -->
          <div v-if="customer.amount_due" class="mb-6">
            <p class="text-sm font-medium text-[var(--fluxr-dark)] mb-2">Quick Select</p>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="amount = customer.amount_due; errors.amount = ''"
                class="py-2 px-3 rounded-lg border-2 border-gray-200 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/10 transition-all text-sm font-medium"
                :class="{ 'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': amount == customer.amount_due }"
              >
                Full Amount (${{ customer.amount_due.toFixed(2) }})
              </button>
              <button
                type="button"
                @click="amount = ''; errors.amount = ''"
                class="py-2 px-3 rounded-lg border-2 border-gray-200 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/10 transition-all text-sm font-medium"
              >
                Custom Amount
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-3">
            <button
              @click="processPayment"
              :disabled="loadingPayment || !amount"
              class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
            >
              <i v-if="loadingPayment" class="fa-solid fa-spinner fa-spin mr-2"></i>
              {{ loadingPayment ? 'Processing...' : `Pay $${amount || '0.00'}` }}
            </button>

            <button
              @click="resetToStep1"
              class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition-all"
            >
              Change Smartcard
            </button>
          </div>
        </div>
      </div>

      <!-- Payment History Section -->
      <div class="bg-white glass-shadow rounded-lg p-6  mt-10">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-[var(--fluxr-dark)]">Recent Payments</h2>
          <button
            @click="fetchHistory"
            :disabled="loadingHistory"
            class="text-sm text-[var(--fluxr-green-dark)] hover:underline"
          >
            <i v-if="loadingHistory" class="fa-solid fa-spinner fa-spin mr-1"></i>
            Refresh
          </button>
        </div>

        <div v-if="loadingHistory && paymentHistory.length === 0" class="text-center py-6">
          <i class="fa-solid fa-spinner fa-spin text-gray-400 text-xl"></i>
        </div>

        <div v-else-if="paymentHistory.length === 0" class="text-center py-6">
          <i class="fa-solid fa-receipt text-gray-300 text-2xl mb-2"></i>
          <p class="text-sm text-gray-500">No payment history yet</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="tx in paymentHistory"
            :key="tx.id"
            class="p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex justify-between items-start mb-2">
              <div>
                <p class="text-sm font-medium text-[var(--fluxr-dark)]">{{ tx.description }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(tx.created_at) }}</p>
              </div>
              <span class="text-sm font-bold text-[var(--fluxr-dark)]">${{ parseFloat(tx.amount).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs">
              <span class="text-gray-500">Ref: {{ tx.reference }}</span>
              <span
                :class="tx.status === 'completed' ? 'text-green-600 bg-green-100' : 'text-yellow-600 bg-yellow-100'"
                class="px-2 py-0.5 rounded-full capitalize"
              >
                {{ tx.status }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { billsService } from '@/helpers'
import { useAlertStore } from '@/stores'
import Swal from 'sweetalert2'

const router = useRouter()
const alertStore = useAlertStore()

const step = ref(1)
const smartcardNumber = ref('')
const amount = ref('')
const customer = ref({})
const dataReference = ref('')
const confirmationNumber = ref('')
const receipt = ref('')
const transactionDetails = ref(null)
const newBalance = ref(null)
const loadingLookup = ref(false)
const loadingSubmit = ref(false)
const loadingPayment = ref(false)
const loadingHistory = ref(false)
const paymentHistory = ref([])
const generalError = ref('')
const errors = ref({
  smartcard: '',
  amount: ''
})

const goBack = () => {
  router.back()
}

const onSmartcardInput = () => {
  // Remove spaces
  smartcardNumber.value = smartcardNumber.value.replace(/\s/g, '')
  errors.value.smartcard = ''
  generalError.value = ''
}

const lookupCustomer = async () => {
  if (!smartcardNumber.value) {
    errors.value.smartcard = 'Smartcard number is required'
    return
  }

  loadingLookup.value = true
  generalError.value = ''

  try {
    // Step 1: Lookup customer details
    const response = await billsService.dstv.lookup(smartcardNumber.value)

    customer.value = {
      name: response.customer?.name || 'Customer',
      amount_due: response.customer?.amount_due || null,
      due_date: response.customer?.due_date || null
    }
    dataReference.value = response.reference

    // Pre-fill amount if due amount available
    if (response.customer?.amount_due) {
      amount.value = response.customer.amount_due
    }

    step.value = 2
  } catch (error) {
    console.error('Lookup failed:', error)
    const errorMessage = error.message || 'Failed to find customer'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors?.smartcard_number) {
      errors.value.smartcard = error.errors.smartcard_number[0]
    }
  } finally {
    loadingLookup.value = false
  }
}

const processPayment = async () => {
  if (!amount.value || amount.value < 10) {
    errors.value.amount = 'Minimum payment is $10'
    return
  }

  loadingPayment.value = true
  generalError.value = ''

  try {
    // Process payment (handles initiate, submit, confirm internally)
    const response = await billsService.dstv.pay(
      smartcardNumber.value,
      parseFloat(amount.value),
      dataReference.value
    )

    receipt.value = response.receipt || response.transaction?.reference
    transactionDetails.value = response.transaction
    newBalance.value = response.balance

    // Show SweetAlert success
    await Swal.fire({
      icon: 'success',
      title: 'Payment Successful!',
      html: `
        <div class="text-left">
          <p class="mb-3">Your DStv payment has been processed.</p>
          <div class="bg-gray-100 rounded-lg p-3 text-sm">
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Smartcard:</span>
              <span class="font-medium">${smartcardNumber.value}</span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Amount:</span>
              <span class="font-medium">$${parseFloat(response.transaction?.amount || amount.value).toFixed(2)}</span>
            </div>
            ${receipt.value ? `
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Receipt:</span>
              <span class="font-medium">${receipt.value}</span>
            </div>
            ` : ''}
            ${response.balance !== undefined ? `
            <div class="flex justify-between pt-2 border-t border-gray-300 mt-2">
              <span class="text-gray-600">New Balance:</span>
              <span class="font-bold text-green-600">$${parseFloat(response.balance).toFixed(2)}</span>
            </div>
            ` : ''}
          </div>
        </div>
      `,
      confirmButtonText: 'Done',
      confirmButtonColor: '#1a1a2e'
    })

    // Refresh history and reset form
    fetchHistory()
    step.value = 1
    smartcardNumber.value = ''
    amount.value = ''
    customer.value = {}
    dataReference.value = ''
  } catch (error) {
    console.error('Payment failed:', error)
    const errorMessage = error.message || 'Payment failed'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors?.amount) {
      errors.value.amount = error.errors.amount[0]
    }
  } finally {
    loadingPayment.value = false
  }
}

const resetToStep1 = () => {
  step.value = 1
  customer.value = {}
  dataReference.value = ''
  amount.value = ''
  generalError.value = ''
}

const resetForm = () => {
  step.value = 1
  smartcardNumber.value = ''
  amount.value = ''
  customer.value = {}
  dataReference.value = ''
  confirmationNumber.value = ''
  receipt.value = ''
  transactionDetails.value = null
  newBalance.value = null
}

const fetchHistory = async () => {
  loadingHistory.value = true
  try {
    const response = await billsService.dstv.history()
    paymentHistory.value = response.transactions || []
  } catch (error) {
    console.error('Failed to fetch history:', error)
  } finally {
    loadingHistory.value = false
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Load history on mount
onMounted(() => {
  fetchHistory()
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

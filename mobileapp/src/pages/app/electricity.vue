<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-4">
            <button @click="goBack" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
              <i class="fa-solid fa-arrow-left text-white text-lg"></i>
            </button>
            <div>
              <h1 class="text-white text-xl font-semibold">Buy Electricity</h1>
              <p class="text-white/60 text-sm">Purchase prepaid electricity tokens</p>
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
    <div class="p-6 -mt-12 relative z-20">
      <div class="bg-white glass-shadow rounded-lg p-6">
        <!-- General Error Message -->
        <div v-if="generalError" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            <p class="text-sm text-red-700">{{ generalError }}</p>
          </div>
        </div>

        <!-- Step 1: Enter Meter Number -->
        <div v-if="step === 1">
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Meter Number
            </label>
            <input
              v-model="meterNumber"
              type="text"
              placeholder="Enter your meter number"
              @input="onMeterInput"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.meterNumber }"
            />
            <p v-if="errors.meterNumber" class="mt-1 text-sm text-red-500">{{ errors.meterNumber }}</p>
          </div>

          <button
            @click="lookupMeter"
            :disabled="loadingLookup || !meterNumber"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
          >
            <i v-if="loadingLookup" class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loadingLookup ? 'Validating...' : 'Validate Meter' }}
          </button>
        </div>

        <!-- Step 2: Customer Details & Payment -->
        <div v-if="step === 2">
          <!-- Customer Info Card -->
          <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fa-solid fa-bolt text-green-600"></i>
              </div>
              <div>
                <p class="font-semibold text-[var(--fluxr-dark)]">{{ customer.name }}</p>
                <p class="text-sm text-gray-500">{{ meterNumber }}</p>
              </div>
            </div>
            <div v-if="customer.address" class="pt-3 border-t border-green-200">
              <p class="text-xs text-gray-600">{{ customer.address }}</p>
            </div>
          </div>

          <!-- Amount Input -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
              Amount (USD)
            </label>
            <input
              v-model="amount"
              type="number"
              step="0.01"
              placeholder="10.00"
              @input="errors.amount = ''"
              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
              :class="{ 'border-red-500': errors.amount }"
            />
            <p v-if="errors.amount" class="mt-1 text-sm text-red-500">{{ errors.amount }}</p>
          </div>

          <!-- Quick Amount Buttons -->
          <div class="mb-6">
            <p class="text-sm font-medium text-[var(--fluxr-dark)] mb-2">Quick Select</p>
            <div class="grid grid-cols-4 gap-2">
              <button
                v-for="quickAmount in [10, 20, 50, 100]"
                :key="quickAmount"
                type="button"
                @click="amount = quickAmount; errors.amount = ''"
                class="py-2 px-3 rounded-lg border-2 border-gray-200 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/10 transition-all text-sm font-medium"
                :class="{ 'border-[var(--fluxr-green-dark)] bg-[var(--fluxr-green)]/10': amount == quickAmount }"
              >
                ${{ quickAmount }}
              </button>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-3">
            <button
              @click="processPurchase"
              :disabled="loadingPurchase || !amount"
              class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center"
            >
              <i v-if="loadingPurchase" class="fa-solid fa-spinner fa-spin mr-2"></i>
              {{ loadingPurchase ? 'Processing...' : `Buy Electricity - $${amount || '0.00'}` }}
            </button>

            <button
              @click="resetToStep1"
              class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition-all"
            >
              Change Meter Number
            </button>
          </div>
        </div>
      </div>

      <!-- Purchase History Section -->
      <div class="bg-white glass-shadow rounded-lg p-6 mt-10">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-[var(--fluxr-dark)]">Recent Purchases</h2>
          <button
            @click="fetchHistory"
            :disabled="loadingHistory"
            class="text-sm text-[var(--fluxr-green-dark)] hover:underline"
          >
            <i v-if="loadingHistory" class="fa-solid fa-spinner fa-spin mr-1"></i>
            Refresh
          </button>
        </div>

        <div v-if="loadingHistory && purchaseHistory.length === 0" class="text-center py-6">
          <i class="fa-solid fa-spinner fa-spin text-gray-400 text-xl"></i>
        </div>

        <div v-else-if="purchaseHistory.length === 0" class="text-center py-6">
          <i class="fa-solid fa-bolt text-gray-300 text-2xl mb-2"></i>
          <p class="text-sm text-gray-500">No purchase history yet</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="tx in purchaseHistory"
            :key="tx.id"
            @click="showTokenDetails(tx)"
            class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-all"
          >
            <div class="flex justify-between items-start mb-2">
              <div>
                <p class="text-sm font-medium text-[var(--fluxr-dark)]">{{ tx.description }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(tx.created_at) }}</p>
                <p class="text-xs text-gray-600 mt-1">Meter: {{ tx.meter_number }}</p>
              </div>
              <span class="text-sm font-bold text-[var(--fluxr-dark)]">${{ parseFloat(tx.amount).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between items-center text-xs">
              <span class="text-gray-500">{{ tx.total_units }} kWh</span>
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
const meterNumber = ref('')
const amount = ref('')
const customer = ref({})
const loadingLookup = ref(false)
const loadingPurchase = ref(false)
const loadingHistory = ref(false)
const purchaseHistory = ref([])
const generalError = ref('')
const errors = ref({
  meterNumber: '',
  amount: ''
})

const goBack = () => {
  router.back()
}

const onMeterInput = () => {
  // Remove spaces
  meterNumber.value = meterNumber.value.replace(/\s/g, '')
  errors.value.meterNumber = ''
  generalError.value = ''
}

const lookupMeter = async () => {
  if (!meterNumber.value) {
    errors.value.meterNumber = 'Meter number is required'
    return
  }

  loadingLookup.value = true
  generalError.value = ''

  try {
    // Step 1: Lookup/validate meter
    const response = await billsService.electricity.lookup(meterNumber.value)

    customer.value = {
      name: response.customer?.name || 'Customer',
      address: response.customer?.address || null,
      meter_number: response.customer?.meter_number || meterNumber.value
    }

    step.value = 2
    alertStore.success('Meter validated successfully')
  } catch (error) {
    console.error('Lookup failed:', error)
    const errorMessage = error.message || 'Failed to validate meter'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors?.meter_number) {
      errors.value.meterNumber = error.errors.meter_number[0]
    }
  } finally {
    loadingLookup.value = false
  }
}

const processPurchase = async () => {
  if (!amount.value || amount.value < 10) {
    errors.value.amount = 'Minimum purchase is $10'
    return
  }

  loadingPurchase.value = true
  generalError.value = ''

  try {
    // Process purchase
    const response = await billsService.electricity.purchase(
      meterNumber.value,
      parseFloat(amount.value)
    )

    const electricity = response.electricity || {}
    const transaction = response.transaction || {}

    // Extract token information
    const tokens = electricity.tokens || []
    const tokenCodes = tokens.map(t => t.code).filter(Boolean)
    const tokenDisplay = tokenCodes.length > 0 ? tokenCodes.join('\n') : 'Token not available'

    // Show SweetAlert success with token details
    await Swal.fire({
      icon: 'success',
      title: 'Purchase Successful!',
      html: `
        <div class="text-left">
          <p class="mb-3">Your electricity purchase has been processed.</p>
          <div class="bg-gray-100 rounded-lg p-3 text-sm">
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Meter:</span>
              <span class="font-medium">${meterNumber.value}</span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Amount:</span>
              <span class="font-medium">$${parseFloat(transaction.amount || amount.value).toFixed(2)}</span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Units:</span>
              <span class="font-medium">${electricity.total_units || 0} kWh</span>
            </div>
            ${tokenCodes.length > 0 ? `
            <div class="mt-3 pt-3 border-t border-gray-300">
              <p class="text-gray-600 mb-2 font-semibold">Token${tokenCodes.length > 1 ? 's' : ''}:</p>
              ${tokens.map(t => `
                <div class="bg-white rounded p-2 mb-2 font-mono text-xs break-all">
                  <div class="font-bold">${t.code || ''}</div>
                  ${t.description ? `<div class="text-gray-500 text-xs">${t.description}</div>` : ''}
                </div>
              `).join('')}
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
      confirmButtonColor: '#1a1a2e',
      width: '90%',
      customClass: {
        htmlContainer: 'text-left'
      }
    })

    // Refresh history and reset form
    fetchHistory()
    step.value = 1
    meterNumber.value = ''
    amount.value = ''
    customer.value = {}
  } catch (error) {
    console.error('Purchase failed:', error)
    const errorMessage = error.message || 'Purchase failed'
    alertStore.error(errorMessage)
    generalError.value = errorMessage

    if (error.errors?.amount) {
      errors.value.amount = error.errors.amount[0]
    }
  } finally {
    loadingPurchase.value = false
  }
}

const resetToStep1 = () => {
  step.value = 1
  customer.value = {}
  amount.value = ''
  generalError.value = ''
}

const fetchHistory = async () => {
  loadingHistory.value = true
  try {
    const response = await billsService.electricity.history()
    purchaseHistory.value = response.transactions || []
  } catch (error) {
    console.error('Failed to fetch history:', error)
  } finally {
    loadingHistory.value = false
  }
}

const showTokenDetails = (tx) => {
  const tokens = tx.tokens || []
  const tokenCodes = tokens.map(t => t.code).filter(Boolean)

  if (tokenCodes.length === 0) {
    Swal.fire({
      icon: 'info',
      title: 'Transaction Details',
      html: `
        <div class="text-left">
          <div class="bg-gray-100 rounded-lg p-3 text-sm">
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Meter:</span>
              <span class="font-medium">${tx.meter_number || 'N/A'}</span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Amount:</span>
              <span class="font-medium">$${parseFloat(tx.amount).toFixed(2)}</span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Units:</span>
              <span class="font-medium">${tx.total_units || 0} kWh</span>
            </div>
            <div class="flex justify-between mb-1">
              <span class="text-gray-600">Status:</span>
              <span class="font-medium capitalize">${tx.status}</span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Token information not available</p>
          </div>
        </div>
      `,
      confirmButtonText: 'Close',
      confirmButtonColor: '#1a1a2e'
    })
    return
  }

  Swal.fire({
    icon: 'info',
    title: 'Token Details',
    html: `
      <div class="text-left">
        <div class="bg-gray-100 rounded-lg p-3 text-sm">
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">Meter:</span>
            <span class="font-medium">${tx.meter_number || 'N/A'}</span>
          </div>
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">Customer:</span>
            <span class="font-medium">${tx.customer_name || 'N/A'}</span>
          </div>
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">Amount:</span>
            <span class="font-medium">$${parseFloat(tx.amount).toFixed(2)}</span>
          </div>
          <div class="flex justify-between mb-1">
            <span class="text-gray-600">Units:</span>
            <span class="font-medium">${tx.total_units || 0} kWh</span>
          </div>
          <div class="mt-3 pt-3 border-t border-gray-300">
            <p class="text-gray-600 mb-2 font-semibold">Token${tokenCodes.length > 1 ? 's' : ''}:</p>
            ${tokens.map(t => `
              <div class="bg-white rounded p-2 mb-2 font-mono text-xs break-all">
                <div class="font-bold">${t.code || ''}</div>
                ${t.description ? `<div class="text-gray-500 text-xs">${t.description}</div>` : ''}
                ${t.units ? `<div class="text-gray-500 text-xs">${t.units} kWh</div>` : ''}
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    `,
    confirmButtonText: 'Close',
    confirmButtonColor: '#1a1a2e',
    width: '90%',
    customClass: {
      htmlContainer: 'text-left'
    }
  })
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

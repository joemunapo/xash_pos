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
            <h1 class="text-white text-xl font-semibold">Add Money</h1>
            <p class="text-white/60 text-sm">Top up your wallet</p>
          </div>
        </div>

        <!-- Current Balance Display -->
        <div class="glass-balance rounded-lg p-4 mt-6">
          <p class="text-white/70 text-sm mb-1">Current Balance</p>
          <div class="flex items-baseline gap-2">
            <span class="text-white text-3xl font-bold">R {{ formatBalance(currentBalance) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-4 relative z-20">
      <!-- Success Message -->
      <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 flex items-start gap-3">
        <i class="fa-solid fa-circle-check text-green-600 text-xl mt-0.5"></i>
        <div class="flex-1">
          <p class="text-green-800 font-medium">{{ successMessage }}</p>
          <p v-if="newBalance" class="text-green-600 text-sm mt-1">New balance: R {{ formatBalance(newBalance) }}</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="handleAddCredit" class="space-y-6">
          <!-- Quick Amount Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Quick Select</label>
            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="amount in quickAmounts"
                :key="amount"
                type="button"
                @click="selectQuickAmount(amount)"
                :class="[
                  'py-3 px-4 rounded-lg font-semibold transition-all',
                  form.amount === amount
                    ? 'bg-[var(--fluxr-green)] text-[var(--fluxr-dark)] border-2 border-[var(--fluxr-green-dark)]'
                    : 'bg-gray-50 text-gray-700 border-2 border-gray-200 hover:border-[var(--fluxr-green)]'
                ]"
                :disabled="loading"
              >
                R{{ amount }}
              </button>
            </div>
          </div>

          <!-- Custom Amount -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
              Or Enter Custom Amount
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">R</span>
              <input
                id="amount"
                v-model.number="form.amount"
                type="number"
                step="0.01"
                min="0.01"
                placeholder="0.00"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
                :disabled="loading"
              />
            </div>
            <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
          </div>

          <!-- Payment Method Info -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-info-circle text-blue-600 text-lg mt-0.5"></i>
              <div>
                <p class="text-blue-800 font-medium text-sm mb-1">Payment Methods</p>
                <ul class="text-blue-700 text-xs space-y-1">
                  <li>• Bank Transfer (EFT)</li>
                  <li>• Card Payment</li>
                  <li>• Instant EFT</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Reference (Optional) -->
          <div>
            <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">
              Payment Reference (Optional)
            </label>
            <input
              id="reference"
              v-model="form.reference"
              type="text"
              placeholder="e.g., BANK-123456"
              maxlength="100"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              :disabled="loading"
            />
          </div>

          <!-- Description (Optional) -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              Description (Optional)
            </label>
            <input
              id="description"
              v-model="form.description"
              type="text"
              placeholder="e.g., Monthly top-up"
              maxlength="255"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              :disabled="loading"
            />
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 text-sm">{{ errorMessage }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading || !form.amount || form.amount <= 0"
            class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            <i v-else class="fa-solid fa-plus mr-2"></i>
            {{ loading ? 'Processing...' : `Add R${form.amount || '0.00'}` }}
          </button>
        </form>
      </div>

      <!-- Info Section -->
      <div class="mt-6 bg-white rounded-2xl p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-[var(--fluxr-dark)] mb-3">How to Add Money</h3>
        <ol class="space-y-3 text-sm text-gray-600">
          <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 bg-[var(--fluxr-green)]/20 text-[var(--fluxr-green-dark)] rounded-full flex items-center justify-center text-xs font-semibold">1</span>
            <span>Select or enter the amount you want to add</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 bg-[var(--fluxr-green)]/20 text-[var(--fluxr-green-dark)] rounded-full flex items-center justify-center text-xs font-semibold">2</span>
            <span>Choose your preferred payment method</span>
          </li>
          <li class="flex items-start gap-3">
            <span class="flex-shrink-0 w-6 h-6 bg-[var(--fluxr-green)]/20 text-[var(--fluxr-green-dark)] rounded-full flex items-center justify-center text-xs font-semibold">3</span>
            <span>Complete the payment and funds will be added instantly</span>
          </li>
        </ol>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { walletService } from '@/helpers/wallet'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

const form = reactive({
  amount: null,
  reference: '',
  description: ''
})

const errors = reactive({
  amount: ''
})

const quickAmounts = [10, 50, 100, 200, 500, 1000]

const loading = ref(false)
const currentBalance = ref(0)
const newBalance = ref(null)
const errorMessage = ref('')
const successMessage = ref('')

const goBack = () => {
  router.back()
}

const formatBalance = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const selectQuickAmount = (amount) => {
  form.amount = amount
  errors.amount = ''
}

const validateForm = () => {
  errors.amount = ''

  if (!form.amount || form.amount <= 0) {
    errors.amount = 'Amount must be greater than 0'
    return false
  }

  if (form.amount < 0.01) {
    errors.amount = 'Minimum amount is R0.01'
    return false
  }

  return true
}

const handleAddCredit = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  newBalance.value = null

  if (!validateForm()) return

  loading.value = true

  try {
    const response = await walletService.addCredit(
      form.amount,
      form.reference || null,
      form.description || null
    )

    successMessage.value = response.message
    newBalance.value = response.balance
    currentBalance.value = response.balance

    // Show success toast
    alertStore.success(`R${form.amount} added successfully! New balance: R${formatBalance(response.balance)}`)

    // Reset form
    form.amount = null
    form.reference = ''
    form.description = ''

    // Clear success message after 5 seconds
    setTimeout(() => {
      successMessage.value = ''
      newBalance.value = null
    }, 5000)
  } catch (error) {
    errorMessage.value = error.message || 'Failed to add credit. Please try again.'
    alertStore.error(error.message || 'Failed to add credit. Please try again.')
  } finally {
    loading.value = false
  }
}

const fetchBalance = async () => {
  try {
    const response = await walletService.getBalance()
    currentBalance.value = response.balance || 0
  } catch (error) {
    console.error('Failed to fetch balance:', error)
  }
}

onMounted(async () => {
  await fetchBalance()
})
</script>

<style scoped>
.glass-balance {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

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

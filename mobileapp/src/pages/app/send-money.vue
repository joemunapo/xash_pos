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
            <h1 class="text-white text-xl font-semibold">Send Money</h1>
            <p class="text-white/60 text-sm">Send money to other users</p>
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
          <p v-if="transactionReference" class="text-green-600 text-sm mt-1">Ref: {{ transactionReference }}</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="handleSendMoney" class="space-y-6">
          <!-- Recipient Phone Number -->
          <div>
            <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-2">
              Recipient Phone Number
            </label>
            <div class="relative">
              <input
                id="phoneNumber"
                v-model="form.phoneNumber"
                type="tel"
                placeholder="e.g., 0712345678 or +27712345678"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
                :disabled="loading"
                @blur="handleCheckAccount"
              />
              <button
                v-if="!recipientName && form.phoneNumber"
                type="button"
                @click="handleCheckAccount"
                :disabled="checkingAccount"
                class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 bg-[var(--fluxr-green)] text-[var(--fluxr-dark)] text-sm rounded-md hover:bg-[var(--fluxr-green-dark)] transition-colors disabled:opacity-50"
              >
                <i v-if="checkingAccount" class="fa-solid fa-spinner fa-spin"></i>
                <span v-else>Check</span>
              </button>
            </div>
            <p v-if="errors.phoneNumber" class="mt-1 text-sm text-red-600">{{ errors.phoneNumber }}</p>
          </div>

          <!-- Recipient Name Display -->
          <div v-if="recipientName" class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-user text-white"></i>
            </div>
            <div class="flex-1">
              <p class="text-sm text-gray-600">Sending to</p>
              <p class="font-semibold text-[var(--fluxr-dark)]">{{ recipientName }}</p>
            </div>
            <button
              type="button"
              @click="clearRecipient"
              class="text-gray-400 hover:text-gray-600"
            >
              <i class="fa-solid fa-times"></i>
            </button>
          </div>

          <!-- Amount -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
              Amount (ZAR)
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

          <!-- Description (Optional) -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              Description (Optional)
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              placeholder="What's this payment for?"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent resize-none"
              :disabled="loading"
            ></textarea>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 text-sm">{{ errorMessage }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading || !recipientName || !form.amount"
            class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            <i v-else class="fa-solid fa-paper-plane mr-2"></i>
            {{ loading ? 'Sending...' : 'Send Money' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { transferService } from '@/helpers/transfers'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

const form = reactive({
  phoneNumber: '',
  amount: null,
  description: ''
})

const errors = reactive({
  phoneNumber: '',
  amount: ''
})

const loading = ref(false)
const checkingAccount = ref(false)
const recipientName = ref('')
const errorMessage = ref('')
const successMessage = ref('')
const transactionReference = ref('')

const goBack = () => {
  router.back()
}

const clearRecipient = () => {
  recipientName.value = ''
  form.phoneNumber = ''
  errors.phoneNumber = ''
}

const handleCheckAccount = async () => {
  if (!form.phoneNumber || recipientName.value) return

  errors.phoneNumber = ''
  errorMessage.value = ''
  checkingAccount.value = true

  try {
    const response = await transferService.checkAccount(form.phoneNumber)
    recipientName.value = response.account_holder_name
  } catch (error) {
    errors.phoneNumber = error.message || 'Account not found'
    recipientName.value = ''
  } finally {
    checkingAccount.value = false
  }
}

const validateForm = () => {
  let isValid = true
  errors.phoneNumber = ''
  errors.amount = ''

  if (!form.phoneNumber) {
    errors.phoneNumber = 'Phone number is required'
    isValid = false
  }

  if (!recipientName.value) {
    errors.phoneNumber = 'Please verify the recipient account first'
    isValid = false
  }

  if (!form.amount || form.amount <= 0) {
    errors.amount = 'Amount must be greater than 0'
    isValid = false
  }

  return isValid
}

const handleSendMoney = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  transactionReference.value = ''

  if (!validateForm()) return

  loading.value = true

  try {
    const response = await transferService.sendMoney(
      form.phoneNumber,
      form.amount,
      form.description || null
    )

    successMessage.value = response.message || 'Money sent successfully!'
    transactionReference.value = response.transaction_id

    // Show success toast
    alertStore.success(`Money sent successfully! Transaction ID: ${response.transaction_id}`)

    // Reset form
    form.phoneNumber = ''
    form.amount = null
    form.description = ''
    recipientName.value = ''

    // Clear success message after 5 seconds
    setTimeout(() => {
      successMessage.value = ''
      transactionReference.value = ''
    }, 5000)
  } catch (error) {
    errorMessage.value = error.message || 'Failed to send money. Please try again.'
    alertStore.error(error.message || 'Failed to send money. Please try again.')
  } finally {
    loading.value = false
  }
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

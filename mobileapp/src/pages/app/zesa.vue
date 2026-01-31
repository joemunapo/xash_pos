<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] p-6 pb-8">
      <button @click="goBack" class="mb-4 p-2 rounded-full hover:bg-white/10 transition-colors">
        <i class="fa-solid fa-arrow-left text-white text-lg"></i>
      </button>
      <h1 class="text-white text-2xl font-bold">ZESA Electricity</h1>
      <p class="text-white/60 text-sm mt-1">Zimbabwe electricity payments</p>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-6 relative z-20">
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <!-- Form Section -->
        <form @submit.prevent="submitPurchase" class="space-y-4">
          <!-- Meter Reference Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fa-solid fa-plug text-amber-600 mr-2"></i>Meter Reference Number
            </label>
            <input
              v-model="meterNumber"
              type="text"
              placeholder="Enter meter reference number"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent outline-none transition"
            />
            <p v-if="errors.meterNumber" class="text-red-500 text-xs mt-1">{{ errors.meterNumber }}</p>
          </div>

          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fa-solid fa-dollar-sign text-green-600 mr-2"></i>Amount (USD)
            </label>
            <input
              v-model="amount"
              type="number"
              placeholder="Enter amount"
              min="10"
              step="0.01"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent outline-none transition"
            />
            <p v-if="errors.amount" class="text-red-500 text-xs mt-1">{{ errors.amount }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] disabled:opacity-50 disabled:cursor-not-allowed text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-lg transition-all"
          >
            <i v-if="!loading" class="fa-solid fa-bolt mr-2"></i>
            <i v-else class="fa-solid fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Processing...' : 'Purchase Electricity' }}
          </button>
        </form>
      </div>

      <!-- Info Section -->
      <div class="mt-6 bg-amber-50 rounded-lg p-4 border border-amber-200">
        <div class="flex gap-3">
          <i class="fa-solid fa-circle-info text-amber-600 text-lg flex-shrink-0 mt-0.5"></i>
          <div class="text-sm text-amber-900">
            <p class="font-semibold mb-1">ZESA Electricity</p>
            <p class="text-xs">Coming soon. Enter your meter reference and amount to purchase electricity tokens.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const meterNumber = ref('')
const amount = ref('')
const loading = ref(false)
const errors = ref({
  meterNumber: '',
  amount: ''
})

const goBack = () => {
  router.back()
}

const submitPurchase = async () => {
  errors.value = { meterNumber: '', amount: '' }

  if (!meterNumber.value) {
    errors.value.meterNumber = 'Meter reference is required'
    return
  }

  if (!amount.value || amount.value < 10) {
    errors.value.amount = 'Minimum purchase is $10'
    return
  }

  loading.value = true

  try {
    // TODO: Implement ZESA API integration when available
    alert('ZESA service is coming soon. Integration pending.')
  } catch (error) {
    console.error('Error:', error)
    errors.value.amount = 'Failed to process payment. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
input {
  transition: all 0.2s ease;
}

input:focus {
  transform: translateY(-1px);
}

button {
  transition: all 0.2s ease;
}

button:active:not(:disabled) {
  transform: scale(0.98);
}
</style>

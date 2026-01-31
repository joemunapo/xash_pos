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
            <h1 class="text-white text-xl font-semibold">Transaction History</h1>
            <p class="text-white/60 text-sm">View all your transactions</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="py-6 -mt-4 relative z-20">
      <div class="p-6">
        <div v-if="loading" class="flex justify-center py-12">
          <i class="fa-solid fa-spinner fa-spin text-[var(--fluxr-dark)] text-3xl"></i>
        </div>

        <div v-else-if="transactions.length === 0" class="text-center py-12">
          <i class="fa-solid fa-receipt text-gray-300 text-6xl mb-3"></i>
          <p class="text-gray-500 text-sm">No transactions yet</p>
          <p class="text-gray-400 text-xs mt-1">Your transactions will appear here</p>
        </div>

        <div v-else class="space-y-5">
          <div
            v-for="transaction in transactions"
            :key="transaction.id"
            class="flex  items-center gap-4 p-4 rounded-lg hover:bg-gray-50 transition-colors border border-gray-100"
          >
            <div :class="`w-12 h-12 rounded-full flex items-center justify-center ${transaction.type === 'credit' ? 'bg-green-100' : 'bg-red-100'}`">
              <i v-if="transaction.type === 'credit'" class="fa-solid fa-arrow-down text-green-600 text-lg"></i>
              <i v-else class="fa-solid fa-arrow-up text-red-600 text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ transaction.description }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(transaction.created_at) }}</p>
            </div>
            <div :class="`text-sm font-semibold ${transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'}`">
              {{ transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
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
import { fetchWrapper } from '@/helpers'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

const transactions = ref([])
const loading = ref(false)

const goBack = () => {
  router.back()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0)
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays === 0) {
    const diffHours = Math.floor(diffTime / (1000 * 60 * 60))
    if (diffHours === 0) {
      const diffMins = Math.floor(diffTime / (1000 * 60))
      return diffMins === 0 ? 'Just now' : `${diffMins}m ago`
    }
    return `${diffHours}h ago`
  } else if (diffDays === 1) {
    return 'Yesterday'
  } else if (diffDays < 7) {
    return `${diffDays}d ago`
  }

  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const fetchTransactions = async () => {
  loading.value = true
  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/transactions`)
    transactions.value = response.data || response.transactions || []
  } catch (error) {
    console.error('Failed to fetch transactions:', error)
    alertStore.error('Failed to load transactions')
    transactions.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchTransactions()
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

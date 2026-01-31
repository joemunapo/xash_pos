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
            <h1 class="text-white text-xl font-semibold">Airtime Purchases</h1>
            <p class="text-white/60 text-sm">View your airtime purchase history</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="py-6 -mt-4 relative z-20">
      <div class="p-6">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
          <i class="fa-solid fa-spinner fa-spin text-[var(--fluxr-dark)] text-3xl"></i>
        </div>

        <!-- Empty State -->
        <div v-else-if="purchases.length === 0" class="text-center py-12">
          <i class="fa-solid fa-phone text-gray-300 text-6xl mb-3"></i>
          <p class="text-gray-500 text-sm">No airtime purchases yet</p>
          <p class="text-gray-400 text-xs mt-1">Your purchases will appear here</p>
        </div>

        <!-- Purchases List -->
        <div v-else class="space-y-3">
          <div
            v-for="purchase in purchases"
            :key="purchase.id"
            @click="viewReceipt(purchase.id)"
            class="bg-white p-4 rounded-lg border border-gray-100 hover:shadow-md transition-shadow cursor-pointer"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <i class="fa-solid fa-phone text-[var(--fluxr-green-dark)] text-sm"></i>
                  <p class="text-sm font-medium text-[var(--fluxr-dark)]">{{ purchase.description }}</p>
                </div>
                <p class="text-xs text-gray-500 mb-2">{{ purchase.target_phone_number }}</p>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                  <span>{{ formatDate(purchase.created_at) }}</span>
                  <span v-if="purchase.reference" class="flex items-center gap-1">
                    <i class="fa-solid fa-hashtag"></i>
                    {{ purchase.reference }}
                  </span>
                </div>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold text-[var(--fluxr-dark)]">{{ formatCurrency(purchase.amount) }}</p>
                <span
                  class="inline-block px-2 py-1 rounded text-xs font-medium mt-1"
                  :class="getStatusClass(purchase.status)"
                >
                  {{ purchase.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Receipt Modal -->
    <div v-if="showReceipt" class="fixed inset-0 bg-black/50 flex items-end sm:items-center justify-center z-50 p-4">
      <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-100 p-4 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-[var(--fluxr-dark)]">Receipt</h3>
          <button @click="closeReceipt" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
            <i class="fa-solid fa-times text-gray-500"></i>
          </button>
        </div>

        <!-- Receipt Content -->
        <div v-if="selectedReceipt" class="p-6">
          <!-- Status Badge -->
          <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3"
                 :class="selectedReceipt.status === 'completed' ? 'bg-green-100' : 'bg-yellow-100'">
              <i class="fa-solid fa-check text-2xl"
                 :class="selectedReceipt.status === 'completed' ? 'text-green-600' : 'text-yellow-600'"></i>
            </div>
            <h4 class="text-xl font-bold text-[var(--fluxr-dark)] mb-1">{{ formatCurrency(selectedReceipt.amount) }}</h4>
            <p class="text-sm text-gray-500">{{ selectedReceipt.description }}</p>
          </div>

          <!-- Receipt Details -->
          <div class="space-y-3 bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Phone Number</span>
              <span class="font-medium text-[var(--fluxr-dark)]">{{ selectedReceipt.target_phone_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Amount</span>
              <span class="font-medium text-[var(--fluxr-dark)]">{{ formatCurrency(selectedReceipt.amount) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Status</span>
              <span class="font-medium capitalize" :class="selectedReceipt.status === 'completed' ? 'text-green-600' : 'text-yellow-600'">
                {{ selectedReceipt.status }}
              </span>
            </div>
            <div v-if="selectedReceipt.reference" class="flex justify-between text-sm">
              <span class="text-gray-500">Reference</span>
              <span class="font-medium text-[var(--fluxr-dark)]">{{ selectedReceipt.reference }}</span>
            </div>
            <div v-if="selectedReceipt.receipt_number" class="flex justify-between text-sm">
              <span class="text-gray-500">Receipt Number</span>
              <span class="font-medium text-[var(--fluxr-dark)]">{{ selectedReceipt.receipt_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Date & Time</span>
              <span class="font-medium text-[var(--fluxr-dark)]">{{ formatFullDate(selectedReceipt.created_at) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Transaction ID</span>
              <span class="font-medium text-[var(--fluxr-dark)]">#{{ selectedReceipt.id }}</span>
            </div>
          </div>

          <!-- Provider Response (if available) -->
          <div v-if="selectedReceipt.provider_response" class="mt-4 p-4 bg-blue-50 rounded-lg">
            <p class="text-xs font-medium text-blue-900 mb-2">Provider Details</p>
            <div class="text-xs text-blue-700 space-y-1">
              <div v-for="(value, key) in selectedReceipt.provider_response" :key="key" class="flex justify-between">
                <span class="capitalize">{{ formatKey(key) }}:</span>
                <span class="font-medium">{{ value }}</span>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-6 space-y-2">
            <button
              @click="shareReceipt"
              class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] text-white font-semibold py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-2"
            >
              <i class="fa-solid fa-share-nodes"></i>
              Share Receipt
            </button>
            <button
              @click="closeReceipt"
              class="w-full border-2 border-gray-200 hover:bg-gray-50 text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-lg transition-all"
            >
              Close
            </button>
          </div>
        </div>

        <!-- Loading receipt -->
        <div v-else class="p-12 text-center">
          <i class="fa-solid fa-spinner fa-spin text-[var(--fluxr-dark)] text-3xl"></i>
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

const purchases = ref([])
const loading = ref(false)
const showReceipt = ref(false)
const selectedReceipt = ref(null)

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

const formatFullDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatKey = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status) => {
  switch (status) {
    case 'completed':
      return 'bg-green-100 text-green-700'
    case 'pending':
      return 'bg-yellow-100 text-yellow-700'
    case 'failed':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
}

const fetchPurchases = async () => {
  loading.value = true
  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/transactions/airtime`)
    purchases.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch airtime purchases:', error)
    alertStore.error('Failed to load airtime purchases')
    purchases.value = []
  } finally {
    loading.value = false
  }
}

const viewReceipt = async (transactionId) => {
  showReceipt.value = true
  selectedReceipt.value = null

  try {
    const response = await fetchWrapper.get(`${import.meta.env.VITE_API_URL}/transactions/${transactionId}`)
    selectedReceipt.value = response.transaction
  } catch (error) {
    console.error('Failed to fetch receipt:', error)
    alertStore.error('Failed to load receipt details')
    showReceipt.value = false
  }
}

const closeReceipt = () => {
  showReceipt.value = false
  selectedReceipt.value = null
}

const shareReceipt = () => {
  if (!selectedReceipt.value) return

  const text = `Airtime Purchase Receipt
Amount: ${formatCurrency(selectedReceipt.value.amount)}
Phone: ${selectedReceipt.value.target_phone_number}
Status: ${selectedReceipt.value.status}
Reference: ${selectedReceipt.value.reference || 'N/A'}
Date: ${formatFullDate(selectedReceipt.value.created_at)}`

  if (navigator.share) {
    navigator.share({
      title: 'Airtime Purchase Receipt',
      text: text
    }).catch(() => {
      // Fallback to copy
      copyToClipboard(text)
    })
  } else {
    copyToClipboard(text)
  }
}

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text).then(() => {
    alertStore.success('Receipt copied to clipboard')
  }).catch(() => {
    alertStore.error('Failed to copy receipt')
  })
}

onMounted(() => {
  fetchPurchases()
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

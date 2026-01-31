<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header with Balance -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6 pb-12">
        <h1 class="text-white text-xl font-semibold mb-8">My Wallet</h1>

        <!-- Balance Card -->
        <div class="glass-balance rounded-lg p-6">
          <p class="text-white/70 text-sm mb-2">Total Balance</p>
          <div class="flex items-baseline gap-3 mb-6">
            <span class="text-white text-4xl font-bold">{{ formatCurrency(balance) }}</span>
            <button @click="toggleBalanceVisibility" class="p-2">
              <i v-if="balanceVisible" class="fa-solid fa-eye text-white/60 text-lg"></i>
              <i v-else class="fa-solid fa-eye-slash text-white/60 text-lg"></i>
            </button>
          </div>

          <div class="flex gap-3">
            <button
              @click="navigateTo('/add-money')"
              class="flex-1 bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-3 px-4 rounded-lg transition-all"
            >
              <i class="fa-solid fa-plus mr-2"></i>Topup
            </button>
            <button
              @click="navigateTo('/send-money')"
              class="flex-1 bg-white/10 hover:bg-white/20 text-white font-medium py-3 px-4 rounded-lg border border-white/20 transition-all"
            >
              <i class="fa-solid fa-paper-plane mr-2"></i>Send
            </button>
          </div>
        </div>
      </div>
    </div>
      <br>

    <!-- Quick Actions -->
    <div class="p-6  relative z-20 -mt-20">
      <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
        <div class="grid grid-cols-3 gap-4">
          <button
            @click="navigateTo('/transactions')"
            class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-gray-50 transition-colors"
          >
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <i class="fa-solid fa-receipt text-purple-600 text-xl"></i>
            </div>
            <span class="text-xs text-gray-700 text-center">Transactions</span>
          </button>

          <button
            @click="navigateTo('/add-money')"
            class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-gray-50 transition-colors"
          >
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <i class="fa-solid fa-wallet text-green-600 text-xl"></i>
            </div>
            <span class="text-xs text-gray-700 text-center">Top Up</span>
          </button>

          <button
            @click="navigateTo('/send-money')"
            class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-gray-50 transition-colors"
          >
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <i class="fa-solid fa-paper-plane text-blue-600 text-xl"></i>
            </div>
            <span class="text-xs text-gray-700 text-center">Transfer</span>
          </button>
        </div>
      </div>

      <!-- Wallet Info -->
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-[var(--fluxr-dark)] mb-4">Wallet Information</h2>

        <div class="space-y-4">
          <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-money-bill-wave text-blue-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Currency</p>
                <p class="text-xs text-gray-500">South African Rand</p>
              </div>
            </div>
            <span class="text-sm font-semibold text-gray-900">ZAR</span>
          </div>

          <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-shield-halved text-green-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Security</p>
                <p class="text-xs text-gray-500">Your wallet is protected</p>
              </div>
            </div>
            <i class="fa-solid fa-check-circle text-green-600"></i>
          </div>

          <div class="flex items-center justify-between py-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-clock text-purple-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Last Updated</p>
                <p class="text-xs text-gray-500">{{ lastUpdated }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Help Section -->
      <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <i class="fa-solid fa-info-circle text-blue-600 text-lg mt-0.5"></i>
          <div>
            <p class="text-blue-800 font-medium text-sm mb-1">Need Help?</p>
            <p class="text-blue-700 text-xs">
              Contact support for any issues with your wallet or transactions.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { walletService } from '@/helpers/wallet'

const router = useRouter()

const balance = ref(0)
const balanceVisible = ref(true)
const updatedAt = ref(null)

const lastUpdated = computed(() => {
  if (!updatedAt.value) return 'Just now'

  const date = new Date(updatedAt.value)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`

  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`

  return date.toLocaleDateString('en-ZA', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

const toggleBalanceVisibility = () => {
  balanceVisible.value = !balanceVisible.value
}

const formatCurrency = (amount) => {
  if (!balanceVisible.value) return '****'
  return new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR'
  }).format(amount || 0)
}

const navigateTo = (path) => {
  router.push(path)
}

const fetchBalance = async () => {
  try {
    const response = await walletService.getBalance()
    balance.value = response.balance || 0
    updatedAt.value = response.updated_at
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

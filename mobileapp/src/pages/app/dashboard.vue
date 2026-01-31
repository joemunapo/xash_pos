<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header with Balance -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <!-- Animated Background Orbs -->
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6 pb-20">
        <!-- Top Bar -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <p class="text-white/60 text-sm">Welcome back</p>
            <h1 class="text-white text-xl font-semibold">{{ user?.name || 'User' }}</h1>
          </div>
          <button @click="logout" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fa-solid fa-right-from-bracket text-white text-lg"></i>
          </button>
        </div>

        <!-- Balance Card -->
        <div class="glass-balance rounded-lg p-4">
          <p class="text-white/70 text-sm mb-2">Total Balance</p>
          <div class="flex items-baseline gap-2 mb-4">
            <span class="text-white text-4xl font-bold">{{ formatCurrency(balance) }}</span>
            <button @click="toggleBalanceVisibility" class="p-1">
              <i v-if="balanceVisible" class="fa-solid fa-eye text-white/60 text-lg"></i>
              <i v-else class="fa-solid fa-eye-slash text-white/60 text-lg"></i>
            </button>
          </div>

          <div class="flex gap-3">
            <button @click="navigateTo('/add-money')" class="flex-1 bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-2.5 px-4 rounded-lg transition-all">
              <i class="fa-solid fa-plus mr-2"></i>Add Money
            </button>
            <button @click="navigateTo('/send-money')" class="flex-1 bg-white/10 hover:bg-white/20 text-white font-medium py-2.5 px-4 rounded-lg border border-white/20 transition-all">
              <i class="fa-solid fa-paper-plane mr-2"></i>Send
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-12 relative z-20 space-y-6 pb-20">
      <!-- Service Categories -->
      <div
        v-for="category in serviceCategories"
        :key="category.title"
        class="bg-white rounded-lg p-5 shadow-sm"
      >
        <!-- Category Header -->
        <div class="flex items-center gap-2 mb-4">
          <i :class="`${category.icon} text-[var(--fluxr-dark)] text-lg`"></i>
          <h2 class="text-lg font-semibold text-[var(--fluxr-dark)]">{{ category.title }}</h2>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 gap-3">
          <button
            v-for="service in category.services"
            :key="service.title"
            @click="navigateTo(service.route)"
            class="flex items-center gap-4 p-4 rounded-xl hover:bg-gray-50 transition-all border border-transparent hover:border-gray-200 text-left"
          >
            <!-- Icon -->
            <div :class="`w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 ${service.bgColor}`">
              <i :class="`${service.icon} text-xl ${service.iconColor}`"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="text-sm font-semibold text-gray-900">{{ service.title }}</h3>
                <span
                  v-if="service.badge"
                  :class="`px-2 py-0.5 rounded-full text-xs font-medium text-white ${service.badgeColor}`"
                >
                  {{ service.badge }}
                </span>
              </div>
              <p class="text-xs text-gray-500 leading-relaxed">{{ service.description }}</p>
              <p v-if="service.stats" class="text-xs text-gray-400 mt-1">{{ service.stats }}</p>
            </div>

            <!-- Arrow -->
            <i class="fa-solid fa-chevron-right text-gray-400 text-sm flex-shrink-0"></i>
          </button>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="bg-white rounded-lg p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-[var(--fluxr-dark)]">Recent Activity</h2>
          <button @click="navigateTo('/transactions')" class="text-sm text-[var(--fluxr-green)] font-medium hover:text-[var(--fluxr-green-dark)]">
            View All
          </button>
        </div>
        <div v-if="recentTransactions.length === 0" class="text-center py-8 text-gray-400">
          <i class="fa-solid fa-receipt text-3xl mb-2"></i>
          <p class="text-sm">No recent transactions</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores'
import { useRouter } from 'vue-router'
import { walletService } from '@/helpers/wallet'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user)
const balance = ref(0)
const balanceVisible = ref(false)
const recentTransactions = ref([])

const serviceCategories = [
  {
    title: 'Mobile Services',
    icon: 'fa-solid fa-mobile-screen',
    services: [
      {
        title: 'Airtime',
        description: 'Buy airtime for all SA networks',
        icon: 'fa-solid fa-mobile',
        bgColor: 'bg-blue-100',
        iconColor: 'text-blue-600',
        badge: 'Popular',
        badgeColor: 'bg-blue-500',
        route: '/recharge',
        stats: null
      },
      {
        title: 'Data Bundles',
        description: 'Purchase data for Vodacom, MTN, Cell C',
        icon: 'fa-solid fa-wifi',
        bgColor: 'bg-cyan-100',
        iconColor: 'text-cyan-600',
        badge: 'New',
        badgeColor: 'bg-green-500',
        route: '/data',
        stats: null
      },
      {
        title: 'Zimbabwe Mobile',
        description: 'NetOne, Econet, Telecel services',
        icon: 'fa-solid fa-tower-cell',
        bgColor: 'bg-purple-100',
        iconColor: 'text-purple-600',
        badge: null,
        badgeColor: null,
        route: '/zimbabwe-mobile',
        stats: null
      }
    ]
  },
  {
    title: 'Utilities & Bills',
    icon: 'fa-solid fa-bolt',
    services: [
      {
        title: 'Electricity (SA)',
        description: 'Prepaid electricity tokens',
        icon: 'fa-solid fa-bolt',
        bgColor: 'bg-yellow-100',
        iconColor: 'text-yellow-600',
        badge: null,
        badgeColor: null,
        route: '/electricity',
        stats: null
      },
      {
        title: 'ZESA',
        description: 'Zimbabwe electricity payments',
        icon: 'fa-solid fa-plug',
        bgColor: 'bg-amber-100',
        iconColor: 'text-amber-600',
        badge: null,
        badgeColor: null,
        route: '/zesa',
        stats: null
      },
      {
        title: 'DStv',
        description: 'Pay DStv subscriptions',
        icon: 'fa-solid fa-tv',
        bgColor: 'bg-pink-100',
        iconColor: 'text-pink-600',
        badge: null,
        badgeColor: null,
        route: '/dstv',
        stats: null
      }
    ]
  },
  {
    title: 'Money & Vouchers',
    icon: 'fa-solid fa-wallet',
    services: [
      {
        title: 'Send Money',
        description: 'Transfer to other Fluxr users',
        icon: 'fa-solid fa-paper-plane',
        bgColor: 'bg-green-100',
        iconColor: 'text-green-600',
        badge: 'Instant',
        badgeColor: 'bg-green-500',
        route: '/send-money',
        stats: null
      },
      {
        title: 'Add Money',
        description: 'Top up your wallet',
        icon: 'fa-solid fa-wallet',
        bgColor: 'bg-indigo-100',
        iconColor: 'text-indigo-600',
        badge: null,
        badgeColor: null,
        route: '/add-money',
        stats: null
      },
      {
        title: 'Vouchers',
        description: '1Voucher, BluVoucher, gift cards',
        icon: 'fa-solid fa-ticket',
        bgColor: 'bg-orange-100',
        iconColor: 'text-orange-600',
        badge: null,
        badgeColor: null,
        route: '/vouchers',
        stats: null
      }
    ]
  }
]

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

const logout = async () => {
  await authStore.logout()
}

const fetchBalance = async () => {
  try {
    const response = await walletService.getBalance()
    balance.value = response.balance || 0
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

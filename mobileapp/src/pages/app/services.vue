<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6 pb-16">
        <h1 class="text-white text-2xl font-semibold mb-2">Services</h1>
        <p class="text-white/60 text-sm">Find and manage all your services</p>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-10 relative z-20">
      <!-- Search -->
      <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
        <div class="relative">
          <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search services..."
            class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
          />
        </div>
      </div>

      <!-- Search Results -->
      <div v-if="searchQuery" class="bg-white rounded-lg p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold text-[var(--fluxr-dark)] mb-4">Search Results</h2>

        <div v-if="filteredServices.length > 0" class="space-y-3">
          <button
            v-for="service in filteredServices"
            :key="service.id"
            @click="navigateTo(service.path)"
            class="w-full flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 transition-colors"
          >
            <div :class="`w-12 h-12 rounded-lg flex items-center justify-center ${service.bgColor}`">
              <i :class="`${service.icon} text-xl ${service.iconColor}`"></i>
            </div>
            <div class="flex-1 text-left">
              <h3 class="font-medium text-[var(--fluxr-dark)]">{{ service.name }}</h3>
              <p class="text-xs text-gray-500">{{ service.category }}</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-400 text-sm"></i>
          </button>
        </div>

        <div v-else class="text-center py-8">
          <i class="fa-solid fa-search text-gray-300 text-3xl mb-2"></i>
          <p class="text-gray-500">No services found</p>
        </div>
      </div>

      <!-- Most Popular -->
      <div v-if="!searchQuery" class="bg-white rounded-lg p-6 shadow-sm mb-6">
        <h2 class="text-lg font-semibold text-[var(--fluxr-dark)] mb-4">Most Popular</h2>
        <div class="grid grid-cols-4 gap-3">
          <button
            v-for="service in popularServices"
            :key="service.id"
            @click="navigateTo(service.path)"
            class="flex flex-col items-center gap-2 p-3 rounded-xl hover:bg-gray-50 transition-colors"
          >
            <div :class="`w-12 h-12 rounded-lg flex items-center justify-center ${service.bgColor}`">
              <i :class="`${service.icon} text-lg ${service.iconColor}`"></i>
            </div>
            <span class="text-xs text-gray-700 text-center leading-tight">{{ service.name }}</span>
          </button>
        </div>
      </div>

      <!-- Browse by Category -->
      <div v-if="!searchQuery">
        <h2 class="text-lg font-semibold text-[var(--fluxr-dark)] mb-4">Browse by Category</h2>

        <div v-for="category in categories" :key="category.name" class="bg-white rounded-lg p-6 shadow-sm mb-4">
          <div class="flex items-center gap-3 mb-4">
            <div :class="`w-10 h-10 rounded-lg flex items-center justify-center ${category.bgColor}`">
              <i :class="`${category.icon} text-lg ${category.iconColor}`"></i>
            </div>
            <h3 class="font-semibold text-[var(--fluxr-dark)]">{{ category.name }}</h3>
          </div>

          <div class="space-y-2">
            <button
              v-for="service in category.services"
              :key="service.id"
              @click="navigateTo(service.path)"
              class="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div :class="`w-10 h-10 rounded-lg flex items-center justify-center ${service.bgColor}`">
                <i :class="`${service.icon} text-sm ${service.iconColor}`"></i>
              </div>
              <div class="flex-1 text-left">
                <h4 class="font-medium text-[var(--fluxr-dark)] text-sm">{{ service.name }}</h4>
                <p class="text-xs text-gray-500">{{ service.description }}</p>
              </div>
              <i class="fa-solid fa-chevron-right text-gray-400 text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const searchQuery = ref('')

// All services data
const allServices = [
  // Telecom
  {
    id: 'airtime-data',
    name: 'Airtime & Data',
    description: 'Buy airtime and data bundles',
    category: 'Telecom',
    path: '/recharge',
    icon: 'fa-solid fa-mobile',
    bgColor: 'bg-blue-100',
    iconColor: 'text-blue-600',
    popular: true
  },
  // Utilities
  {
    id: 'electricity',
    name: 'Electricity',
    description: 'Buy prepaid electricity tokens',
    category: 'Utilities',
    path: '/electricity',
    icon: 'fa-solid fa-bolt',
    bgColor: 'bg-yellow-100',
    iconColor: 'text-yellow-600',
    popular: true
  },
  // Entertainment
  {
    id: 'dstv',
    name: 'DStv',
    description: 'Pay your DStv subscription',
    category: 'Entertainment',
    path: '/dstv',
    icon: 'fa-solid fa-tv',
    bgColor: 'bg-pink-100',
    iconColor: 'text-pink-600',
    popular: true
  },
  {
    id: 'gotv',
    name: 'GOtv',
    description: 'Pay your GOtv subscription',
    category: 'Entertainment',
    path: '/bill-payments',
    icon: 'fa-solid fa-satellite-dish',
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600',
    popular: false
  },
  // Money Transfer
  {
    id: 'send-money',
    name: 'Send Money',
    description: 'Transfer to other users',
    category: 'Money Transfer',
    path: '/send-money',
    icon: 'fa-solid fa-paper-plane',
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600',
    popular: true
  },
  {
    id: 'add-money',
    name: 'Add Money',
    description: 'Top up your wallet',
    category: 'Money Transfer',
    path: '/add-money',
    icon: 'fa-solid fa-wallet',
    bgColor: 'bg-indigo-100',
    iconColor: 'text-indigo-600',
    popular: false
  },
  // Vouchers
  {
    id: 'vouchers',
    name: 'Vouchers',
    description: 'Buy and redeem vouchers',
    category: 'Vouchers',
    path: '/vouchers',
    icon: 'fa-solid fa-ticket',
    bgColor: 'bg-orange-100',
    iconColor: 'text-orange-600',
    popular: false
  }
]

// Popular services
const popularServices = computed(() => {
  return allServices.filter(s => s.popular)
})

// Categories with services
const categories = computed(() => {
  const categoryMap = {}

  allServices.forEach(service => {
    if (!categoryMap[service.category]) {
      categoryMap[service.category] = {
        name: service.category,
        icon: getCategoryIcon(service.category),
        bgColor: getCategoryBgColor(service.category),
        iconColor: getCategoryIconColor(service.category),
        services: []
      }
    }
    categoryMap[service.category].services.push(service)
  })

  return Object.values(categoryMap)
})

// Filtered services for search
const filteredServices = computed(() => {
  if (!searchQuery.value) return []

  const query = searchQuery.value.toLowerCase()
  return allServices.filter(s =>
    s.name.toLowerCase().includes(query) ||
    s.description.toLowerCase().includes(query) ||
    s.category.toLowerCase().includes(query)
  )
})

const getCategoryIcon = (category) => {
  const icons = {
    'Telecom': 'fa-solid fa-signal',
    'Utilities': 'fa-solid fa-plug',
    'Entertainment': 'fa-solid fa-film',
    'Money Transfer': 'fa-solid fa-money-bill-transfer',
    'Vouchers': 'fa-solid fa-gift'
  }
  return icons[category] || 'fa-solid fa-circle'
}

const getCategoryBgColor = (category) => {
  const colors = {
    'Telecom': 'bg-blue-100',
    'Utilities': 'bg-yellow-100',
    'Entertainment': 'bg-pink-100',
    'Money Transfer': 'bg-green-100',
    'Vouchers': 'bg-orange-100'
  }
  return colors[category] || 'bg-gray-100'
}

const getCategoryIconColor = (category) => {
  const colors = {
    'Telecom': 'text-blue-600',
    'Utilities': 'text-yellow-600',
    'Entertainment': 'text-pink-600',
    'Money Transfer': 'text-green-600',
    'Vouchers': 'text-orange-600'
  }
  return colors[category] || 'text-gray-600'
}

const navigateTo = (path) => {
  router.push(path)
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

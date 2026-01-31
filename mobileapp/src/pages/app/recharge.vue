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
          <div class="mb-5">
            <h1 class="text-white text-xl font-semibold">Airtime & Data</h1>
            <p class="text-white/60 text-sm">Select your network provider</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-12 relative z-20">
      <div class="bg-white rounded-lg p-6 shadow-sm">
        <!-- Country Selection -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-[var(--fluxr-dark)] mb-2">
            Select Country
          </label>
          <div class="relative">
            <select
              v-model="selectedCountry"
              class="w-full px-4 py-3 pr-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none appearance-none"
              :class="{
                'text-gray-400': !selectedCountry,
                'text-[var(--fluxr-dark)]': selectedCountry
              }"
            >
              <option value="" disabled hidden>Select Country</option>
              <option v-for="country in countries" :key="country.code" :value="country.code" class="text-[var(--fluxr-dark)]">
                {{ country.flag }} {{ country.name }}
              </option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
              <i class="fa-solid fa-chevron-down text-gray-400 text-sm"></i>
            </div>
          </div>
        </div>

        <!-- Search -->
        <div v-if="selectedCountry" class="mb-6">
          <div class="relative">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search provider..."
              class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none"
            />
          </div>
        </div>

        <!-- Providers List -->
        <div v-if="selectedCountry" class="space-y-3">
          <button
            v-for="provider in filteredProviders"
            :key="provider.id"
            @click="selectProvider(provider)"
            class="w-full flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-green)]/5 transition-all"
          >
            <div :class="`w-12 h-12 rounded-lg flex items-center justify-center ${provider.bgColor}`">
              <i :class="`${provider.icon} text-xl ${provider.iconColor}`"></i>
            </div>
            <div class="flex-1 text-left">
              <h3 class="font-semibold text-[var(--fluxr-dark)]">{{ provider.name }}</h3>
              <p class="text-sm text-gray-500">{{ provider.description }}</p>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-400"></i>
          </button>

          <!-- No results -->
          <div v-if="filteredProviders.length === 0" class="text-center py-8">
            <i class="fa-solid fa-search text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">No providers found</p>
          </div>
        </div>

        <!-- Select Country Message -->
        <div v-else class="text-center py-12">
          <i class="fa-solid fa-globe text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-semibold text-[var(--fluxr-dark)] mb-2">Select a Country</h3>
          <p class="text-gray-500">Choose your country to see available providers</p>
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
const selectedCountry = ref('')

const countries = [
  { code: 'ZW', name: 'Zimbabwe', flag: '🇿🇼', currency: 'USD' },
  { code: 'ZA', name: 'South Africa', flag: '🇿🇦', currency: 'ZAR' }
]

const providers = [
  {
    id: 'econet',
    name: 'Econet',
    description: 'Econet Wireless Zimbabwe',
    country: 'ZW',
    icon: 'fa-solid fa-signal',
    bgColor: 'bg-red-100',
    iconColor: 'text-red-600'
  },
  {
    id: 'netone',
    name: 'NetOne',
    description: 'NetOne Cellular Zimbabwe',
    country: 'ZW',
    icon: 'fa-solid fa-tower-cell',
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600'
  },
  {
    id: 'kazang',
    name: 'Kazang',
    description: 'South African Networks',
    country: 'ZA',
    icon: 'fa-solid fa-wifi',
    bgColor: 'bg-blue-100',
    iconColor: 'text-blue-600'
  }
]

const filteredProviders = computed(() => {
  // First filter by country
  let countryFiltered = providers.filter(p => p.country === selectedCountry.value)

  // Then filter by search query
  if (!searchQuery.value) return countryFiltered

  const query = searchQuery.value.toLowerCase()
  return countryFiltered.filter(p =>
    p.name.toLowerCase().includes(query) ||
    p.description.toLowerCase().includes(query)
  )
})

const goBack = () => {
  router.back()
}

const selectProvider = (provider) => {
  router.push(`/recharge/${provider.id}`)
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

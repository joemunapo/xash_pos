<template>
  <div class="relative" ref="currencySelectorRef">
    <button
      @click="showDropdown = !showDropdown"
      class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors"
      :class="size === 'sm' ? 'text-xs' : 'text-sm'"
    >
      <span class="font-semibold" :class="getCurrencyColor(selectedCurrency)">
        {{ selectedCurrency }}
      </span>
      <i class="fas fa-chevron-down text-xs"></i>
    </button>

    <!-- Currency Dropdown -->
    <Transition name="fade-drop">
      <div
        v-if="showDropdown"
        class="absolute top-full mt-2 right-0 bg-white dark:bg-slate-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden z-50 min-w-[160px]"
      >
        <button
          v-for="currency in currencies"
          :key="currency.code"
          @click="selectCurrency(currency.code)"
          class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors flex items-center justify-between"
          :class="{ 'bg-green-50 dark:bg-green-900/20': selectedCurrency === currency.code }"
        >
          <div class="flex items-center gap-3">
            <span class="text-lg">{{ currency.symbol }}</span>
            <div>
              <p class="font-medium text-sm" :class="getCurrencyColor(currency.code)">
                {{ currency.code }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ currency.name }}</p>
            </div>
          </div>
          <i
            v-if="selectedCurrency === currency.code"
            class="fas fa-check text-green-600"
          ></i>
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: 'USD'
  },
  size: {
    type: String,
    default: 'md' // sm, md
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const showDropdown = ref(false);
const selectedCurrency = ref(props.modelValue);
const currencySelectorRef = ref(null);

const currencies = [
  { code: 'USD', name: 'US Dollar', symbol: '$' },
  { code: 'ZIG', name: 'Zimbabwe Gold', symbol: 'ZG' },
  { code: 'ZAR', name: 'South African Rand', symbol: 'R' }
];

function selectCurrency(code) {
  selectedCurrency.value = code;
  emit('update:modelValue', code);
  emit('change', code);
  showDropdown.value = false;
  
  // Save to localStorage
  localStorage.setItem('selectedCurrency', code);
}

function getCurrencyColor(code) {
  const colors = {
    'USD': 'text-green-600',
    'ZIG': 'text-blue-600',
    'ZAR': 'text-orange-600'
  };
  return colors[code] || 'text-gray-600';
}

function handleClickOutside(event) {
  if (currencySelectorRef.value && !currencySelectorRef.value.contains(event.target)) {
    showDropdown.value = false;
  }
}

onMounted(() => {
  // Load saved currency preference
  const saved = localStorage.getItem('selectedCurrency');
  if (saved && currencies.some(c => c.code === saved)) {
    selectedCurrency.value = saved;
    emit('update:modelValue', saved);
  }
  
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.fade-drop-enter-active {
  transition: all 0.2s ease;
}

.fade-drop-leave-active {
  transition: all 0.15s ease;
}

.fade-drop-enter-from,
.fade-drop-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300',
      active
        ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300'
        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:text-gray-900 dark:hover:text-gray-200 hover:translate-x-1'
    ]"
  >
    <i v-if="icon" :class="[
      `fas ${icon}`,
      active ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 dark:text-gray-500'
    ]"></i>
    <span class="flex-1"><slot /></span>
    <span
      v-if="badge"
      class="px-2 py-0.5 text-xs font-bold rounded-full bg-brand-500 text-white min-w-[20px] text-center"
    >
      {{ badge }}
    </span>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: String,
    default: '',
  },
  badge: {
    type: [String, Number],
    default: null,
  },
});
</script>

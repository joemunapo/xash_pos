<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300',
      active
        ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300'
        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:text-gray-900 dark:hover:text-gray-200 hover:translate-x-1'
    ]"
  >
    <i v-if="icon" :class="[
      `fas ${icon}`,
      active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'
    ]"></i>
    <span class="flex-1"><slot /></span>
    <span
      v-if="badge"
      class="px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-500 text-white min-w-[20px] text-center"
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

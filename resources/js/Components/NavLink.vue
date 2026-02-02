<template>
  <div class="relative" ref="navLinkRef" @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave">
    <Link
      :href="href"
      :class="[
        'relative flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-sm transition-all duration-300 overflow-hidden',
        active
          ? 'bg-gradient-to-r from-brand-500 to-brand-500 text-white shadow-lg shadow-brand-500/30'
          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:translate-x-1',
        miniSidebar ? 'justify-center' : ''
      ]"
    >
      <div :class="[
        'flex items-center justify-center',
        active ? 'text-white' : 'text-gray-500 dark:text-gray-400'
      ]">
        <i :class="`fas ${icon} text-base`"></i>
      </div>
      <span v-if="!miniSidebar" class="flex-1 font-medium">
        <slot />
      </span>

      <!-- Active indicator -->
      <div
        v-if="active && !miniSidebar"
        class="w-1 h-6 bg-white/30 rounded-full"
      ></div>
    </Link>

    <!-- Tooltip for mini sidebar -->
    <teleport to="body">
      <transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 scale-95 -translate-x-2"
        enter-to-class="opacity-100 scale-100 translate-x-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 scale-100 translate-x-0"
        leave-to-class="opacity-0 scale-95 -translate-x-2"
      >
        <div
          v-if="showTooltip && miniSidebar && $slots.default"
          :style="{ top: `${tooltipPosition.top}px`, left: `${tooltipPosition.left}px` }"
          class="fixed z-50 px-3 py-2 bg-gray-900 dark:bg-slate-800 text-white text-sm rounded-lg shadow-2xl whitespace-nowrap border border-gray-700 pointer-events-none"
        >
          <slot />
          <div class="absolute right-full top-1/2 -translate-y-1/2 border-[6px] border-transparent border-r-gray-900 dark:border-r-slate-800"></div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup>
import { inject, ref } from 'vue';
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
    required: true,
  },
});

const miniSidebar = inject('miniSidebar', false);
const activeTooltip = inject('activeTooltip', ref(null));
const navLinkRef = ref(null);
const showTooltip = ref(false);
const tooltipPosition = ref({ top: 0, left: 0 });
const tooltipId = Symbol(); // Unique ID for this tooltip
let tooltipTimeout = null;

const handleMouseEnter = (event) => {
  if (miniSidebar.value) {
    clearTimeout(tooltipTimeout);
    
    // Immediately hide any other active tooltip
    if (activeTooltip.value && activeTooltip.value !== tooltipId) {
      // Signal to close other tooltips
    }
    
    const rect = event.currentTarget.getBoundingClientRect();
    tooltipPosition.value = {
      top: rect.top,
      left: rect.right + 12,
    };
    showTooltip.value = true;
    activeTooltip.value = tooltipId;
  }
};

const handleMouseLeave = () => {
  if (miniSidebar.value) {
    tooltipTimeout = setTimeout(() => {
      showTooltip.value = false;
      if (activeTooltip.value === tooltipId) {
        activeTooltip.value = null;
      }
    }, 100);
  }
};
</script>

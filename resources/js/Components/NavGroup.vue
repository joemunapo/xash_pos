<template>
  <div>
    <!-- Group Header -->
    <button
      @click="toggleExpanded"
      @mouseenter="handleMouseEnter"
      @mouseleave="handleMouseLeave"
      :class="[
        'w-full flex items-center justify-between px-3 py-3 rounded-lg font-medium text-sm transition-all duration-300 overflow-hidden',
        isAnyChildActive
          ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400'
          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-800/50 hover:translate-x-1'
      ]"
    >
      <div class="flex items-center gap-3">
        <div :class="[
          'flex items-center justify-center',
          isAnyChildActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'
        ]">
          <i :class="`fas ${icon} text-base`"></i>
        </div>
        <span v-if="!miniSidebar" class="font-medium">{{ title }}</span>
      </div>
      <i
        v-if="!miniSidebar"
        :class="[
          'fas fa-chevron-down text-xs transition-transform duration-300',
          isExpanded ? 'rotate-180' : '',
          isAnyChildActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'
        ]"
      ></i>
    </button>

    <!-- Submenu Items -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 max-h-0 -translate-y-2"
      enter-to-class="opacity-100 max-h-[500px] translate-y-0"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 max-h-[500px] translate-y-0"
      leave-to-class="opacity-0 max-h-0 -translate-y-2"
    >
      <div v-show="isExpanded && !miniSidebar" class="mt-1 ml-3 pl-5 border-l-2 border-gray-200 dark:border-slate-700 space-y-1 overflow-hidden">
        <slot />
      </div>
    </transition>

    <!-- Mini Sidebar Tooltip Menu -->
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
          v-if="showTooltipMenu && miniSidebar"
          @mouseenter="onTooltipMouseEnter"
          @mouseleave="onTooltipMouseLeave"
          :style="{ top: `${tooltipPosition.top}px`, left: `${tooltipPosition.left}px` }"
          class="fixed z-50 min-w-[200px] bg-white dark:bg-slate-800 rounded-lg shadow-2xl border border-gray-200 dark:border-slate-700 py-2"
        >
          <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-slate-700">
            {{ title }}
          </div>
          <div class="py-1">
            <slot name="mini" />
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, inject } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  activePatterns: {
    type: Array,
    default: () => [],
  },
});

const miniSidebar = inject('miniSidebar', ref(false));
const activeTooltip = inject('activeTooltip', ref(null));
const page = usePage();

const isExpanded = ref(false);
const showTooltipMenu = ref(false);
const keepTooltipOpen = ref(false);
const tooltipPosition = ref({ top: 0, left: 0 });
const tooltipId = Symbol(); // Unique ID for this popover
let tooltipTimeout = null;

// Check if any child route is active
const isAnyChildActive = computed(() => {
  return props.activePatterns.some(pattern => {
    if (typeof pattern === 'string') {
      return page.url.startsWith(pattern);
    }
    return false;
  });
});

// Auto-expand if a child is active
watch(isAnyChildActive, (newVal) => {
  if (newVal && !miniSidebar.value) {
    isExpanded.value = true;
  }
}, { immediate: true });

// Collapse when sidebar is minimized
watch(miniSidebar, (newVal) => {
  if (newVal) {
    isExpanded.value = false;
  }
});

const toggleExpanded = (event) => {
  if (miniSidebar.value) {
    // Show tooltip menu
    const rect = event.currentTarget.getBoundingClientRect();
    tooltipPosition.value = {
      top: rect.top,
      left: rect.right + 8,
    };
    showTooltipMenu.value = true;
  } else {
    isExpanded.value = !isExpanded.value;
  }
};

const hideTooltipMenu = () => {
  tooltipTimeout = setTimeout(() => {
    if (!keepTooltipOpen.value) {
      showTooltipMenu.value = false;
      if (activeTooltip.value === tooltipId) {
        activeTooltip.value = null;
      }
    }
    keepTooltipOpen.value = false;
  }, 100);
};

const handleMouseEnter = (event) => {
  if (miniSidebar.value) {
    clearTimeout(tooltipTimeout);
    
    // Immediately hide any other active tooltip/popover
    if (activeTooltip.value && activeTooltip.value !== tooltipId) {
      // Signal to close other tooltips
    }
    
    const rect = event.currentTarget.getBoundingClientRect();
    tooltipPosition.value = {
      top: rect.top,
      left: rect.right + 12,
    };
    showTooltipMenu.value = true;
    activeTooltip.value = tooltipId;
  }
};

const handleMouseLeave = () => {
  if (miniSidebar.value) {
    hideTooltipMenu();
  }
};

const onTooltipMouseEnter = () => {
  clearTimeout(tooltipTimeout);
  keepTooltipOpen.value = true;
};

const onTooltipMouseLeave = () => {
  keepTooltipOpen.value = false;
  hideTooltipMenu();
};
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-100">
    <!-- Search Bar -->
    <div v-if="searchable" class="p-4 border-b border-gray-100">
      <div class="relative">
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
        />
        <button
          v-if="searchQuery"
          @click="searchQuery = ''"
          class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-primary"></i>
    </div>

    <!-- Empty State -->
    <div v-else-if="!filteredData || filteredData.length === 0" class="text-center py-12 text-gray-500">
      <i :class="emptyIcon" class="text-5xl mb-3 text-gray-300"></i>
      <p class="text-lg">{{ searchQuery ? 'No results found' : emptyMessage }}</p>
      <p v-if="searchQuery" class="text-sm text-gray-400 mt-2">Try adjusting your search</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-gray-50/50">
          <tr class="border-b border-gray-100">
            <th
              v-for="column in columns"
              :key="column.key"
              @click="column.sortable ? sort(column.key) : null"
              :class="[
                'py-4 px-6 text-sm font-semibold text-gray-600',
                column.align === 'right' ? 'text-right' : column.align === 'center' ? 'text-center' : 'text-left',
                column.sortable ? 'cursor-pointer hover:bg-gray-100/50 transition-colors' : ''
              ]"
            >
              <div class="flex items-center gap-2" :class="column.align === 'right' ? 'justify-end' : column.align === 'center' ? 'justify-center' : ''">
                <span>{{ column.label }}</span>
                <i
                  v-if="column.sortable"
                  :class="[
                    'fas text-xs',
                    sortKey === column.key && sortOrder === 'asc' ? 'fa-sort-up text-primary' :
                    sortKey === column.key && sortOrder === 'desc' ? 'fa-sort-down text-primary' :
                    'fa-sort text-gray-400'
                  ]"
                ></i>
              </div>
            </th>
            <th v-if="actions" class="text-center py-4 px-6 text-sm font-semibold text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, index) in paginatedData"
            :key="index"
            class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors"
          >
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'py-4 px-6',
                column.align === 'right' ? 'text-right' : column.align === 'center' ? 'text-center' : 'text-left'
              ]"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="getNestedValue(row, column.key)">
                <span v-if="column.format" v-html="column.format(getNestedValue(row, column.key), row)"></span>
                <span v-else>{{ getNestedValue(row, column.key) }}</span>
              </slot>
            </td>
            <td v-if="actions" class="py-4 px-6 text-center">
              <slot name="actions" :row="row"></slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && filteredData.length > 0" class="p-4 border-t border-gray-100">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Page Info -->
        <div class="text-sm text-gray-600">
          Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredData.length }} entries
          <span v-if="searchQuery && filteredData.length < props.data.length" class="text-gray-400">
            (filtered from {{ props.data.length }} total)
          </span>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center gap-2">
          <button
            @click="goToPage(1)"
            :disabled="currentPage === 1"
            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors"
            :class="currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'"
          >
            <i class="fas fa-angle-double-left"></i>
          </button>
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors"
            :class="currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'"
          >
            <i class="fas fa-angle-left"></i>
          </button>

          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page)"
              class="px-4 py-2 border rounded-lg text-sm font-medium transition-colors"
              :class="currentPage === page ? 'bg-primary text-white border-primary' : 'text-gray-700 hover:bg-gray-50'"
            >
              {{ page }}
            </button>
            <span v-else class="px-2 text-gray-400">...</span>
          </template>

          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors"
            :class="currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'"
          >
            <i class="fas fa-angle-right"></i>
          </button>
          <button
            @click="goToPage(totalPages)"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors"
            :class="currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-50'"
          >
            <i class="fas fa-angle-double-right"></i>
          </button>
        </div>

        <!-- Per Page Selector -->
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">Per page:</label>
          <div class="relative">
            <select
              v-model="perPage"
              @change="currentPage = 1"
              class="appearance-none pl-3 pr-8 py-2 bg-gray-50 border-0 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white transition-all cursor-pointer"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button (for infinite scroll) -->
    <div v-if="loadMore && hasMore && !loading" class="p-4 border-t">
      <button
        @click="$emit('load-more')"
        class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium"
      >
        Load More
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  columns: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Boolean,
    default: true
  },
  loadMore: {
    type: Boolean,
    default: false
  },
  hasMore: {
    type: Boolean,
    default: false
  },
  initialPerPage: {
    type: Number,
    default: 10
  },
  actions: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'No data found'
  },
  emptyIcon: {
    type: String,
    default: 'fas fa-inbox'
  },
  searchable: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  searchKeys: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['load-more']);

const sortKey = ref('');
const sortOrder = ref('asc');
const currentPage = ref(1);
const perPage = ref(props.initialPerPage);
const searchQuery = ref('');

function getNestedValue(obj, path) {
  return path.split('.').reduce((value, key) => value?.[key], obj);
}

function sort(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
}

const filteredData = computed(() => {
  if (!searchQuery.value) {
    return props.data;
  }

  const query = searchQuery.value.toLowerCase();

  return props.data.filter(row => {
    // If specific search keys are provided, search only those
    if (props.searchKeys.length > 0) {
      return props.searchKeys.some(key => {
        const value = getNestedValue(row, key);
        return String(value || '').toLowerCase().includes(query);
      });
    }

    // Otherwise, search all columns
    return props.columns.some(column => {
      const value = getNestedValue(row, column.key);
      return String(value || '').toLowerCase().includes(query);
    });
  });
});

const sortedData = computed(() => {
  if (!sortKey.value) {
    return filteredData.value;
  }

  return [...filteredData.value].sort((a, b) => {
    const aVal = getNestedValue(a, sortKey.value);
    const bVal = getNestedValue(b, sortKey.value);

    let comparison = 0;
    if (aVal > bVal) comparison = 1;
    if (aVal < bVal) comparison = -1;

    return sortOrder.value === 'asc' ? comparison : -comparison;
  });
});

const totalPages = computed(() => {
  if (!props.pagination) return 1;
  return Math.ceil(sortedData.value.length / perPage.value);
});

const startIndex = computed(() => {
  if (!props.pagination) return 0;
  return (currentPage.value - 1) * perPage.value;
});

const endIndex = computed(() => {
  if (!props.pagination) return sortedData.value.length;
  return Math.min(startIndex.value + perPage.value, sortedData.value.length);
});

const paginatedData = computed(() => {
  if (!props.pagination) return sortedData.value;
  return sortedData.value.slice(startIndex.value, endIndex.value);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i);
      pages.push('...');
      pages.push(total);
    } else if (current >= total - 3) {
      pages.push(1);
      pages.push('...');
      for (let i = total - 4; i <= total; i++) pages.push(i);
    } else {
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) pages.push(i);
      pages.push('...');
      pages.push(total);
    }
  }

  return pages;
});

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

// Reset to page 1 when data or search changes
watch(() => props.data, () => {
  currentPage.value = 1;
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

// Export for parent components if needed
defineExpose({
  currentPage,
  perPage,
  totalPages,
  goToPage,
  searchQuery
});
</script>

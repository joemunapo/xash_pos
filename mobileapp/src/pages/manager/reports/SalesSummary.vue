<template>
  <div class="space-y-4 md:space-y-6">
    <!-- Page Header with Filter Toggle -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg sm:text-2xl font-bold text-gray-900">Sales Summary Report</h1>
        <p class="text-xs sm:text-sm text-gray-600 mt-1">Analyze your sales performance</p>
      </div>
      <div class="flex items-center gap-2">
        <!-- Export Dropdown -->
        <div class="relative">
          <button
            @click="showExportMenu = !showExportMenu"
            class="px-3 md:px-4 py-2 text-xs md:text-sm font-medium rounded-lg transition-all duration-200 flex items-center gap-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 shadow-sm"
          >
            <i class="fas fa-download"></i>
            <span class="hidden sm:inline">Export</span>
            <i class="fas fa-chevron-down text-xs"></i>
          </button>
          
          <!-- Export Menu -->
          <div
            v-if="showExportMenu"
            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
          >
            <button
              @click="exportReport('pdf')"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors"
            >
              <i class="fas fa-file-pdf text-red-500 w-5"></i>
              <span>Export as PDF</span>
            </button>
            <button
              @click="exportReport('csv')"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors border-t border-gray-100"
            >
              <i class="fas fa-file-csv text-success w-5"></i>
              <span>Export as CSV</span>
            </button>
            <button
              @click="exportReport('excel')"
              class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors border-t border-gray-100"
            >
              <i class="fas fa-file-excel text-success w-5"></i>
              <span>Export as Excel</span>
            </button>
          </div>
        </div>
        
        <!-- Filter Toggle Button -->
        <button
          @click="showFilters = !showFilters"
          class="px-3 md:px-4 py-2 text-xs md:text-sm font-medium rounded-lg transition-all duration-200 flex items-center gap-2 bg-primary text-white hover:bg-primary-dark shadow-sm"
        >
          <i class="fas fa-filter"></i>
          <span class="hidden sm:inline">Filters</span>
          <i class="fas fa-chevron-down text-xs" :class="showFilters ? 'fa-rotate-180' : ''"></i>
        </button>
      </div>
    </div>

    <!-- Filters Bottom Modal -->
    <transition name="modal-backdrop">
      <div
        v-if="showFilters"
        @click="showFilters = false"
        class="fixed inset-0 bg-black/30 z-40"
        style="backdrop-filter: blur(4px);"
      ></div>
    </transition>

    <transition name="slide-up">
      <div
        v-if="showFilters"
        class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl border-t p-4 md:p-6 z-50 max-h-[85vh] overflow-y-auto"
        style="border-color: var(--fluxr-green-light); box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);"
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base md:text-lg font-semibold text-gray-900">Filters</h2>
          <button
            @click="showFilters = false"
            class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <i class="fas fa-times text-gray-500"></i>
          </button>
        </div>
        
        <!-- Date Range - Single Row on Mobile -->
        <div class="grid grid-cols-2 gap-2 md:gap-4 mb-3 md:mb-4">
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <flat-pickr
              v-model="filters.start_date"
              :config="datePickerConfig"
              class="w-full px-2 md:px-3 py-1.5 md:py-2 text-xs md:text-sm border border-gray300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Select start date"
            />
          </div>
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">End Date</label>
            <flat-pickr
              v-model="filters.end_date"
              :config="datePickerConfig"
              class="w-full px-2 md:px-3 py-1.5 md:py-2 text-xs md:text-sm border border-gray300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="Select end date"
            />
          </div>
        </div>

        <!-- Time Range with Dropdown -->
        <div class="mb-3 md:mb-4">
          <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Time Range</label>
          <div class="relative">
            <button
              @click="showTimeDropdown = !showTimeDropdown"
              type="button"
              class="w-full md:w-auto px-3 py-1.5 md:py-2 text-xs md:text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary flex items-center justify-between"
            >
              <span>{{ timeRangeLabel }}</span>
              <i class="fas fa-chevron-down ml-2 text-xs"></i>
            </button>
            
            <!-- Time Dropdown -->
            <div
              v-if="showTimeDropdown"
              class="absolute z-10 mt-1 w-full md:w-64 bg-white border border-gray-300 rounded-lg shadow-lg overflow-hidden"
            >
              <button
                @click="selectTimeRange('all_day')"
                type="button"
                class="w-full px-3 py-2 text-left text-xs md:text-sm hover:bg-gray-100 transition-colors"
                :class="{ 'text-white': filters.time_range === 'all_day' }"
                :style="filters.time_range === 'all_day' ? 'background: var(--fluxr-primary);' : ''"
              >
                <i class="fas fa-clock mr-2"></i>
                All Day
              </button>
              <button
                @click="selectTimeRange('custom')"
                type="button"
                class="w-full px-3 py-2 text-left text-xs md:text-sm hover:bg-gray-100 transition-colors"
                :class="{ 'text-white': filters.time_range === 'custom' }"
                :style="filters.time_range === 'custom' ? 'background: var(--fluxr-primary);' : ''"
              >
                <i class="fas fa-clock mr-2"></i>
                Custom Time
              </button>
            </div>
          </div>
        </div>

        <!-- Custom Time Inputs (shown when Custom is selected) -->
        <div v-if="filters.time_range === 'custom'" class="grid grid-cols-2 gap-2 md:gap-4 mb-3 md:mb-4">
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Start Time</label>
            <input
              v-model="filters.start_time"
              type="time"
              class="w-full px-2 md:px-3 py-1.5 md:py-2 text-xs md:text-sm border border-gray300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">End Time</label>
            <input
              v-model="filters.end_time"
              type="time"
              class="w-full px-2 md:px-3 py-1.5 md:py-2 text-xs md:text-sm border border-gray300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
        </div>

        <!-- Branch, Cashier & Product Multi-select -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-4">
          <!-- Branch Multi-select -->
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Branches</label>
            <v-select
              v-model="selectedBranches"
              :options="branches"
              multiple
              label="name"
              :reduce="branch => branch"
              placeholder="Search and select branches"
              class="custom-v-select"
            />
          </div>

          <!-- Cashier Multi-select -->
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Cashiers</label>
            <v-select
              v-model="selectedCashiers"
              :options="cashiers"
              multiple
              label="name"
              :reduce="cashier => cashier"
              placeholder="Search and select cashiers"
              class="custom-v-select"
            />
          </div>
        </div>

        <!-- Product Multi-select -->
        <div class="mb-4">
          <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Products (Optional)</label>
          <v-select
            v-model="selectedProducts"
            :options="products"
            multiple
            label="name"
            :reduce="product => product"
            placeholder="Search and select products"
            class="custom-v-select"
          >
            <template #option="{ name, sku }">
              <div class="flex flex-col">
                <span class="font-medium">{{ name }}</span>
                <span class="text-xs text-gray-500">SKU: {{ sku }}</span>
              </div>
            </template>
          </v-select>
        </div>

        <!-- Apply Filters Button -->
        <div class="flex gap-2 sticky bottom-0 bg-white pt-4 -mx-4 md:-mx-6 px-4 md:px-6 pb-2 border-t border-gray-100">
          <button
            @click="applyFiltersAndClose"
            :disabled="loading"
            class="flex-1 px-3 md:px-4 py-2 md:py-2.5 text-xs md:text-sm font-medium text-white rounded-lg hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            style="background: linear-gradient(to bottom right, var(--fluxr-primary), var(--fluxr-primary-dark));"
          >
            <i class="fas fa-check mr-1"></i>
            {{ loading ? 'Loading...' : 'Apply Filters' }}
          </button>
          <button
            @click="resetFilters"
            class="px-3 md:px-4 py-2 md:py-2.5 text-xs md:text-sm font-medium bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition-colors"
          >
            <i class="fas fa-redo mr-1"></i>
            Reset
          </button>
        </div>
      </div>
    </transition>

    <!-- Metrics Card -->
    <div v-if="!loading" class="bg-white rounded-lg border border-gray-200 p-4 md:p-6">
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6">
        <!-- Gross Sales -->
        <div class="text-center md:border-r border-gray-200 last:border-r-0">
          <p class="text-xs md:text-sm text-gray-500 mb-1">Gross Sales</p>
          <p class="text-base md:text-lg font-bold" style="color: var(--fluxr-primary);">${{ formatCurrency(metrics.gross_sales) }}</p>
        </div>

        <!-- Discounts -->
        <div class="text-center md:border-r border-gray-200 last:border-r-0">
          <p class="text-xs md:text-sm text-gray-500 mb-1">Discounts</p>
          <p class="text-base md:text-lg font-bold" style="color: var(--fluxr-primary);">${{ formatCurrency(metrics.discounts) }}</p>
        </div>

        <!-- Net Sales -->
        <div class="text-center md:border-r border-gray-200 last:border-r-0 col-span-2 md:col-span-1 border-t md:border-t-0 pt-4 md:pt-0">
          <p class="text-xs md:text-sm text-gray-500 mb-1">Net Sales</p>
          <p class="text-base md:text-lg font-bold" style="color: var(--fluxr-primary);">${{ formatCurrency(metrics.net_sales) }}</p>
        </div>

        <!-- Gross Profits -->
        <div class="text-center md:border-r border-gray-200 last:border-r-0">
          <p class="text-xs md:text-sm text-gray-500 mb-1">Gross Profits</p>
          <p class="text-base md:text-lg font-bold" style="color: var(--fluxr-primary);">${{ formatCurrency(metrics.gross_profits) }}</p>
        </div>

        <!-- Refunds -->
        <div class="text-center">
          <p class="text-xs md:text-sm text-gray-500 mb-1">Refunds</p>
          <p class="text-base md:text-lg font-bold text-red-600">${{ formatCurrency(metrics.refunds) }}</p>
        </div>
      </div>
    </div>

    <!-- Loading Skeleton for Metrics -->
    <div v-else class="flex items-center justify-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl" style="color: var(--fluxr-primary);"></i>
    </div>

    <!-- Bar Chart Section (Desktop/Tablet Only) -->
    <div v-if="!loading && dailyData.length > 0" class="hidden md:block bg-white rounded-lg border border-gray-200 p-4 md:p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base md:text-lg font-semibold text-gray-900">Daily Sales Trend</h2>
        <div class="text-xs text-gray-500">
          <i class="fas fa-chart-bar mr-1"></i>
          {{ dailyData.length }} days
        </div>
      </div>
      <div style="height: 400px;">
        <Bar :data="chartData" :options="chartOptions" />
      </div>
    </div>

    <!-- Chart Loading Skeleton (Desktop/Tablet Only) -->
    <div v-else-if="loading" class="hidden md:flex bg-white rounded-lg border border-gray-200 p-4 md:p-6 items-center justify-center" style="min-height: 400px;">
      <i class="fas fa-spinner fa-spin text-4xl" style="color: var(--fluxr-primary);"></i>
    </div>

    <!-- Empty State (Desktop/Tablet Only) -->
    <div v-else class="hidden md:block bg-white rounded-lg border border-gray-200 p-8 text-center">
      <i class="fas fa-chart-bar text-4xl md:text-5xl text-gray-300 mb-3"></i>
      <p class="text-sm md:text-base text-gray-500">No sales data available for the selected filters</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import { fetchWrapper } from '@/helpers/fetch-wrapper';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const loading = ref(true);
const showTimeDropdown = ref(false);
const showFilters = ref(false); // Start with modal closed
const showExportMenu = ref(false);
const metrics = ref({
  gross_sales: 0,
  discounts: 0,
  net_sales: 0,
  gross_profits: 0,
  refunds: 0,
});
const dailyData = ref([]);
const branches = ref([]);
const cashiers = ref([]);
const selectedBranches = ref([]);
const selectedCashiers = ref([]);
const selectedProducts = ref([]);
const products = ref([]);

const filters = ref({
  start_date: '',
  end_date: '',
  time_range: 'all_day', // 'all_day' or 'custom'
  start_time: '',
  end_time: '',
});

// Flatpickr configuration
const datePickerConfig = {
  dateFormat: 'Y-m-d',
  allowInput: true,
  locale: {
    firstDayOfWeek: 1
  }
};


// Initialize filters with default date range (last 7 days)
const initializeFilters = () => {
  const today = new Date();
  const sevenDaysAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);

  filters.value = {
    start_date: sevenDaysAgo.toISOString().split('T')[0],
    end_date: today.toISOString().split('T')[0],
    time_range: 'all_day',
    start_time: '',
    end_time: '',
  };
};

// Computed property for time range label
const timeRangeLabel = computed(() => {
  if (filters.value.time_range === 'all_day') {
    return 'All Day';
  } else if (filters.value.time_range === 'custom') {
    if (filters.value.start_time && filters.value.end_time) {
      return `${filters.value.start_time} - ${filters.value.end_time}`;
    }
    return 'Custom Time';
  }
  return 'All Day';
});

// Select time range function
const selectTimeRange = (range) => {
  filters.value.time_range = range;
  if (range === 'all_day') {
    filters.value.start_time = '';
    filters.value.end_time = '';
  }
  showTimeDropdown.value = false;
};

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-ZA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

// Chart data
const chartData = computed(() => {
  const dates = dailyData.value.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  });

  const sales = dailyData.value.map(item => parseFloat(item.net_sales));
  
  // Create background colors array - green for positive, red for negative
  const backgroundColors = sales.map(value => 
    value >= 0 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)'
  );
  
  const borderColors = sales.map(value => 
    value >= 0 ? 'rgb(22, 163, 74)' : 'rgb(220, 38, 38)'
  );

  return {
    labels: dates,
    datasets: [
      {
        label: 'Net Sales',
        data: sales,
        backgroundColor: backgroundColors,
        borderColor: borderColors,
        borderWidth: 1,
        borderRadius: 4,
      },
    ],
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleFont: { size: 14 },
      bodyFont: { size: 13 },
      callbacks: {
        label: (context) => {
          return '$' + formatCurrency(context.parsed.y);
        },
      },
    },
  },
  scales: {
    y: {
      ticks: {
        callback: (value) => '$' + formatCurrency(value),
        color: 'rgb(107, 114, 128)',
      },
      grid: {
        color: 'rgba(107, 114, 128, 0.1)',
        lineWidth: (context) => {
          // Make the zero line thicker
          return context.tick.value === 0 ? 2 : 1;
        },
      },
    },
    x: {
      ticks: {
        color: 'rgb(107, 114, 128)',
        maxRotation: 45,
        minRotation: 0,
      },
      grid: {
        display: false,
      },
    },
  },
};

// Fetch report data
const fetchReport = async () => {
  loading.value = true;

  try {
    const params = new URLSearchParams({
      start_date: filters.value.start_date,
      end_date: filters.value.end_date,
    });

    // Only add time params if custom time is selected
    if (filters.value.time_range === 'custom') {
      if (filters.value.start_time) {
        params.append('start_time', filters.value.start_time);
      }
      if (filters.value.end_time) {
        params.append('end_time', filters.value.end_time);
      }
    }

    // Add branch IDs from multiselect
    selectedBranches.value.forEach(branch => {
      params.append('branch_ids[]', branch.id);
    });

    // Add cashier IDs from multiselect
    selectedCashiers.value.forEach(cashier => {
      params.append('cashier_ids[]', cashier.id);
    });

    // Add product IDs from multiselect
    selectedProducts.value.forEach(product => {
      params.append('product_ids[]', product.id);
    });

    const apiUrl = import.meta.env.VITE_API_URL;
    const data = await fetchWrapper.get(`${apiUrl}/manager/reports/sales-summary?${params.toString()}`);

    metrics.value = data.metrics;
    dailyData.value = data.daily_breakdown;
    branches.value = data.branches;
    cashiers.value = data.cashiers;
    
    // Store products if available
    if (data.products) {
      products.value = data.products;
    }
  } catch (error) {
    console.error('Error fetching report:', error);
  } finally {
    loading.value = false;
  }
};

// Apply filters and close modal
const applyFiltersAndClose = async () => {
  await fetchReport();
  showFilters.value = false;
};

// Reset filters
const resetFilters = () => {
  initializeFilters();
  selectedBranches.value = [];
  selectedCashiers.value = [];
  selectedProducts.value = [];
  fetchReport();
};

// Export report in different formats
const exportReport = async (format) => {
  showExportMenu.value = false;
  
  try {
    if (format === 'csv') {
      exportCSV();
    } else if (format === 'excel') {
      exportExcel();
    } else if (format === 'pdf') {
      exportPDF();
    }
  } catch (error) {
    console.error('Export error:', error);
    alert('Failed to export report. Please try again.');
  }
};

// Export as CSV
const exportCSV = () => {
  const headers = ['Date', 'Gross Sales', 'Discounts', 'Net Sales', 'Gross Profits', 'Refunds'];
  const rows = dailyData.value.map(day => [
    day.date,
    day.gross_sales,
    day.discounts,
    day.net_sales,
    day.gross_profits,
    day.refunds || 0
  ]);
  
  let csvContent = headers.join(',') + '\n';
  rows.forEach(row => {
    csvContent += row.join(',') + '\n';
  });
  
  // Add summary
  csvContent += '\n';
  csvContent += 'Summary,,,,\n';
  csvContent += `Total Gross Sales,${metrics.value.gross_sales},,,\n`;
  csvContent += `Total Discounts,${metrics.value.discounts},,,\n`;
  csvContent += `Total Net Sales,${metrics.value.net_sales},,,\n`;
  csvContent += `Total Gross Profits,${metrics.value.gross_profits},,,\n`;
  csvContent += `Total Refunds,${metrics.value.refunds},,,\n`;
  
  downloadFile(csvContent, 'sales-summary.csv', 'text/csv');
};

// Export as Excel (using CSV with .xlsx extension for compatibility)
const exportExcel = () => {
  const headers = ['Date', 'Gross Sales', 'Discounts', 'Net Sales', 'Gross Profits', 'Refunds'];
  const rows = dailyData.value.map(day => [
    day.date,
    day.gross_sales,
    day.discounts,
    day.net_sales,
    day.gross_profits,
    day.refunds || 0
  ]);
  
  let content = headers.join('\t') + '\n';
  rows.forEach(row => {
    content += row.join('\t') + '\n';
  });
  
  // Add summary
  content += '\n';
  content += 'Summary\t\t\t\t\n';
  content += `Total Gross Sales\t${metrics.value.gross_sales}\t\t\t\n`;
  content += `Total Discounts\t${metrics.value.discounts}\t\t\t\n`;
  content += `Total Net Sales\t${metrics.value.net_sales}\t\t\t\n`;
  content += `Total Gross Profits\t${metrics.value.gross_profits}\t\t\t\n`;
  content += `Total Refunds\t${metrics.value.refunds}\t\t\t\n`;
  
  downloadFile(content, 'sales-summary.xls', 'application/vnd.ms-excel');
};

// Export as PDF (Simple text-based PDF)
const exportPDF = () => {
  let pdfContent = `
SALES SUMMARY REPORT
Date Range: ${filters.value.start_date} to ${filters.value.end_date}
Generated: ${new Date().toLocaleString()}

================================================================

SUMMARY METRICS:
Gross Sales:     $${formatCurrency(metrics.value.gross_sales)}
Discounts:       $${formatCurrency(metrics.value.discounts)}
Net Sales:       $${formatCurrency(metrics.value.net_sales)}
Gross Profits:   $${formatCurrency(metrics.value.gross_profits)}
Refunds:         $${formatCurrency(metrics.value.refunds)}

================================================================

DAILY BREAKDOWN:

Date          | Gross Sales | Discounts  | Net Sales  | Profits    | Refunds
--------------------------------------------------------------------------
`;

  dailyData.value.forEach(day => {
    pdfContent += `${day.date.padEnd(14)}| $${formatCurrency(day.gross_sales).padEnd(12)}| $${formatCurrency(day.discounts).padEnd(11)}| $${formatCurrency(day.net_sales).padEnd(11)}| $${formatCurrency(day.gross_profits).padEnd(11)}| $${formatCurrency(day.refunds || 0)}\n`;
  });
  
  pdfContent += '\n================================================================\n';
  pdfContent += '\nGenerated by XASH POS System';
  
  downloadFile(pdfContent, 'sales-summary.txt', 'text/plain');
};

// Helper function to trigger file download
const downloadFile = (content, filename, mimeType) => {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

// Initial load
onMounted(() => {
  initializeFilters();
  fetchReport();
});
</script>

<style>
/* Modal Backdrop Transition */
.modal-backdrop-enter-active {
  transition: opacity 0.3s ease;
}

.modal-backdrop-leave-active {
  transition: opacity 0.2s ease;
}

.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
  opacity: 0;
}

/* Slide Up Transition for Bottom Modal */
.slide-up-enter-active {
  transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}

.slide-up-leave-active {
  transition: transform 0.25s cubic-bezier(0.32, 0.72, 0, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}

/* Flatpickr custom styles */
.flatpickr-calendar {
  font-size: 0.875rem;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.flatpickr-day.selected {
  background: #22c55e;
  border-color: #22c55e;
}

.flatpickr-day.selected:hover {
  background: #16a34a;
  border-color: #16a34a;
}

/* Vue-Select custom styles */
.custom-v-select .vs__dropdown-toggle {
  border: 1px solid #d1d5db;
  background-color: white;
  min-height: 38px;
  padding: 2px 8px;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.custom-v-select .vs__selected-options {
  padding: 0;
  flex-wrap: wrap;
}

.custom-v-select .vs__search,
.custom-v-select .vs__search:focus {
  margin: 0;
  padding: 4px 0;
  font-size: 0.875rem;
  background-color: transparent;
  border: none;
}

.custom-v-select .vs__selected {
  background-color: #22c55e;
  color: white;
  padding: 2px 8px;
  margin: 2px 4px 2px 0;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  border: none;
}

.custom-v-select .vs__deselect {
  fill: white;
}

.custom-v-select .vs__deselect:hover {
  fill: white;
}

.custom-v-select .vs__clear,
.custom-v-select .vs__open-indicator {
  fill: #6b7280;
}

.custom-v-select .vs__clear,
.custom-v-select .vs__open-indicator {
  fill: #6b7280;
}

.custom-v-select .vs__dropdown-menu {
  background-color: white;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 4px 0;
}

.custom-v-select .vs__dropdown-option {
  padding: 10px 12px;
  font-size: 0.875rem;
  color: #374151;
}

.custom-v-select .vs__dropdown-option--highlight {
  background-color: #22c55e;
  color: white;
}

.custom-v-select .vs__dropdown-option--selected {
  background-color: #dcfce7;
  color: #166534;
}

.custom-v-select .vs__no-options {
  padding: 12px;
  text-align: center;
  color: #6b7280;
  font-size: 0.875rem;
}

/* Mobile responsiveness for vue-select */
@media (max-width: 640px) {
  .custom-v-select .vs__dropdown-toggle {
    min-height: 34px;
    font-size: 0.75rem;
    padding: 1px 6px;
  }
  
  .custom-v-select .vs__selected {
    font-size: 0.7rem;
    padding: 2px 6px;
    margin: 1px 2px 1px 0;
  }
  
  .custom-v-select .vs__search {
    font-size: 0.75rem;
  }
  
  .custom-v-select .vs__dropdown-option {
    font-size: 0.75rem;
    padding: 8px 10px;
  }
}
</style>

<template>
  <AdminLayout page-title="Dashboard">
    <div class="space-y-6">
      <!-- Welcome Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {{ $page.props.auth.user.name }}!</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Here's what's happening with your business today.</p>
        </div>
        <div class="flex gap-2">
          <Link :href="route('admin.branches.create')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> New Branch
          </Link>
        </div>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Branches Card -->
        <div class="bg-brand-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-store text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-brand-100">Branches</p>
              <p class="text-lg font-bold text-white">{{ stats.active_branches }}<span class="text-xs font-normal text-brand-200">/{{ stats.total_branches }}</span></p>
            </div>
          </div>
        </div>

        <!-- Products Card -->
        <div class="bg-brand-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-box text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-brand-100">Products</p>
              <p class="text-lg font-bold text-white">{{ stats.active_products }}<span class="text-xs font-normal text-brand-200">/{{ stats.total_products }}</span></p>
            </div>
          </div>
        </div>

        <!-- Users Card -->
        <div class="bg-brand-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-users text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-brand-100">Users</p>
              <p class="text-lg font-bold text-white">{{ stats.active_users }}<span class="text-xs font-normal text-brand-200">/{{ stats.total_users }}</span></p>
            </div>
          </div>
        </div>

        <!-- Customers Card -->
        <div class="bg-brand-500 rounded-lg p-3 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center flex-shrink-0">
              <i class="fas fa-user-friends text-white text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs text-brand-100">Customers</p>
              <p class="text-lg font-bold text-white">{{ stats.total_customers }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Products Growth Chart -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Products Added</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Last 7 days</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
            </div>
          </div>
          <apexchart
            type="area"
            height="280"
            :options="productsChart.options"
            :series="productsChart.series"
          ></apexchart>
        </div>

        <!-- Category Distribution Chart -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Category Distribution</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Products by category</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-pie text-purple-600 dark:text-purple-400"></i>
            </div>
          </div>
          <apexchart
            type="donut"
            height="280"
            :options="categoryChart.options"
            :series="categoryChart.series"
          ></apexchart>
        </div>
      </div>

      <!-- Stock Levels & Top Products -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Stock Levels -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Stock Levels</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Inventory status overview</p>
            </div>
            <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-boxes text-brand-600 dark:text-brand-400"></i>
            </div>
          </div>
          <apexchart
            type="bar"
            height="280"
            :options="stockChart.options"
            :series="stockChart.series"
          ></apexchart>
        </div>

        <!-- Top Products -->
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products by Value</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Highest stock value items</p>
            </div>
          </div>
          <div class="space-y-4">
            <div v-if="topProducts.length > 0">
              <div v-for="(product, index) in topProducts" :key="index" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-800 rounded-lg hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4 flex-1 min-w-0">
                  <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-500 rounded-lg flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    #{{ index + 1 }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ product.sku }}</p>
                  </div>
                </div>
                <div class="text-right flex-shrink-0 ml-4">
                  <p class="font-bold text-gray-900 dark:text-white">R{{ formatCurrency(product.stock_value) }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.total_quantity }} units</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
              <i class="fas fa-box-open text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
              <p>No product data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Branches -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Branches</h2>
            <Link :href="route('admin.branches.index')" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">
              View all
            </Link>
          </div>
          <div class="space-y-3" v-if="branches.length > 0">
            <div v-for="branch in branches" :key="branch.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
              <div class="flex items-center gap-3">
                <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', branch.is_active ? 'bg-brand-100 dark:bg-brand-900/30' : 'bg-gray-200 dark:bg-gray-700']">
                  <i :class="['fas fa-store', branch.is_active ? 'text-brand-600 dark:text-brand-400' : 'text-gray-500']"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ branch.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ branch.city || 'No location' }}</p>
                </div>
              </div>
              <span :class="['px-2 py-1 text-xs font-medium rounded-full', branch.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                {{ branch.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            <i class="fas fa-store text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
            <p>No branches yet</p>
            <Link :href="route('admin.branches.create')" class="text-brand-600 dark:text-brand-400 hover:underline text-sm mt-2 inline-block">
              Create your first branch
            </Link>
          </div>
        </div>

        <!-- Recent Users -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h2>
            <Link :href="route('admin.users.index')" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">
              View all
            </Link>
          </div>
          <div class="space-y-3" v-if="recentUsers.length > 0">
            <div v-for="user in recentUsers" :key="user.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 to-brand-500 flex items-center justify-center text-white font-semibold">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                </div>
              </div>
              <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 capitalize">
                {{ user.role }}
              </span>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            <i class="fas fa-users text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
            <p>No users yet</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <Link :href="route('admin.products.create')" class="quick-action-btn">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/10 rounded-lg flex items-center justify-center mb-3">
              <i class="fas fa-plus text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Add Product</span>
          </Link>
          <Link :href="route('admin.users.create')" class="quick-action-btn">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900/30 dark:to-purple-900/10 rounded-lg flex items-center justify-center mb-3">
              <i class="fas fa-user-plus text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Add User</span>
          </Link>
          <Link :href="route('admin.customers.create')" class="quick-action-btn">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-50 dark:from-orange-900/30 dark:to-orange-900/10 rounded-lg flex items-center justify-center mb-3">
              <i class="fas fa-user-friends text-orange-600 dark:text-orange-400 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Add Customer</span>
          </Link>
          <Link :href="route('admin.settings.index')" class="quick-action-btn">
            <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-lg flex items-center justify-center mb-3">
              <i class="fas fa-cog text-gray-600 dark:text-gray-400 text-xl"></i>
            </div>
            <span class="text-sm font-medium text-gray-900 dark:text-white">Settings</span>
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
  stats: Object,
  branches: Array,
  recentUsers: Array,
  lowStockCount: Number,
  productsGrowth: Array,
  categoryDistribution: Array,
  topProducts: Array,
  stockLevels: Object,
});

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-ZA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

// Products Growth Chart Configuration
const productsChart = computed(() => ({
  series: [{
    name: 'Products Added',
    data: props.productsGrowth.map(item => item.count)
  }],
  options: {
    chart: {
      type: 'area',
      height: 280,
      toolbar: {
        show: false
      },
      zoom: {
        enabled: false
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 3
    },
    colors: ['#10b981'],
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.4,
        opacityTo: 0.1,
      }
    },
    xaxis: {
      categories: props.productsGrowth.map(item => item.date),
      labels: {
        style: {
          colors: '#9ca3af'
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: '#9ca3af'
        }
      }
    },
    grid: {
      borderColor: '#374151',
      strokeDashArray: 5
    },
    tooltip: {
      theme: 'dark',
      y: {
        formatter: function (val) {
          return val + ' products'
        }
      }
    }
  }
}));

// Category Distribution Chart Configuration
const categoryChart = computed(() => ({
  series: props.categoryDistribution.map(cat => cat.count),
  options: {
    chart: {
      type: 'donut',
      height: 280
    },
    labels: props.categoryDistribution.map(cat => cat.name),
    colors: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4'],
    legend: {
      position: 'bottom',
      labels: {
        colors: '#9ca3af'
      }
    },
    plotOptions: {
      pie: {
        donut: {
          size: '70%',
          labels: {
            show: true,
            total: {
              show: true,
              label: 'Total Products',
              color: '#9ca3af',
              formatter: function (w) {
                return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              }
            }
          }
        }
      }
    },
    dataLabels: {
      enabled: true,
      style: {
        colors: ['#fff']
      }
    },
    tooltip: {
      theme: 'dark',
      y: {
        formatter: function (val) {
          return val + ' products'
        }
      }
    }
  }
}));

// Stock Levels Chart Configuration
const stockChart = computed(() => ({
  series: [{
    name: 'Products',
    data: [
      props.stockLevels.in_stock,
      props.stockLevels.low_stock,
      props.stockLevels.out_of_stock
    ]
  }],
  options: {
    chart: {
      type: 'bar',
      height: 280,
      toolbar: {
        show: false
      }
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '55%',
        borderRadius: 8
      }
    },
    dataLabels: {
      enabled: false
    },
    colors: ['#10b981', '#f59e0b', '#ef4444'],
    xaxis: {
      categories: ['In Stock', 'Low Stock', 'Out of Stock'],
      labels: {
        style: {
          colors: ['#10b981', '#f59e0b', '#ef4444']
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: '#9ca3af'
        }
      }
    },
    grid: {
      borderColor: '#374151',
      strokeDashArray: 5
    },
    tooltip: {
      theme: 'dark',
      y: {
        formatter: function (val) {
          return val + ' products'
        }
      }
    }
  }
}));
</script>

<script>
import VueApexCharts from 'vue3-apexcharts';

export default {
  components: {
    apexchart: VueApexCharts,
  },
};
</script>

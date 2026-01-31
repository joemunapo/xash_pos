<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-primary-light rounded-lg flex items-center justify-center">
          <i class="fas fa-chart-bar text-primary text-xl"></i>
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Reports</h1>
          <p class="text-gray-600">View detailed business reports</p>
        </div>
      </div>
    </div>

    <!-- Report Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="report in reports"
        :key="report.name"
        class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 cursor-pointer"
      >
        <div class="flex items-start gap-4">
          <div :class="`w-14 h-14 rounded-lg flex items-center justify-center flex-shrink-0 ${report.bgColor}`">
            <i :class="`${report.icon} text-2xl ${report.iconColor}`"></i>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-800 text-lg mb-1">{{ report.name }}</h3>
            <p class="text-sm text-gray-500 mb-3">{{ report.description }}</p>
            <button class="text-sm text-primary hover:text-primary-dark font-medium flex items-center gap-1">
              View Report
              <i class="fas fa-arrow-right text-xs"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-primary-light rounded-lg flex items-center justify-center">
          <i class="fas fa-tachometer-alt text-primary text-lg"></i>
        </div>
        <h2 class="text-lg font-semibold text-gray-800">Quick Overview</h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-success-light rounded-lg border border-success">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-dollar-sign text-success"></i>
            <p class="text-sm text-gray-600">Today's Revenue</p>
          </div>
          <p class="text-2xl font-bold text-success">$0.00</p>
        </div>
        <div class="p-4 bg-primary-light rounded-lg border border-primary">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-calendar-week text-primary"></i>
            <p class="text-sm text-gray-600">This Week</p>
          </div>
          <p class="text-2xl font-bold text-primary">$0.00</p>
        </div>
        <div class="p-4 bg-primary-light rounded-lg border border-primary">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-calendar-alt text-primary"></i>
            <p class="text-sm text-gray-600">This Month</p>
          </div>
          <p class="text-2xl font-bold text-primary">$0.00</p>
        </div>
        <div class="p-4 bg-accent-light rounded-lg border border-accent">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-shopping-cart text-accent"></i>
            <p class="text-sm text-gray-600">Total Sales</p>
          </div>
          <p class="text-2xl font-bold text-accent">0</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const reports = ref([
  {
    name: 'Sales Report',
    description: 'Daily, weekly, and monthly sales analytics',
    icon: 'fas fa-chart-line',
    bgColor: 'bg-primary-light',
    iconColor: 'text-primary'
  },
  {
    name: 'Cashier Performance',
    description: 'Individual cashier statistics and rankings',
    icon: 'fas fa-users',
    bgColor: 'bg-success-light',
    iconColor: 'text-success'
  },
  {
    name: 'Product Report',
    description: 'Top selling products and inventory insights',
    icon: 'fas fa-box',
    bgColor: 'bg-accent-light',
    iconColor: 'text-accent'
  },
  {
    name: 'Daily Summary',
    description: 'Detailed breakdown of today\'s transactions',
    icon: 'fas fa-calendar-day',
    bgColor: 'bg-primary-light',
    iconColor: 'text-primary'
  },
  {
    name: 'Payment Methods',
    description: 'Analysis of payment method usage',
    icon: 'fas fa-credit-card',
    bgColor: 'bg-success-light',
    iconColor: 'text-success'
  },
  {
    name: 'Hourly Trends',
    description: 'Sales trends by hour of the day',
    icon: 'fas fa-clock',
    bgColor: 'bg-accent-light',
    iconColor: 'text-accent'
  }
]);
</script>

<template>
  <AdminLayout page-title="Goods Received">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Coming Soon Card -->
      <div class="card p-12 text-center">
        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-brand-100 to-brand-200 dark:from-brand-900/30 dark:to-brand-800/30 rounded-full flex items-center justify-center">
          <i class="fas fa-dolly text-4xl text-brand-600 dark:text-brand-400"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">Goods Received</h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">Coming Soon</p>
        
        <div class="max-w-2xl mx-auto text-left space-y-4 mb-8">
          <p class="text-gray-700 dark:text-gray-300">The Goods Received module will allow you to:</p>
          <ul class="space-y-2">
            <li class="flex items-start gap-3">
              <i class="fas fa-check-circle text-brand-500 mt-1"></i>
              <span class="text-gray-700 dark:text-gray-300">Record goods received from suppliers</span>
            </li>
            <li class="flex items-start gap-3">
              <i class="fas fa-check-circle text-brand-500 mt-1"></i>
              <span class="text-gray-700 dark:text-gray-300">Link GRNs to purchase orders</span>
            </li>
            <li class="flex items-start gap-3">
              <i class="fas fa-check-circle text-brand-500 mt-1"></i>
              <span class="text-gray-700 dark:text-gray-300">Verify quantities received vs ordered</span>
            </li>
            <li class="flex items-start gap-3">
              <i class="fas fa-check-circle text-brand-500 mt-1"></i>
              <span class="text-gray-700 dark:text-gray-300">Automatically update stock levels</span>
            </li>
            <li class="flex items-start gap-3">
              <i class="fas fa-check-circle text-brand-500 mt-1"></i>
              <span class="text-gray-700 dark:text-gray-300">Generate GRN reports for accounting</span>
            </li>
          </ul>
        </div>

        <div class="flex items-center justify-center gap-4">
          <Link :href="route('admin.suppliers.index')" class="btn-secondary">
            <i class="fas fa-truck mr-2"></i>View Suppliers
          </Link>
          <Link :href="route('admin.inventory.overview')" class="btn-primary">
            <i class="fas fa-warehouse mr-2"></i>Check Inventory
          </Link>
        </div>
      </div>

      <!-- Feature Preview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card p-6 text-center">
          <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
            <i class="fas fa-clipboard-check text-2xl text-blue-600 dark:text-blue-400"></i>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Record GRNs</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Quick goods received note creation</p>
        </div>
        
        <div class="card p-6 text-center">
          <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
            <i class="fas fa-link text-2xl text-purple-600 dark:text-purple-400"></i>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Link to POs</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Connected to purchase orders</p>
        </div>
        
        <div class="card p-6 text-center">
          <div class="w-16 h-16 mx-auto mb-4 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center">
            <i class="fas fa-sync-alt text-2xl text-brand-600 dark:text-brand-400"></i>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Auto Update</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Automatic inventory synchronization</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
</script>

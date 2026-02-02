<template>
  <AdminLayout page-title="Supplier Details">
    <div class="max-w-6xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ supplier.name }}</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Supplier information and associated products</p>
        </div>
        <div class="flex gap-3">
          <Link :href="route('admin.suppliers.edit', supplier.id)" class="btn-secondary">
            <i class="fas fa-edit mr-2"></i>Edit
          </Link>
          <Link :href="route('admin.suppliers.index')" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back
          </Link>
        </div>
      </div>

      <!-- Supplier Information -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>
            Supplier Information
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier Name</label>
              <p class="text-gray-900 dark:text-white mt-1">{{ supplier.name }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
              <p class="mt-1">
                <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', supplier.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                  <i :class="[supplier.is_active ? 'fas fa-check-circle' : 'fas fa-times-circle', 'mr-1.5']"></i>
                  {{ supplier.is_active ? 'Active' : 'Inactive' }}
                </span>
              </p>
            </div>
            <div v-if="supplier.contact_person">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Person</label>
              <p class="text-gray-900 dark:text-white mt-1">{{ supplier.contact_person }}</p>
            </div>
            <div v-if="supplier.phone">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</label>
              <p class="text-gray-900 dark:text-white mt-1">
                <i class="fas fa-phone mr-2 text-gray-400"></i>{{ supplier.phone }}
              </p>
            </div>
            <div v-if="supplier.email" class="md:col-span-2">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
              <p class="text-gray-900 dark:text-white mt-1">
                <i class="fas fa-envelope mr-2 text-gray-400"></i>{{ supplier.email }}
              </p>
            </div>
            <div v-if="supplier.address" class="md:col-span-2">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</label>
              <p class="text-gray-900 dark:text-white mt-1">{{ supplier.address }}</p>
            </div>
            <div v-if="supplier.city">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">City</label>
              <p class="text-gray-900 dark:text-white mt-1">{{ supplier.city }}</p>
            </div>
          </div>
        </div>

        <!-- Payment Terms -->
        <div class="card p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-credit-card text-brand-500"></i>
            Payment Terms
          </h2>
          <div class="space-y-4">
            <div v-if="supplier.payment_terms">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Terms</label>
              <p class="text-gray-900 dark:text-white mt-1">{{ supplier.payment_terms }}</p>
            </div>
            <div v-if="supplier.credit_days">
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Credit Days</label>
              <p class="text-gray-900 dark:text-white mt-1">{{ supplier.credit_days }} days</p>
            </div>
            <div v-if="!supplier.payment_terms && !supplier.credit_days">
              <p class="text-gray-500 dark:text-gray-400 italic">No payment terms specified</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="supplier.notes" class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
          <i class="fas fa-sticky-note text-orange-500"></i>
          Notes
        </h2>
        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ supplier.notes }}</p>
      </div>

      <!-- Associated Products -->
      <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fas fa-boxes text-purple-500"></i>
          Associated Products
          <span class="px-2.5 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded-full ml-2">
            {{ supplier.products?.length || 0 }}
          </span>
        </h2>
        <div v-if="supplier.products && supplier.products.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-40">Supplier SKU</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Cost Price</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Selling Price</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">Primary</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="product in supplier.products" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-900 dark:text-white">{{ product.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ product.sku }}</p>
                </td>
                <td class="px-4 py-3 text-gray-900 dark:text-white font-mono text-sm">
                  {{ product.pivot.supplier_sku || '-' }}
                </td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white font-semibold">
                  ${{ parseFloat(product.pivot.cost_price || 0).toFixed(2) }}
                </td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                  ${{ parseFloat(product.selling_price || 0).toFixed(2) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="product.pivot.is_primary" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                    <i class="fas fa-star mr-1"></i>Primary
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8">
          <i class="fas fa-box-open text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
          <p class="text-gray-500 dark:text-gray-400">No products associated with this supplier</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  supplier: Object,
});
</script>

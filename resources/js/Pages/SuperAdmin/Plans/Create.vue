<template>
  <SuperAdminLayout page-title="Create Plan">
    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="route('superadmin.plans.index')" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Subscription Plan</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Define a new pricing plan for tenants.</p>
        </div>
      </div>

      <form @submit.prevent="submit">
        <!-- Basic Info -->
        <div class="card mb-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Plan Details</h2>
          <div class="space-y-4">
            <div>
              <label class="label">Plan Name *</label>
              <input v-model="form.name" type="text" class="input-field" placeholder="e.g. Professional" />
              <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="label">Description</label>
              <textarea v-model="form.description" rows="2" class="input-field" placeholder="Brief description of this plan"></textarea>
            </div>
          </div>
        </div>

        <!-- Pricing -->
        <div class="card mb-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pricing</h2>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Monthly Price ($) *</label>
              <input v-model="form.price_monthly" type="number" step="0.01" min="0" class="input-field" placeholder="29.00" />
              <p v-if="form.errors.price_monthly" class="text-sm text-red-500 mt-1">{{ form.errors.price_monthly }}</p>
            </div>
            <div>
              <label class="label">Yearly Price ($) *</label>
              <input v-model="form.price_yearly" type="number" step="0.01" min="0" class="input-field" placeholder="290.00" />
              <p v-if="form.errors.price_yearly" class="text-sm text-red-500 mt-1">{{ form.errors.price_yearly }}</p>
            </div>
          </div>
        </div>

        <!-- Limits -->
        <div class="card mb-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Limits</h2>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="label">Max Users *</label>
              <input v-model="form.max_users" type="number" min="1" class="input-field" />
              <p v-if="form.errors.max_users" class="text-sm text-red-500 mt-1">{{ form.errors.max_users }}</p>
            </div>
            <div>
              <label class="label">Max Branches *</label>
              <input v-model="form.max_branches" type="number" min="1" class="input-field" />
              <p v-if="form.errors.max_branches" class="text-sm text-red-500 mt-1">{{ form.errors.max_branches }}</p>
            </div>
            <div>
              <label class="label">Max Products *</label>
              <input v-model="form.max_products" type="number" min="1" class="input-field" />
              <p v-if="form.errors.max_products" class="text-sm text-red-500 mt-1">{{ form.errors.max_products }}</p>
            </div>
          </div>
        </div>

        <!-- Features -->
        <div class="card mb-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Features</h2>
          <div class="space-y-2">
            <div v-for="(feature, index) in form.features" :key="index" class="flex items-center gap-2">
              <input v-model="form.features[index]" type="text" class="input-field" placeholder="e.g. Priority Support" />
              <button type="button" @click="form.features.splice(index, 1)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex-shrink-0">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <button type="button" @click="form.features.push('')" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
              <i class="fas fa-plus mr-1"></i>Add Feature
            </button>
          </div>
        </div>

        <!-- Settings -->
        <div class="card mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Active</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400">Make this plan available for selection</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input v-model="form.is_active" type="checkbox" class="sr-only peer" />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('superadmin.plans.index')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50">
            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-plus mr-2"></i>
            {{ form.processing ? 'Creating...' : 'Create Plan' }}
          </button>
        </div>
      </form>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

const form = useForm({
  name: '',
  description: '',
  price_monthly: '',
  price_yearly: '',
  max_users: 5,
  max_branches: 1,
  max_products: 100,
  features: [''],
  is_active: true,
  sort_order: 0,
});

const submit = () => {
  form.transform((data) => ({
    ...data,
    features: data.features.filter(f => f.trim() !== ''),
  })).post(route('superadmin.plans.store'));
};
</script>

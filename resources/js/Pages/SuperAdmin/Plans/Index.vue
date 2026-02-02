<template>
  <SuperAdminLayout page-title="Subscription Plans">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage pricing plans for tenants.</p>
        </div>
        <Link :href="route('superadmin.plans.create')" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors shadow-sm">
          <i class="fas fa-plus mr-2"></i> New Plan
        </Link>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="plan in plans" :key="plan.id" class="card relative">
          <!-- Active/Inactive badge -->
          <div class="absolute top-3 right-3">
            <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', plan.is_active ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
              {{ plan.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ plan.name }}</h3>
            <p v-if="plan.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ plan.description }}</p>
          </div>

          <!-- Pricing -->
          <div class="flex items-baseline gap-1 mb-4">
            <span class="text-3xl font-bold text-brand-600 dark:text-brand-400">${{ plan.price_monthly }}</span>
            <span class="text-gray-500 dark:text-gray-400">/mo</span>
            <span class="text-sm text-gray-400 dark:text-gray-500 ml-2">or ${{ plan.price_yearly }}/yr</span>
          </div>

          <!-- Limits -->
          <div class="space-y-2 mb-4 pb-4 border-b border-gray-100 dark:border-slate-800">
            <div class="flex items-center gap-2 text-sm">
              <i class="fas fa-users text-brand-500 w-5"></i>
              <span class="text-gray-700 dark:text-gray-300">{{ plan.max_users >= 999999 ? 'Unlimited' : plan.max_users }} users</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <i class="fas fa-store text-brand-500 w-5"></i>
              <span class="text-gray-700 dark:text-gray-300">{{ plan.max_branches >= 999999 ? 'Unlimited' : plan.max_branches }} branches</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <i class="fas fa-box text-purple-500 w-5"></i>
              <span class="text-gray-700 dark:text-gray-300">{{ plan.max_products >= 999999 ? 'Unlimited' : plan.max_products }} products</span>
            </div>
          </div>

          <!-- Features -->
          <div v-if="plan.features && plan.features.length > 0" class="space-y-1.5 mb-4">
            <div v-for="feature in plan.features" :key="feature" class="flex items-center gap-2 text-sm">
              <i class="fas fa-check text-brand-500 text-xs"></i>
              <span class="text-gray-600 dark:text-gray-400">{{ feature }}</span>
            </div>
          </div>

          <!-- Stats & Actions -->
          <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100 dark:border-slate-800">
            <span class="text-sm text-gray-500 dark:text-gray-400">
              <i class="fas fa-users mr-1"></i>{{ plan.active_subscriptions_count }} active
            </span>
            <div class="flex gap-2">
              <Link :href="route('superadmin.plans.edit', plan.id)" class="px-3 py-1.5 text-xs font-medium bg-brand-600 text-white rounded hover:bg-brand-700 transition-colors">
                <i class="fas fa-edit mr-1"></i>Edit
              </Link>
              <button @click="deletePlan(plan)" class="px-3 py-1.5 text-xs font-medium bg-danger-600 text-white rounded hover:bg-danger-700 transition-colors">
                <i class="fas fa-trash mr-1"></i>Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="plans.length === 0" class="card text-center py-12">
        <i class="fas fa-credit-card text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
        <p class="text-gray-500 dark:text-gray-400">No subscription plans yet</p>
        <Link :href="route('superadmin.plans.create')" class="text-brand-600 dark:text-brand-400 hover:underline text-sm mt-2 inline-block">
          Create your first plan
        </Link>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { confirmAction } from '@/composables/useFlashMessages';

defineProps({
  plans: Array,
});

const deletePlan = async (plan) => {
  const result = await confirmAction({
    title: 'Delete Plan?',
    text: `Are you sure you want to delete the "${plan.name}" plan?`,
    icon: 'warning',
    confirmButtonText: 'Yes, delete',
  });
  if (result.isConfirmed) {
    router.delete(route('superadmin.plans.destroy', plan.id));
  }
};
</script>

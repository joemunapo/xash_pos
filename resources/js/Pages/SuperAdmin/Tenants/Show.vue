<template>
  <SuperAdminLayout page-title="Tenant Details">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link :href="route('superadmin.tenants.index')" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </Link>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ tenant.name }}</h1>
              <span :class="statusBadgeClass(tenant.subscription_status)">{{ tenant.subscription_status }}</span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ tenant.email }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button v-if="tenant.subscription_status !== 'suspended'" @click="suspendTenant" class="px-3 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            <i class="fas fa-ban mr-1"></i>Suspend
          </button>
          <button v-else @click="activateTenant" class="px-3 py-2 text-sm font-medium bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-colors">
            <i class="fas fa-check mr-1"></i>Activate
          </button>
          <button @click="deleteTenant" class="px-3 py-2 text-sm font-medium bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-trash mr-1"></i>Delete
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="card text-center">
          <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ tenant.users_count }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Users</p>
        </div>
        <div class="card text-center">
          <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ tenant.branches_count }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Branches</p>
        </div>
        <div class="card text-center">
          <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ tenant.products_count }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Products</p>
        </div>
        <div class="card text-center">
          <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ tenant.customers_count }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Customers</p>
        </div>
        <div class="card text-center">
          <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ tenant.suppliers_count }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">Suppliers</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Business Info -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Business Information</h2>
            <button @click="editingInfo = !editingInfo" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
              {{ editingInfo ? 'Cancel' : 'Edit' }}
            </button>
          </div>

          <form v-if="editingInfo" @submit.prevent="updateInfo" class="space-y-3">
            <div>
              <label class="label">Name</label>
              <input v-model="infoForm.name" type="text" class="input-field" />
            </div>
            <div>
              <label class="label">Email</label>
              <input v-model="infoForm.email" type="email" class="input-field" />
            </div>
            <div>
              <label class="label">Phone</label>
              <input v-model="infoForm.phone" type="text" class="input-field" />
            </div>
            <div>
              <label class="label">Address</label>
              <textarea v-model="infoForm.address" rows="2" class="input-field"></textarea>
            </div>
            <button type="submit" :disabled="infoForm.processing" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
              <i v-if="infoForm.processing" class="fas fa-spinner fa-spin mr-1"></i>Save Changes
            </button>
          </form>

          <div v-else class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Name</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.name }}</span>
            </div>
            <div v-if="tenant.trading_name" class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Trading Name</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.trading_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Email</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.email }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Phone</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.phone || '—' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Currency</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.default_currency }}</span>
            </div>
            <div class="flex justify-between py-2">
              <span class="text-sm text-gray-500 dark:text-gray-400">Created</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(tenant.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Subscription -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription</h2>

          <div v-if="tenant.active_subscription" class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Plan</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.active_subscription.plan_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Price</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">${{ tenant.active_subscription.price }}/{{ tenant.active_subscription.billing_cycle }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Limits</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.active_subscription.max_users }} users, {{ tenant.active_subscription.max_branches }} branches</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-slate-800">
              <span class="text-sm text-gray-500 dark:text-gray-400">Ends</span>
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(tenant.active_subscription.ends_at) }}</span>
            </div>
            <div class="flex justify-between py-2">
              <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
              <span :class="statusBadgeClass(tenant.active_subscription.status)">{{ tenant.active_subscription.status }}</span>
            </div>
          </div>

          <div v-else class="space-y-4">
            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800/30">
              <p class="text-sm text-amber-700 dark:text-amber-300">
                <i class="fas fa-info-circle mr-1"></i>
                <span v-if="tenant.subscription_status === 'trial'">On free trial until {{ formatDate(tenant.trial_ends_at) }}</span>
                <span v-else>No active subscription</span>
              </p>
            </div>

            <!-- Assign Subscription -->
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Assign a Plan</h3>
            <form @submit.prevent="assignSubscription" class="space-y-3">
              <select v-model="subForm.subscription_plan_id" class="input-field">
                <option value="">Select a plan...</option>
                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                  {{ plan.name }} - ${{ plan.price_monthly }}/mo
                </option>
              </select>
              <select v-model="subForm.billing_cycle" class="input-field">
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
              <button type="submit" :disabled="!subForm.subscription_plan_id || subForm.processing" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                <i v-if="subForm.processing" class="fas fa-spinner fa-spin mr-1"></i>Assign Plan
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Users -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Users ({{ tenant.users.length }})</h2>
        <div v-if="tenant.users.length > 0" class="space-y-2">
          <div v-for="user in tenant.users" :key="user.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded-full capitalize">{{ user.role }}</span>
              <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', user.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
          <p>No users</p>
        </div>
      </div>

      <!-- Branches -->
      <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Branches ({{ tenant.branches.length }})</h2>
        <div v-if="tenant.branches.length > 0" class="space-y-2">
          <div v-for="branch in tenant.branches" :key="branch.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <i class="fas fa-store text-blue-600 dark:text-blue-400 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ branch.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ branch.city || branch.address || '—' }}</p>
              </div>
            </div>
            <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', branch.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400']">
              {{ branch.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
          <p>No branches</p>
        </div>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { confirmAction } from '@/composables/useFlashMessages';

const props = defineProps({
  tenant: Object,
  plans: Array,
});

const editingInfo = ref(false);

const infoForm = useForm({
  name: props.tenant.name,
  email: props.tenant.email,
  phone: props.tenant.phone || '',
  address: props.tenant.address || '',
});

const subForm = useForm({
  subscription_plan_id: '',
  billing_cycle: 'monthly',
});

const updateInfo = () => {
  infoForm.put(route('superadmin.tenants.update', props.tenant.id), {
    preserveScroll: true,
    onSuccess: () => { editingInfo.value = false; },
  });
};

const assignSubscription = () => {
  subForm.post(route('superadmin.tenants.assign-subscription', props.tenant.id), {
    preserveScroll: true,
    onSuccess: () => { subForm.reset(); },
  });
};

const suspendTenant = async () => {
  const result = await confirmAction({
    title: 'Suspend Tenant?',
    text: `Are you sure you want to suspend "${props.tenant.name}"?`,
    icon: 'warning',
    confirmButtonText: 'Yes, suspend',
  });
  if (result.isConfirmed) {
    router.post(route('superadmin.tenants.suspend', props.tenant.id));
  }
};

const activateTenant = async () => {
  const result = await confirmAction({
    title: 'Activate Tenant?',
    text: `Activate "${props.tenant.name}"?`,
    icon: 'question',
    confirmButtonText: 'Yes, activate',
  });
  if (result.isConfirmed) {
    router.post(route('superadmin.tenants.activate', props.tenant.id));
  }
};

const deleteTenant = async () => {
  const result = await confirmAction({
    title: 'Delete Tenant?',
    text: `This will permanently delete "${props.tenant.name}" and all associated data. This cannot be undone.`,
    icon: 'warning',
    confirmButtonText: 'Yes, delete permanently',
  });
  if (result.isConfirmed) {
    router.delete(route('superadmin.tenants.destroy', props.tenant.id));
  }
};

const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusBadgeClass = (status) => {
  const base = 'px-2 py-1 text-xs font-medium rounded-full capitalize';
  switch (status) {
    case 'active': return `${base} bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400`;
    case 'trial': return `${base} bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400`;
    case 'suspended': return `${base} bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400`;
    case 'cancelled': return `${base} bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400`;
    default: return `${base} bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400`;
  }
};
</script>

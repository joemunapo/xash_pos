<template>
  <SuperAdminLayout page-title="Create Tenant">
    <div class="mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="route('superadmin.tenants.index')" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Tenant</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Set up a new business account on the platform.</p>
        </div>
      </div>

      <form @submit.prevent="submit">
        <!-- Business Details & Admin User (Side by Side on md+) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <!-- Business Details -->
          <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              <i class="fas fa-building mr-2 text-brand-500"></i>Business Details
            </h2>
            <div class="space-y-4">
              <div>
                <label class="label">Business Name *</label>
                <input v-model="form.name" type="text" class="input-field" placeholder="e.g. OK Zimbabwe, Pick n Pay" />
                <p v-if="form.errors.name" class="text-sm text-danger-500 mt-1">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="label">Trading Name</label>
                <input v-model="form.trading_name" type="text" class="input-field" placeholder="e.g. OK Grand Challenge" />
              </div>
              <div>
                <label class="label">Email *</label>
                <input v-model="form.email" type="email" class="input-field" placeholder="info@yourstore.co.zw" />
                <p v-if="form.errors.email" class="text-sm text-danger-500 mt-1">{{ form.errors.email }}</p>
              </div>
              <div>
                <label class="label">Phone</label>
                <input v-model="form.phone" type="text" class="input-field" placeholder="+263 77 123 4567" />
              </div>
              <div>
                <label class="label">Website</label>
                <input v-model="form.website" type="url" class="input-field" placeholder="https://yourstore.co.zw" />
              </div>
              <div>
                <label class="label">Address</label>
                <textarea v-model="form.address" rows="2" class="input-field" placeholder="123 Robert Mugabe Road, Harare, Zimbabwe"></textarea>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="label">Registration Number</label>
                  <input v-model="form.registration_number" type="text" class="input-field" placeholder="e.g. 12345/2024" />
                </div>
                <div>
                  <label class="label">VAT Number</label>
                  <input v-model="form.vat_number" type="text" class="input-field" placeholder="e.g. 10123456V01" />
                </div>
              </div>
              <div>
                <label class="label">Default Currency</label>
                <select v-model="form.default_currency" class="input-field">
                  <option value="ZWL">ZWL - Zimbabwean Dollar</option>
                  <option value="USD">USD - US Dollar</option>
                  <option value="ZAR">ZAR - South African Rand</option>
                  <option value="GBP">GBP - British Pound</option>
                  <option value="EUR">EUR - Euro</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Admin User & Subscription Plan (Right Column) -->
          <div class="space-y-6">
            <!-- Admin User -->
            <div class="card">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-user-shield mr-2 text-brand-500"></i>Admin User
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Full Name *</label>
                  <input v-model="form.admin_name" type="text" class="input-field" placeholder="Tendai Moyo" />
                  <p v-if="form.errors.admin_name" class="text-sm text-danger-500 mt-1">{{ form.errors.admin_name }}</p>
                </div>
                <div>
                  <label class="label">Email *</label>
                  <input v-model="form.admin_email" type="email" class="input-field" placeholder="admin@yourstore.co.zw" />
                  <p v-if="form.errors.admin_email" class="text-sm text-danger-500 mt-1">{{ form.errors.admin_email }}</p>
                </div>
                <div>
                  <label class="label">Phone</label>
                  <input v-model="form.admin_phone" type="text" class="input-field" placeholder="+263 77 123 4567" />
                  <p v-if="form.errors.admin_phone" class="text-sm text-danger-500 mt-1">{{ form.errors.admin_phone }}</p>
                </div>
                <div>
                  <label class="label">Password *</label>
                  <input v-model="form.admin_password" type="password" class="input-field" placeholder="Minimum 8 characters" />
                  <p v-if="form.errors.admin_password" class="text-sm text-danger-500 mt-1">{{ form.errors.admin_password }}</p>
                </div>
              </div>
            </div>

            <!-- Subscription Plan -->
            <div class="card">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-credit-card mr-2 text-brand-500"></i>Subscription Plan
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Plan (leave empty for 14-day trial)</label>
                  <select v-model="form.subscription_plan_id" class="input-field">
                    <option value="">Free Trial (14 days)</option>
                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                      {{ plan.name }} - ${{ plan.price_monthly }}/mo or ${{ plan.price_yearly }}/yr
                    </option>
                  </select>
                </div>
                <div v-if="form.subscription_plan_id">
                  <label class="label">Billing Cycle</label>
                  <select v-model="form.billing_cycle" class="input-field">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div> 

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('superadmin.tenants.index')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition-colors shadow-sm disabled:opacity-50">
            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
            <i v-else class="fas fa-plus mr-2"></i>
            {{ form.processing ? 'Creating...' : 'Create Tenant' }}
          </button>
        </div>
      </form>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';

defineProps({
  plans: Array,
});

const form = useForm({
  name: '',
  trading_name: '',
  email: '',
  phone: '',
  address: '',
  website: '',
  registration_number: '',
  vat_number: '',
  default_currency: 'ZWL',
  subscription_plan_id: '',
  billing_cycle: 'monthly',
  admin_name: '',
  admin_email: '',
  admin_phone: '',
  admin_password: '',
});

const submit = () => {
  form.post(route('superadmin.tenants.store'));
};
</script>

<template>
  <AdminLayout page-title="Settings">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Company Profile</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your business information and settings</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitCompany">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left Column - Basic Information -->
          <div class="space-y-6">
            <!-- Company Details -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-building mr-2 text-brand-500"></i>Company Details
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Company Name *</label>
                  <input v-model="companyForm.name" type="text" class="input-field" placeholder="Your Company Name" required />
                  <p v-if="companyForm.errors.name" class="text-red-500 text-sm mt-1">{{ companyForm.errors.name }}</p>
                </div>
                <div>
                  <label class="label">Trading Name</label>
                  <input v-model="companyForm.trading_name" type="text" class="input-field" placeholder="Trading As (if different)" />
                  <p v-if="companyForm.errors.trading_name" class="text-red-500 text-sm mt-1">{{ companyForm.errors.trading_name }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Registration Number</label>
                    <input v-model="companyForm.registration_number" type="text" class="input-field" placeholder="Company Reg No." />
                    <p v-if="companyForm.errors.registration_number" class="text-red-500 text-sm mt-1">{{ companyForm.errors.registration_number }}</p>
                  </div>
                  <div>
                    <label class="label">VAT Number</label>
                    <input v-model="companyForm.vat_number" type="text" class="input-field" placeholder="VAT Reg No." />
                    <p v-if="companyForm.errors.vat_number" class="text-red-500 text-sm mt-1">{{ companyForm.errors.vat_number }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-address-book mr-2 text-brand-500"></i>Contact Information
              </h2>
              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Phone</label>
                    <input v-model="companyForm.phone" type="text" class="input-field" placeholder="+263 77 123 4567" />
                    <p v-if="companyForm.errors.phone" class="text-red-500 text-sm mt-1">{{ companyForm.errors.phone }}</p>
                  </div>
                  <div>
                    <label class="label">Email</label>
                    <input v-model="companyForm.email" type="email" class="input-field" placeholder="info@company.com" />
                    <p v-if="companyForm.errors.email" class="text-red-500 text-sm mt-1">{{ companyForm.errors.email }}</p>
                  </div>
                </div>
                <div>
                  <label class="label">Website</label>
                  <input v-model="companyForm.website" type="url" class="input-field" placeholder="https://www.company.com" />
                  <p v-if="companyForm.errors.website" class="text-red-500 text-sm mt-1">{{ companyForm.errors.website }}</p>
                </div>
                <div>
                  <label class="label">Address</label>
                  <textarea v-model="companyForm.address" rows="3" class="input-field" placeholder="Physical Address"></textarea>
                  <p v-if="companyForm.errors.address" class="text-red-500 text-sm mt-1">{{ companyForm.errors.address }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Business Settings -->
          <div class="space-y-6">
            <!-- Currency Settings -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-coins mr-2 text-brand-500"></i>Currency Settings
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Enabled Currencies *</label>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select all currencies your business accepts</p>
                  <div class="grid grid-cols-2 gap-2">
                    <label
                      v-for="currency in availableCurrencies"
                      :key="currency.code"
                      class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                    >
                      <input
                        type="checkbox"
                        :value="currency.code"
                        v-model="companyForm.enabled_currencies"
                        class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500"
                      />
                      <span class="text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-medium">{{ currency.code }}</span>
                        <span class="text-gray-500 dark:text-gray-400"> - {{ currency.name }}</span>
                      </span>
                    </label>
                  </div>
                  <p v-if="companyForm.errors.enabled_currencies" class="text-red-500 text-sm mt-1">{{ companyForm.errors.enabled_currencies }}</p>
                </div>
                <div>
                  <label class="label">Default Currency *</label>
                  <select v-model="companyForm.default_currency" class="input-field" required>
                    <option v-for="currency in enabledCurrencyOptions" :key="currency.code" :value="currency.code">
                      {{ currency.code }} - {{ currency.name }}
                    </option>
                  </select>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Used for reports and primary display</p>
                  <p v-if="companyForm.errors.default_currency" class="text-red-500 text-sm mt-1">{{ companyForm.errors.default_currency }}</p>
                </div>
              </div>
            </div>

            <!-- Business Settings -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-cogs mr-2 text-brand-500"></i>Business Settings
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Fiscal Year Start *</label>
                  <select v-model="companyForm.fiscal_year_start" class="input-field" required>
                    <option v-for="m in 12" :key="m" :value="m">{{ new Date(2000, m-1).toLocaleString('default', { month: 'long' }) }}</option>
                  </select>
                  <p v-if="companyForm.errors.fiscal_year_start" class="text-red-500 text-sm mt-1">{{ companyForm.errors.fiscal_year_start }}</p>
                </div>
              </div>
            </div> 

            <!-- Actions -->
            <div class="card p-6">
              <div class="flex items-center justify-end gap-3">
                <button type="button" @click="resetForm" class="btn-secondary">
                  <i class="fas fa-undo mr-2"></i>Reset
                </button>
                <button type="submit" :disabled="companyForm.processing" class="btn-primary">
                  <i :class="[companyForm.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save', 'mr-2']"></i>
                  {{ companyForm.processing ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  tenant: Object,
  availableCurrencies: Array,
  enabledCurrencies: Array,
});

const companyForm = useForm({
  name: props.tenant?.name || '',
  trading_name: props.tenant?.trading_name || '',
  registration_number: props.tenant?.registration_number || '',
  vat_number: props.tenant?.vat_number || '',
  address: props.tenant?.address || '',
  phone: props.tenant?.phone || '',
  email: props.tenant?.email || '',
  website: props.tenant?.website || '',
  default_currency: props.tenant?.default_currency || 'USD',
  enabled_currencies: props.enabledCurrencies || ['USD'],
  fiscal_year_start: props.tenant?.fiscal_year_start || 1,
});

const enabledCurrencyOptions = computed(() => {
  return props.availableCurrencies.filter(c => companyForm.enabled_currencies.includes(c.code));
});

const submitCompany = () => companyForm.put(route('admin.settings.company'));

const resetForm = () => {
  companyForm.name = props.tenant?.name || '';
  companyForm.trading_name = props.tenant?.trading_name || '';
  companyForm.registration_number = props.tenant?.registration_number || '';
  companyForm.vat_number = props.tenant?.vat_number || '';
  companyForm.address = props.tenant?.address || '';
  companyForm.phone = props.tenant?.phone || '';
  companyForm.email = props.tenant?.email || '';
  companyForm.website = props.tenant?.website || '';
  companyForm.default_currency = props.tenant?.default_currency || 'USD';
  companyForm.enabled_currencies = props.enabledCurrencies || ['USD'];
  companyForm.fiscal_year_start = props.tenant?.fiscal_year_start || 1;
};
</script>

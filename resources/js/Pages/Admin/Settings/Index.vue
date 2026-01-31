<template>
  <AdminLayout page-title="Settings">
    <div class="max-w-2xl space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>

      <!-- Company Profile -->
      <form @submit.prevent="submitCompany" class="card p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Company Profile</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="label">Company Name *</label>
            <input v-model="companyForm.name" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">Trading Name</label>
            <input v-model="companyForm.trading_name" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">Registration Number</label>
            <input v-model="companyForm.registration_number" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">VAT Number</label>
            <input v-model="companyForm.vat_number" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">Phone</label>
            <input v-model="companyForm.phone" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">Email</label>
            <input v-model="companyForm.email" type="email" class="input-field" />
          </div>
          <div>
            <label class="label">Website</label>
            <input v-model="companyForm.website" type="url" class="input-field" />
          </div>
          <div class="sm:col-span-2">
            <label class="label">Address</label>
            <textarea v-model="companyForm.address" rows="2" class="input-field"></textarea>
          </div>
          <div>
            <label class="label">Default Currency</label>
            <select v-model="companyForm.default_currency" class="input-field">
              <option value="USD">USD - US Dollar</option>
              <option value="ZWL">ZWL - Zimbabwe Dollar</option>
            </select>
          </div>
          <div>
            <label class="label">Fiscal Year Start</label>
            <select v-model="companyForm.fiscal_year_start" class="input-field">
              <option v-for="m in 12" :key="m" :value="m">{{ new Date(2000, m-1).toLocaleString('default', { month: 'long' }) }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-slate-700">
          <button type="submit" :disabled="companyForm.processing" class="btn-primary">Save Company Settings</button>
        </div>
      </form>

      <!-- Quick Links -->
      <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Configuration</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <Link :href="route('admin.settings.payment-methods')" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-credit-card text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Payment Methods</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Configure payment options</p>
            </div>
          </Link>
          <Link :href="route('admin.settings.tax')" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-percentage text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Tax Settings</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Configure tax rates</p>
            </div>
          </Link>
          <Link :href="route('admin.settings.receipt')" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-receipt text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
              <p class="font-medium text-gray-900 dark:text-white">Receipt Settings</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Customize receipts</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ company: Object });

const companyForm = useForm({
  name: props.company?.name || '',
  trading_name: props.company?.trading_name || '',
  registration_number: props.company?.registration_number || '',
  vat_number: props.company?.vat_number || '',
  address: props.company?.address || '',
  phone: props.company?.phone || '',
  email: props.company?.email || '',
  website: props.company?.website || '',
  default_currency: props.company?.default_currency || 'USD',
  fiscal_year_start: props.company?.fiscal_year_start || 1,
});

const submitCompany = () => companyForm.put(route('admin.settings.company'));
</script>




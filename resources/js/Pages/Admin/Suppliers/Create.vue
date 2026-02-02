<template>
  <AdminLayout page-title="Add Supplier">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Supplier</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Create a new supplier record</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left Column -->
          <div class="space-y-6">
            <!-- Basic Information -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-building mr-2 text-brand-500"></i>Basic Information
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Supplier Name *</label>
                  <input v-model="form.name" type="text" class="input-field" placeholder="e.g., Mutare Wholesalers (Pvt) Ltd" required />
                  <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>
                <div>
                  <label class="label">Contact Person</label>
                  <input v-model="form.contact_person" type="text" class="input-field" placeholder="e.g., Tendai Moyo" />
                  <p v-if="form.errors.contact_person" class="text-red-500 text-sm mt-1">{{ form.errors.contact_person }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Phone</label>
                    <input v-model="form.phone" type="tel" class="input-field" placeholder="e.g., +263 24 2123456" />
                    <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
                  </div>
                  <div>
                    <label class="label">Email</label>
                    <input v-model="form.email" type="email" class="input-field" placeholder="e.g., info@supplier.co.zw" />
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Address Information -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-map-marker-alt mr-2 text-brand-500"></i>Address Information
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Address</label>
                  <textarea v-model="form.address" class="input-field" rows="2" placeholder="22 Herbert Chitepo Street, Harare CBD"></textarea>
                  <p v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</p>
                </div>
                <div>
                  <label class="label">City</label>
                  <input v-model="form.city" type="text" class="input-field" placeholder="e.g., Harare, Bulawayo, Mutare" />
                  <p v-if="form.errors.city" class="text-red-500 text-sm mt-1">{{ form.errors.city }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Payment Terms -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-credit-card mr-2 text-brand-500"></i>Payment Terms
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Payment Terms</label>
                  <input v-model="form.payment_terms" type="text" class="input-field" placeholder="e.g., Net 30, COD" />
                  <p v-if="form.errors.payment_terms" class="text-red-500 text-sm mt-1">{{ form.errors.payment_terms }}</p>
                </div>
                <div>
                  <label class="label">Credit Days</label>
                  <input v-model="form.credit_days" type="number" min="0" class="input-field" placeholder="e.g., 30" />
                  <p v-if="form.errors.credit_days" class="text-red-500 text-sm mt-1">{{ form.errors.credit_days }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Number of days for payment</p>
                </div>
              </div>
            </div>

            <!-- Additional Information -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-info-circle mr-2 text-brand-500"></i>Additional Information
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Notes</label>
                  <textarea v-model="form.notes" class="input-field" rows="3" placeholder="Any additional notes about this supplier..."></textarea>
                  <p v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</p>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                  <input v-model="form.is_active" type="checkbox" id="is_active" class="w-5 h-5 text-brand-600 rounded border-gray-300 focus:ring-brand-500" />
                  <label for="is_active" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">Active Supplier</label>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="card p-6">
              <div class="flex items-center justify-end gap-3">
                <Link :href="route('admin.suppliers.index')" class="btn-secondary">
                  <i class="fas fa-times mr-2"></i>Cancel
                </Link>
                <button type="submit" class="btn-primary" :disabled="form.processing">
                  <i class="fas fa-save mr-2"></i>{{ form.processing ? 'Saving...' : 'Save Supplier' }}
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
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
  name: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  city: '',
  payment_terms: '',
  credit_days: '',
  notes: '',
  is_active: true,
});

const submit = () => {
  form.post(route('admin.suppliers.store'));
};
</script>

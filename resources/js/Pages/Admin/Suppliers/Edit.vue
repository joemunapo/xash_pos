<template>
  <AdminLayout page-title="Edit Supplier">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Supplier</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Update supplier information</p>
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
                  <input v-model="form.name" type="text" class="input-field" required />
                  <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>
                <div>
                  <label class="label">Contact Person</label>
                  <input v-model="form.contact_person" type="text" class="input-field" />
                  <p v-if="form.errors.contact_person" class="text-red-500 text-sm mt-1">{{ form.errors.contact_person }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Phone</label>
                    <input v-model="form.phone" type="tel" class="input-field" />
                    <p v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</p>
                  </div>
                  <div>
                    <label class="label">Email</label>
                    <input v-model="form.email" type="email" class="input-field" />
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
                  <textarea v-model="form.address" class="input-field" rows="2"></textarea>
                  <p v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</p>
                </div>
                <div>
                  <label class="label">City</label>
                  <input v-model="form.city" type="text" class="input-field" />
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
                  <input v-model="form.payment_terms" type="text" class="input-field" />
                  <p v-if="form.errors.payment_terms" class="text-red-500 text-sm mt-1">{{ form.errors.payment_terms }}</p>
                </div>
                <div>
                  <label class="label">Credit Days</label>
                  <input v-model="form.credit_days" type="number" min="0" class="input-field" />
                  <p v-if="form.errors.credit_days" class="text-red-500 text-sm mt-1">{{ form.errors.credit_days }}</p>
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
                  <textarea v-model="form.notes" class="input-field" rows="3"></textarea>
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
                  <i class="fas fa-save mr-2"></i>{{ form.processing ? 'Updating...' : 'Update Supplier' }}
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

const props = defineProps({
  supplier: Object,
});

const form = useForm({
  name: props.supplier.name,
  contact_person: props.supplier.contact_person,
  phone: props.supplier.phone,
  email: props.supplier.email,
  address: props.supplier.address,
  city: props.supplier.city,
  payment_terms: props.supplier.payment_terms,
  credit_days: props.supplier.credit_days,
  notes: props.supplier.notes,
  is_active: props.supplier.is_active,
});

const submit = () => {
  form.put(route('admin.suppliers.update', props.supplier.id));
};
</script>

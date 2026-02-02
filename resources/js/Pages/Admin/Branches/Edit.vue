<template>
  <AdminLayout page-title="Edit Branch">
    <div class="max-w-2xl">
      <!-- Header -->
      <div class="mb-6">
        <Link :href="route('admin.branches.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Branches
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Branch</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Update branch details</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="card p-6 space-y-6">
        <!-- Basic Info -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Branch Name *</label>
              <input v-model="form.name" type="text" class="input-field" />
              <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="label">Branch Code</label>
              <input v-model="form.code" type="text" class="input-field" />
              <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</p>
            </div>
          </div>
        </div>

        <!-- Location -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Location</h3>
          
          <div>
            <label class="label">Address</label>
            <textarea v-model="form.address" rows="2" class="input-field"></textarea>
          </div>

          <div>
            <label class="label">City</label>
            <input v-model="form.city" type="text" class="input-field" />
          </div>
        </div>

        <!-- Contact -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Contact</h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Phone</label>
              <input v-model="form.phone" type="text" class="input-field" />
            </div>
            <div>
              <label class="label">Email</label>
              <input v-model="form.email" type="email" class="input-field" />
            </div>
          </div>
        </div>

        <!-- Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Settings</h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Currency *</label>
              <select v-model="form.currency" class="input-field">
                <option value="USD">USD - US Dollar</option>
                <option value="ZWL">ZWL - Zimbabwe Dollar</option>
              </select>
            </div>
            <div>
              <label class="label">Tax Rate (%)</label>
              <input v-model="form.tax_rate" type="number" step="0.01" min="0" max="100" class="input-field" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Opening Time</label>
              <input v-model="form.opening_time" type="time" class="input-field" />
            </div>
            <div>
              <label class="label">Closing Time</label>
              <input v-model="form.closing_time" type="time" class="input-field" />
            </div>
          </div>

          <div class="flex items-center gap-3">
            <input type="checkbox" v-model="form.is_active" id="is_active" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500" />
            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Branch is active</label>
          </div>
        </div>

        <!-- Receipt Settings -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Receipt Settings</h3>
          
          <div>
            <label class="label">Receipt Header</label>
            <input v-model="form.receipt_header" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">Receipt Footer</label>
            <input v-model="form.receipt_footer" type="text" class="input-field" />
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link :href="route('admin.branches.index')" class="btn-secondary">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  branch: Object,
});

const form = useForm({
  name: props.branch.name,
  code: props.branch.code || '',
  address: props.branch.address || '',
  city: props.branch.city || '',
  phone: props.branch.phone || '',
  email: props.branch.email || '',
  currency: props.branch.currency,
  tax_rate: props.branch.tax_rate,
  opening_time: props.branch.opening_time || '',
  closing_time: props.branch.closing_time || '',
  receipt_header: props.branch.receipt_header || '',
  receipt_footer: props.branch.receipt_footer || '',
  is_active: props.branch.is_active,
});

const submit = () => {
  form.put(route('admin.branches.update', props.branch.id));
};
</script>




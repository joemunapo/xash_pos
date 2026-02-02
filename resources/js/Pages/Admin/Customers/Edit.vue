<template>
  <AdminLayout page-title="Edit Customer">
    <div class="max-w-lg">
      <div class="mb-6">
        <Link :href="route('admin.customers.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Customers
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Customer</h1>
      </div>

      <form @submit.prevent="submit" class="card p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">First Name *</label>
            <input v-model="form.first_name" type="text" class="input-field" />
          </div>
          <div>
            <label class="label">Last Name</label>
            <input v-model="form.last_name" type="text" class="input-field" />
          </div>
        </div>
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
        <div>
          <label class="label">City</label>
          <input v-model="form.city" type="text" class="input-field" />
        </div>
        <div>
          <label class="label">Address</label>
          <textarea v-model="form.address" rows="2" class="input-field"></textarea>
        </div>
        <div class="flex items-center gap-3">
          <input type="checkbox" v-model="form.is_active" id="is_active" class="w-4 h-4 text-brand-600 rounded" />
          <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Customer is active</label>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link :href="route('admin.customers.index')" class="btn-secondary">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ customer: Object });

const form = useForm({
  first_name: props.customer.first_name,
  last_name: props.customer.last_name || '',
  phone: props.customer.phone || '',
  email: props.customer.email || '',
  city: props.customer.city || '',
  address: props.customer.address || '',
  is_active: props.customer.is_active,
});

const submit = () => form.put(route('admin.customers.update', props.customer.id));
</script>




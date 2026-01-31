<template>
  <AdminLayout page-title="Create Branch">
    <div class="max-w-3xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <Link :href="route('admin.branches.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Branches
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Branch</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new store location to your business</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Basic Information</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Name <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Main Branch" />
              <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Code</label>
              <input v-model="form.code" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="BR001" />
              <p v-if="form.errors.code" class="text-red-500 text-xs mt-1">{{ form.errors.code }}</p>
            </div>
          </div>
        </div>

        <!-- Location -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Location</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
              <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none" placeholder="Street address"></textarea>
            </div>
            <div class="sm:w-1/2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
              <input v-model="form.city" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Harare" />
            </div>
          </div>
        </div>

        <!-- Contact -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Contact</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
              <input v-model="form.phone" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="+263771234567" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
              <input v-model="form.email" type="email" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="branch@example.com" />
            </div>
          </div>
        </div>

        <!-- Receipt Settings -->
        <div class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Receipt Settings</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receipt Header</label>
              <input v-model="form.receipt_header" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Welcome to Our Store" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receipt Footer</label>
              <input v-model="form.receipt_footer" type="text" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Thank you for shopping!" />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('admin.branches.index')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
            Cancel
          </Link>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors">
            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
            Create Branch
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useFlashMessages } from '@/Composables/useFlashMessages';

// Initialize flash messages to display SweetAlert notifications
useFlashMessages();

const form = useForm({
  name: '',
  code: '',
  address: '',
  city: '',
  phone: '',
  email: '',
  receipt_header: '',
  receipt_footer: '',
});

const submit = () => {
  form.post(route('admin.branches.store'));
};
</script>




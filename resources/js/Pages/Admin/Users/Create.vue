<template>
  <AdminLayout page-title="Create User">
    <div class="max-w-2xl">
      <div class="mb-6">
        <Link :href="route('admin.users.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create User</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new staff member</p>
      </div>

      <form @submit.prevent="submit" class="card p-6 space-y-8">
        <!-- Basic Info -->
        <div class="space-y-6">
          <div class="pb-2 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Information</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Basic user details and credentials</p>
          </div>
          
          <div>
            <label class="label">Full Name *</label>
            <input v-model="form.name" type="text" class="input-field" placeholder="John Doe" required />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Email *</label>
              <input v-model="form.email" type="email" class="input-field" placeholder="john@example.com" required />
              <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
              <label class="label">Phone</label>
              <input v-model="form.phone_number" type="text" class="input-field" placeholder="+263771234567" />
              <p v-if="form.errors.phone_number" class="text-red-500 text-sm mt-1">{{ form.errors.phone_number }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Password *</label>
              <input v-model="form.password" type="password" class="input-field" placeholder="••••••••" required />
              <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
              <label class="label">PIN (4-6 digits)</label>
              <input v-model="form.pin" type="text" maxlength="6" class="input-field" placeholder="1234" />
              <p v-if="form.errors.pin" class="text-red-500 text-sm mt-1">{{ form.errors.pin }}</p>
              <p v-else class="text-xs text-gray-500 dark:text-gray-400 mt-1">For POS device login</p>
            </div>
          </div>
        </div>

        <!-- Role & Permissions -->
        <div class="space-y-6">
          <div class="pb-2 border-b border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Role & Access</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define user permissions and branch assignments</p>
          </div>
          
          <div>
            <label class="label">Role *</label>
            <select v-model="form.role" class="input-field" required>
              <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
            </select>
            <p v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</p>
          </div>

          <div>
            <label class="label">Assign to Branches</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
              <label v-for="branch in branches" :key="branch.id" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                <input type="checkbox" :value="branch.id" v-model="form.branches" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500" />
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ branch.name }}</span>
              </label>
            </div>
            <p v-if="form.errors.branches" class="text-red-500 text-sm mt-1">{{ form.errors.branches }}</p>
          </div>

          <div v-if="form.branches.length > 0">
            <label class="label">Primary Branch *</label>
            <select v-model="form.primary_branch_id" class="input-field" required>
              <option v-for="branchId in form.branches" :key="branchId" :value="branchId">
                {{ branches.find(b => b.id === branchId)?.name }}
              </option>
            </select>
            <p v-if="form.errors.primary_branch_id" class="text-red-500 text-sm mt-1">{{ form.errors.primary_branch_id }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-slate-700">
          <Link :href="route('admin.users.index')" class="btn-secondary">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="btn-primary">
            <i v-if="form.processing" class="fas fa-spinner fa-spin mr-2"></i>
            Create User
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  branches: Array,
  roles: Array,
});

const form = useForm({
  name: '',
  email: '',
  phone_number: '',
  password: '',
  pin: '',
  role: 'cashier',
  branches: [],
  primary_branch_id: null,
});

watch(() => form.branches, (newBranches) => {
  if (newBranches.length > 0 && !newBranches.includes(form.primary_branch_id)) {
    form.primary_branch_id = newBranches[0];
  }
});

const submit = () => {
  form.post(route('admin.users.store'));
};
</script>




<template>
  <AdminLayout page-title="Edit User">
    <div class="max-w-2xl">
      <div class="mb-6">
        <Link :href="route('admin.users.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Update user details and permissions</p>
      </div>

      <form @submit.prevent="submit" class="card p-6 space-y-6">
        <!-- Basic Info -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Account Information</h3>
          
          <div>
            <label class="label">Full Name *</label>
            <input v-model="form.name" type="text" class="input-field" />
            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Email *</label>
              <input v-model="form.email" type="email" class="input-field" />
              <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
              <label class="label">Phone</label>
              <input v-model="form.phone_number" type="text" class="input-field" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">New Password</label>
              <input v-model="form.password" type="password" class="input-field" placeholder="Leave blank to keep current" />
              <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
              <label class="label">New PIN</label>
              <input v-model="form.pin" type="text" maxlength="6" class="input-field" placeholder="Leave blank to keep current" />
            </div>
          </div>

          <div class="flex items-center gap-3">
            <input type="checkbox" v-model="form.is_active" id="is_active" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500" />
            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">User is active</label>
          </div>
        </div>

        <!-- Role & Permissions -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Role & Access</h3>
          
          <div>
            <label class="label">Role *</label>
            <select v-model="form.role" class="input-field">
              <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
            </select>
          </div>

          <div>
            <label class="label">Assign to Branches</label>
            <div class="grid grid-cols-2 gap-2 mt-2">
              <label v-for="branch in branches" :key="branch.id" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                <input type="checkbox" :value="branch.id" v-model="form.branches" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500" />
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ branch.name }}</span>
              </label>
            </div>
          </div>

          <div v-if="form.branches.length > 0">
            <label class="label">Primary Branch</label>
            <select v-model="form.primary_branch_id" class="input-field">
              <option v-for="branchId in form.branches" :key="branchId" :value="branchId">
                {{ branches.find(b => b.id === branchId)?.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link :href="route('admin.users.index')" class="btn-secondary">Cancel</Link>
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
import { watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  user: Object,
  branches: Array,
  userBranchIds: Array,
  roles: Array,
});

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  phone_number: props.user.phone_number || '',
  password: '',
  pin: '',
  role: props.user.role,
  is_active: props.user.is_active,
  branches: props.userBranchIds || [],
  primary_branch_id: props.user.branches?.find(b => b.pivot?.is_primary)?.id || null,
});

watch(() => form.branches, (newBranches) => {
  if (newBranches.length > 0 && !newBranches.includes(form.primary_branch_id)) {
    form.primary_branch_id = newBranches[0];
  }
});

const submit = () => {
  form.put(route('admin.users.update', props.user.id));
};
</script>




<template>
  <AdminLayout page-title="Edit User">
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Update user details and permissions</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Left Column -->
          <div class="space-y-6">
            <!-- Account Information -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-user mr-2 text-brand-500"></i>Account Information
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Full Name *</label>
                  <input v-model="form.name" type="text" class="input-field" required />
                  <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label">Email *</label>
                    <input v-model="form.email" type="email" class="input-field" required />
                    <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</p>
                  </div>
                  <div>
                    <label class="label">Phone</label>
                    <input v-model="form.phone_number" type="text" class="input-field" />
                    <p v-if="form.errors.phone_number" class="text-red-500 text-sm mt-1">{{ form.errors.phone_number }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                  <input type="checkbox" v-model="form.is_active" id="is_active" class="w-5 h-5 text-brand-600 rounded border-gray-300 focus:ring-brand-500" />
                  <label for="is_active" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">User is active</label>
                </div>
              </div>
            </div>

            <!-- Security -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-lock mr-2 text-brand-500"></i>Security
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">New Password</label>
                  <input v-model="form.password" type="password" class="input-field" placeholder="Leave blank to keep current" />
                  <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</p>
                </div>
                <div>
                  <label class="label">New PIN</label>
                  <input v-model="form.pin" type="text" maxlength="6" class="input-field" placeholder="Leave blank to keep current" />
                  <p v-if="form.errors.pin" class="text-red-500 text-sm mt-1">{{ form.errors.pin }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Role Selection -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-user-tag mr-2 text-brand-500"></i>Role
              </h2>
              <div>
                <label class="label">User Role *</label>
                <select v-model="form.role" class="input-field" required>
                  <option v-for="role in roles" :key="role.value" :value="role.value">
                    {{ role.label }}
                  </option>
                </select>
                <p v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</p>
              </div>
            </div>

            <!-- Branch Assignment -->
            <div class="card p-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-slate-700">
                <i class="fas fa-store mr-2 text-brand-500"></i>Branch Assignment
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="label">Assign to Branches</label>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                    <label v-for="branch in branches" :key="branch.id" class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-slate-800 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                      <input type="checkbox" :value="branch.id" v-model="form.branches" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500" />
                      <span class="text-sm text-gray-700 dark:text-gray-300">{{ branch.name }}</span>
                    </label>
                  </div>
                  <p v-if="form.errors.branches" class="text-red-500 text-sm mt-1">{{ form.errors.branches }}</p>
                </div>

                <div v-if="form.branches.length > 0">
                  <label class="label">Primary Branch</label>
                  <select v-model="form.primary_branch_id" class="input-field">
                    <option v-for="branchId in form.branches" :key="branchId" :value="branchId">
                      {{ branches.find(b => b.id === branchId)?.name }}
                    </option>
                  </select>
                  <p v-if="form.errors.primary_branch_id" class="text-red-500 text-sm mt-1">{{ form.errors.primary_branch_id }}</p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="card p-6">
              <div class="flex items-center justify-end gap-3">
                <Link :href="route('admin.users.index')" class="btn-secondary">
                  <i class="fas fa-times mr-2"></i>Cancel
                </Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">
                  <i :class="[form.processing ? 'fas fa-spinner fa-spin' : 'fas fa-save', 'mr-2']"></i>
                  {{ form.processing ? 'Saving...' : 'Save Changes' }}
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

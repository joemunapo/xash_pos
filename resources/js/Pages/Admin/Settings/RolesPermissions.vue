<template>
  <AdminLayout page-title="Roles & Permissions">
    <div class="max-w-6xl space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roles & Permissions</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage user roles and their permissions</p>
        </div>
      </div>

      <!-- Roles Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="role in roles"
          :key="role.value"
          class="card p-6 hover:shadow-xl transition-shadow duration-300 relative overflow-hidden group"
        >
          <div class="absolute top-0 right-0 w-24 h-24 transform translate-x-8 -translate-y-8">
            <div :class="`${role.color} opacity-10 rounded-full w-full h-full`"></div>
          </div>
          <div class="relative">
            <div :class="`${role.iconBg} w-12 h-12 rounded-xl flex items-center justify-center mb-4`">
              <i :class="`${role.icon} ${role.iconColor} text-xl`"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white capitalize mb-2">
              {{ role.label }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              {{ role.description }}
            </p>
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
              <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ role.userCount }} {{ role.userCount === 1 ? 'user' : 'users' }}
              </span>
              <button
                @click="editRole(role)"
                class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 text-sm font-medium"
              >
                Configure <i class="fas fa-arrow-right ml-1"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Permissions Matrix -->
      <div class="card overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-shield-alt text-brand-500"></i>
            Permissions Matrix
          </h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Manage what each role can do in the system
          </p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Module / Action
                </th>
                <th
                  v-for="role in roles"
                  :key="role.value"
                  class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >
                  <div class="flex flex-col items-center gap-1">
                    <i :class="`${role.icon} ${role.iconColor}`"></i>
                    <span>{{ role.label }}</span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 divide-y divide-gray-200 dark:divide-slate-700">
              <template v-for="(module, moduleKey) in permissionsMatrix" :key="moduleKey">
                <!-- Module Header -->
                <tr class="bg-gray-50/50 dark:bg-slate-800/50">
                  <td colspan="5" class="px-6 py-3 text-sm font-semibold text-gray-900 dark:text-white">
                    <i :class="`${module.icon} mr-2 text-gray-500 dark:text-gray-400`"></i>
                    {{ module.name }}
                  </td>
                </tr>
                <!-- Permissions -->
                <tr v-for="permission in module.permissions" :key="permission.key">
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                    <div class="flex items-center gap-2">
                      <i :class="`${permission.icon} text-gray-400 w-4`"></i>
                      {{ permission.label }}
                    </div>
                  </td>
                  <td
                    v-for="role in roles"
                    :key="role.value"
                    class="px-6 py-4 text-center"
                  >
                    <div class="flex justify-center">
                      <input
                        type="checkbox"
                        :checked="hasPermission(role.value, moduleKey, permission.key)"
                        @change="togglePermission(role.value, moduleKey, permission.key)"
                        :disabled="role.value === 'admin'"
                        class="w-5 h-5 text-brand-600 rounded border-gray-300 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed"
                      />
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="p-6 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50">
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              <i class="fas fa-info-circle mr-1"></i>
              Admin role has all permissions by default and cannot be modified
            </p>
            <button @click="savePermissions" class="btn-primary">
              <i class="fas fa-save mr-2"></i>
              Save Changes
            </button>
          </div>
        </div>
      </div>

      <!-- Role Descriptions -->
      <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <i class="fas fa-info-circle text-blue-500"></i>
          Role Descriptions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="role in roles" :key="role.value" class="p-4 bg-gray-50 dark:bg-slate-800 rounded-lg">
            <h3 :class="`text-base font-semibold capitalize mb-2 ${role.textColor}`">
              <i :class="`${role.icon} mr-2`"></i>
              {{ role.label }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ role.description }}</p>
            <ul class="mt-3 space-y-1">
              <li v-for="feature in role.features" :key="feature" class="text-sm text-gray-700 dark:text-gray-300 flex items-start gap-2">
                <i class="fas fa-check text-brand-500 mt-0.5"></i>
                <span>{{ feature }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  userCounts: Object,
  currentPermissions: Object,
});

const roles = ref([
  {
    value: 'admin',
    label: 'Admin',
    description: 'Full system access with all permissions',
    icon: 'fas fa-user-shield',
    iconColor: 'text-red-600 dark:text-red-400',
    iconBg: 'bg-red-100 dark:bg-red-900/30',
    color: 'bg-red-500',
    textColor: 'text-red-600 dark:text-red-400',
    userCount: props.userCounts?.admin || 0,
    features: [
      'Complete system control',
      'Manage all users and roles',
      'Access all reports and analytics',
      'Configure system settings',
    ],
  },
  {
    value: 'manager',
    label: 'Manager',
    description: 'Branch management and reporting access',
    icon: 'fas fa-user-tie',
    iconColor: 'text-blue-600 dark:text-blue-400',
    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
    color: 'bg-blue-500',
    textColor: 'text-blue-600 dark:text-blue-400',
    userCount: props.userCounts?.manager || 0,
    features: [
      'Manage branch operations',
      'View sales reports',
      'Manage inventory',
      'Handle refunds and adjustments',
    ],
  },
  {
    value: 'cashier',
    label: 'Cashier',
    description: 'POS operations and customer transactions',
    icon: 'fas fa-cash-register',
    iconColor: 'text-brand-600 dark:text-brand-400',
    iconBg: 'bg-brand-100 dark:bg-brand-900/30',
    color: 'bg-brand-500',
    textColor: 'text-brand-600 dark:text-brand-400',
    userCount: props.userCounts?.cashier || 0,
    features: [
      'Process sales transactions',
      'Handle customer payments',
      'View product information',
      'Print receipts',
    ],
  },
  {
    value: 'stockist',
    label: 'Stockist',
    description: 'Inventory and stock management',
    icon: 'fas fa-boxes',
    iconColor: 'text-purple-600 dark:text-purple-400',
    iconBg: 'bg-purple-100 dark:bg-purple-900/30',
    color: 'bg-purple-500',
    textColor: 'text-purple-600 dark:text-purple-400',
    userCount: props.userCounts?.stockist || 0,
    features: [
      'Manage product inventory',
      'Stock adjustments',
      'Receive deliveries',
      'Track stock levels',
    ],
  },
]);

const permissionsMatrix = ref({
  products: {
    name: 'Products',
    icon: 'fas fa-box',
    permissions: [
      { key: 'view', label: 'View Products', icon: 'fas fa-eye' },
      { key: 'create', label: 'Create Products', icon: 'fas fa-plus' },
      { key: 'edit', label: 'Edit Products', icon: 'fas fa-edit' },
      { key: 'delete', label: 'Delete Products', icon: 'fas fa-trash' },
    ],
  },
  sales: {
    name: 'Sales & POS',
    icon: 'fas fa-cash-register',
    permissions: [
      { key: 'process', label: 'Process Sales', icon: 'fas fa-shopping-cart' },
      { key: 'refund', label: 'Process Refunds', icon: 'fas fa-undo' },
      { key: 'view_history', label: 'View Sales History', icon: 'fas fa-history' },
      { key: 'view_reports', label: 'View Sales Reports', icon: 'fas fa-chart-line' },
    ],
  },
  inventory: {
    name: 'Inventory',
    icon: 'fas fa-warehouse',
    permissions: [
      { key: 'view', label: 'View Inventory', icon: 'fas fa-eye' },
      { key: 'adjust', label: 'Adjust Stock', icon: 'fas fa-sliders-h' },
      { key: 'transfer', label: 'Transfer Stock', icon: 'fas fa-exchange-alt' },
      { key: 'receive', label: 'Receive Stock', icon: 'fas fa-dolly' },
    ],
  },
  customers: {
    name: 'Customers',
    icon: 'fas fa-users',
    permissions: [
      { key: 'view', label: 'View Customers', icon: 'fas fa-eye' },
      { key: 'create', label: 'Create Customers', icon: 'fas fa-plus' },
      { key: 'edit', label: 'Edit Customers', icon: 'fas fa-edit' },
      { key: 'delete', label: 'Delete Customers', icon: 'fas fa-trash' },
    ],
  },
  users: {
    name: 'User Management',
    icon: 'fas fa-user-cog',
    permissions: [
      { key: 'view', label: 'View Users', icon: 'fas fa-eye' },
      { key: 'create', label: 'Create Users', icon: 'fas fa-plus' },
      { key: 'edit', label: 'Edit Users', icon: 'fas fa-edit' },
      { key: 'delete', label: 'Delete Users', icon: 'fas fa-trash' },
    ],
  },
  settings: {
    name: 'Settings',
    icon: 'fas fa-cog',
    permissions: [
      { key: 'view', label: 'View Settings', icon: 'fas fa-eye' },
      { key: 'edit_company', label: 'Edit Company Info', icon: 'fas fa-building' },
      { key: 'edit_payment', label: 'Configure Payments', icon: 'fas fa-credit-card' },
      { key: 'edit_tax', label: 'Configure Tax', icon: 'fas fa-percent' },
    ],
  },
});

const rolePermissions = reactive(props.currentPermissions || {
  manager: {
    products: ['view', 'create', 'edit'],
    sales: ['process', 'refund', 'view_history', 'view_reports'],
    inventory: ['view', 'adjust', 'transfer', 'receive'],
    customers: ['view', 'create', 'edit'],
    users: ['view'],
    settings: ['view'],
  },
  cashier: {
    products: ['view'],
    sales: ['process', 'view_history'],
    inventory: ['view'],
    customers: ['view', 'create'],
    users: [],
    settings: [],
  },
  stockist: {
    products: ['view', 'edit'],
    sales: [],
    inventory: ['view', 'adjust', 'transfer', 'receive'],
    customers: [],
    users: [],
    settings: [],
  },
});

const hasPermission = (role, module, permission) => {
  if (role === 'admin') return true;
  return rolePermissions[role]?.[module]?.includes(permission) || false;
};

const togglePermission = (role, module, permission) => {
  if (role === 'admin') return;
  
  if (!rolePermissions[role]) {
    rolePermissions[role] = {};
  }
  if (!rolePermissions[role][module]) {
    rolePermissions[role][module] = [];
  }
  
  const index = rolePermissions[role][module].indexOf(permission);
  if (index > -1) {
    rolePermissions[role][module].splice(index, 1);
  } else {
    rolePermissions[role][module].push(permission);
  }
};

const savePermissions = () => {
  router.post(route('admin.settings.roles.update'), {
    permissions: rolePermissions,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Show success message
    },
  });
};

const editRole = (role) => {
  // Could open a modal or navigate to role edit page
  console.log('Edit role:', role);
};
</script>

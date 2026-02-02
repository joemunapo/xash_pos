<template>
  <AdminLayout page-title="Employee Performance">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Employee Performance</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Track staff sales performance and productivity</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="card p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="label">Start Date</label>
            <input v-model="filterForm.start_date" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">End Date</label>
            <input v-model="filterForm.end_date" type="date" class="input-field" @change="applyFilters" />
          </div>
          <div>
            <label class="label">Branch</label>
            <select v-model="filterForm.branch_id" class="input-field" @change="applyFilters">
              <option value="">All Branches</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Employee</label>
            <select v-model="filterForm.employee_id" class="input-field" @change="applyFilters">
              <option value="">All Employees</option>
              <option v-for="emp in allEmployees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="resetFilters" class="btn-secondary w-full h-[42px] justify-center">
              <i class="fas fa-undo mr-2"></i>Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Performance Table -->
      <div class="card overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            <i class="fas fa-users mr-2 text-brand-500"></i>All Employees
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Sales</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transactions</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg. Sale</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Items Sold</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="(employee, index) in employees" :key="employee.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-6 py-4 text-sm">
                  <span :class="['inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm', getRankClass(index)]">
                    {{ index + 1 }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold">
                      {{ employee.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ employee.name }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ employee.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white capitalize">{{ employee.role }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-brand-600 dark:text-brand-400 text-right">${{ formatNumber(employee.total_sales) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ employee.total_transactions }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">${{ formatNumber(employee.average_sale) }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white text-right">{{ formatNumber(employee.total_items) }}</td>
              </tr>
              <tr v-if="employees.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <i class="fas fa-users text-4xl mb-2 opacity-50"></i>
                  <p>No employee data for this period</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  employees: Array,
  allEmployees: Array,
  branches: Array,
  filters: Object,
});

const filterForm = reactive({
  start_date: props.filters.start_date,
  end_date: props.filters.end_date,
  branch_id: props.filters.branch_id || '',
  employee_id: props.filters.employee_id || '',
});

const applyFilters = () => {
  router.get(route('admin.reports.employee-performance'), filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filterForm.start_date = '';
  filterForm.end_date = '';
  filterForm.branch_id = '';
  filterForm.employee_id = '';
  applyFilters();
};

const formatNumber = (value) => {
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
};

const getRankClass = (index) => {
  if (index === 0) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
  if (index === 1) return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
  if (index === 2) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
  return 'bg-gray-50 text-gray-600 dark:bg-slate-800 dark:text-gray-400';
};
</script>

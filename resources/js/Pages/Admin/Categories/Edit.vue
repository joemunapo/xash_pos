<template>
  <AdminLayout page-title="Edit Category">
    <div class="max-w-lg">
      <div class="mb-6">
        <Link :href="route('admin.categories.index')" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
          <i class="fas fa-arrow-left mr-2"></i> Back to Categories
        </Link>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Category</h1>
      </div>

      <form @submit.prevent="submit" class="card p-6 space-y-4">
        <div>
          <label class="label">Category Name *</label>
          <input v-model="form.name" type="text" class="input-field" />
        </div>
        <div>
          <label class="label">Parent Category</label>
          <select v-model="form.parent_id" class="input-field">
            <option value="">None (Top Level)</option>
            <option v-for="cat in parentCategories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
        <div>
          <label class="label">Description</label>
          <textarea v-model="form.description" rows="3" class="input-field"></textarea>
        </div>
        <div>
          <label class="label">Sort Order</label>
          <input v-model="form.sort_order" type="number" min="0" class="input-field" />
        </div>
        <div class="flex items-center gap-3">
          <input type="checkbox" v-model="form.is_active" id="is_active" class="w-4 h-4 text-brand-600 rounded" />
          <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Category is active</label>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link :href="route('admin.categories.index')" class="btn-secondary">Cancel</Link>
          <button type="submit" :disabled="form.processing" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ category: Object, parentCategories: Array });

const form = useForm({
  name: props.category.name,
  parent_id: props.category.parent_id || '',
  description: props.category.description || '',
  sort_order: props.category.sort_order,
  is_active: props.category.is_active,
});

const submit = () => form.put(route('admin.categories.update', props.category.id));
</script>




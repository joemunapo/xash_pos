<template>
  <AdminLayout page-title="Categories">
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Organize your products</p>
        </div>
        <button @click="openCreateModal" class="btn-primary">
          <i class="fas fa-plus mr-2"></i> Add Category
        </button>
      </div>

      <div class="card overflow-hidden">
        <table class="w-full">
          <thead>
            <tr class="border-b border-gray-200 dark:border-slate-700">
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Category</th>
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Parent</th>
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Products</th>
              <th class="text-left py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Status</th>
              <th class="text-right py-3 px-4 text-sm font-medium text-gray-600 dark:text-gray-400">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="category in categories.data" :key="category.id" class="border-b border-gray-100 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800">
              <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-success-100 dark:bg-success-900/30 rounded-lg flex items-center justify-center">
                    <i class="fas fa-folder text-success-600 dark:text-success-400"></i>
                  </div>
                  <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
                </div>
              </td>
              <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ category.parent?.name || '-' }}</td>
              <td class="py-3 px-4 text-gray-900 dark:text-white">{{ category.products_count }}</td>
              <td class="py-3 px-4">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', category.is_active ? 'bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400' : 'bg-gray-100 text-gray-600']">
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(category)" class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="confirmDelete(category)" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="categories.data.length === 0" class="text-center py-12">
          <i class="fas fa-folder text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
          <p class="text-gray-500 dark:text-gray-400">No categories yet</p>
        </div>
      </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="closeCategoryModal"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          {{ editingCategory ? 'Edit Category' : 'Create Category' }}
        </h3>
        <form @submit.prevent="submitCategory" class="space-y-4">
          <div>
            <label class="label">Category Name *</label>
            <input v-model="categoryForm.name" type="text" class="input-field" placeholder="Enter category name" />
            <p v-if="categoryForm.errors.name" class="text-red-500 text-sm mt-1">{{ categoryForm.errors.name }}</p>
          </div>
          <div>
            <label class="label">Parent Category</label>
            <select v-model="categoryForm.parent_id" class="input-field">
              <option value="">None (Top Level)</option>
              <option v-for="cat in availableParents" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Description</label>
            <textarea v-model="categoryForm.description" rows="3" class="input-field" placeholder="Optional description"></textarea>
          </div>
          <div>
            <label class="label">Sort Order</label>
            <input v-model="categoryForm.sort_order" type="number" min="0" class="input-field" placeholder="0" />
          </div>
          <div v-if="editingCategory" class="flex items-center gap-2">
            <input type="checkbox" v-model="categoryForm.is_active" id="is_active" class="w-4 h-4 text-brand-600 rounded" />
            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
            <button type="button" @click="closeCategoryModal" class="btn-secondary">Cancel</button>
            <button type="submit" :disabled="categoryForm.processing" class="btn-primary">
              {{ editingCategory ? 'Update' : 'Create' }} Category
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
      <div class="relative bg-white dark:bg-slate-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delete Category</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Delete <strong>{{ categoryToDelete?.name }}</strong>?</p>
        <div class="flex justify-end gap-3">
          <button @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
          <button @click="deleteCategory" class="btn-danger">Delete</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ categories: Object, parentCategories: Array, filters: Object });

const showCategoryModal = ref(false);
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);
const editingCategory = ref(null);

const categoryForm = useForm({
  name: '',
  parent_id: '',
  description: '',
  sort_order: 0,
  is_active: true,
});

const availableParents = computed(() => {
  if (editingCategory.value) {
    return props.parentCategories.filter(cat => cat.id !== editingCategory.value.id);
  }
  return props.parentCategories;
});

const openCreateModal = () => {
  editingCategory.value = null;
  categoryForm.reset();
  categoryForm.clearErrors();
  showCategoryModal.value = true;
};

const openEditModal = (category) => {
  editingCategory.value = category;
  categoryForm.name = category.name;
  categoryForm.parent_id = category.parent_id || '';
  categoryForm.description = category.description || '';
  categoryForm.sort_order = category.sort_order || 0;
  categoryForm.is_active = category.is_active;
  categoryForm.clearErrors();
  showCategoryModal.value = true;
};

const closeCategoryModal = () => {
  showCategoryModal.value = false;
  editingCategory.value = null;
  categoryForm.reset();
};

const submitCategory = () => {
  if (editingCategory.value) {
    categoryForm.put(route('admin.categories.update', editingCategory.value.id), {
      onSuccess: () => closeCategoryModal(),
    });
  } else {
    categoryForm.post(route('admin.categories.store'), {
      onSuccess: () => closeCategoryModal(),
    });
  }
};

const confirmDelete = (category) => { categoryToDelete.value = category; showDeleteModal.value = true; };
const deleteCategory = () => {
  router.delete(route('admin.categories.destroy', categoryToDelete.value.id), {
    onSuccess: () => { showDeleteModal.value = false; }
  });
};
</script>

<template>
  <div class="mb-6 flex items-center">
    <input
      :id="id"
      type="checkbox"
      :checked="modelValue"
      @change="$emit('update:modelValue', $event.target.checked)"
      :class="[
        'w-4 h-4 border-gray-300 dark:border-gray-600 rounded cursor-pointer',
        'text-brand-600 dark:text-brand-500',
        'focus:ring-2 focus:ring-brand-500 dark:focus:ring-brand-400',
        'accent-brand-600 dark:accent-brand-500'
      ]"
    />

    <label
      :for="id"
      class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
    >
      <slot>{{ label }}</slot>
    </label>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    modelValue: boolean;
    label?: string;
    id?: string;
  }>(),
  {
    label: '',
  }
);

defineEmits<{
  'update:modelValue': [value: boolean];
}>();

// Generate unique ID if not provided
const id = computed(() => props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
input[type='checkbox']:focus {
  outline: none;
}
</style>

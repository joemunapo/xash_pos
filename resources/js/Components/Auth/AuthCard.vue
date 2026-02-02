<template>
  <div class="mx-auto w-full sm:max-w-lg">
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-lg shadow-2xl overflow-hidden border border-white/20 dark:border-slate-700/50">
      <!-- Card header -->
      <div class="bg-gradient-to-r from-brand-600 to-brand-600 dark:from-brand-500 dark:to-brand-500 px-6 py-4 sm:px-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ title }}</h2>
        <p v-if="subtitle" class="text-brand-50 text-sm mt-2">{{ subtitle }}</p>
      </div>

      <!-- Card content -->
      <div class="px-6 py-8 sm:px-8">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts" generic="T extends Record<string, any>">
defineProps<{
  title: string;
  subtitle?: string;
}>();
</script>

<style scoped>
/* Card animation */
</style>

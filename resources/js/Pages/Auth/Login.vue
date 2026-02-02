<template>
  <GuestLayout>
    <div class="max-w-md mx-auto">
      <!-- Logo -->
      <div class="text-center mb-8">
        <Link :href="route('welcome')" class="inline-block group">
          <img 
            src="/logo.png" 
            alt="XASH POS Logo" 
            class="h-16 w-auto object-contain mx-auto dark:hidden group-hover:scale-105 transition-transform"
          />
          <img 
            src="/logo-white.png" 
            alt="XASH POS Logo" 
            class="h-16 w-auto object-contain mx-auto hidden dark:block group-hover:scale-105 transition-transform"
          />
        </Link>
        <p class="text-gray-600 dark:text-gray-400 mt-4">Sign in to your account</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl p-8 shadow-xl">
        <form @submit.prevent="submitLogin" class="space-y-5">
          <!-- Email field -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
              <input
                v-model="form.email"
                type="email"
                class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                placeholder="you@example.com"
                required
              />
            </div>
            <p v-if="form.errors.email" class="text-danger-500 text-sm mt-1">{{ form.errors.email }}</p>
          </div>

          <!-- Password field -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
            <div class="relative">
              <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
              <input
                v-model="form.password"
                type="password"
                class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                placeholder="••••••••"
                required
              />
            </div>
            <p v-if="form.errors.password" class="text-danger-500 text-sm mt-1">{{ form.errors.password }}</p>
          </div>

          <!-- Remember me -->
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
              <input v-model="form.remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-brand-500 focus:ring-brand-500 focus:ring-offset-0" />
              <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
            </label>
            <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
              Forgot password?
            </Link>
          </div>

          <!-- Submit button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3 bg-gradient-to-r from-brand-500 to-brand-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-brand-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
            <span>{{ form.processing ? 'Signing in...' : 'Sign In' }}</span>
            <i v-if="!form.processing" class="fas fa-arrow-right"></i>
          </button>
        </form>
      </div>

      <!-- Back to home -->
      <div class="text-center mt-6">
        <Link :href="route('welcome')" class="text-gray-600 hover:text-gray-900 transition-colors text-sm">
          <i class="fas fa-arrow-left mr-2"></i>
          Back to home
        </Link>
      </div>
    </div>
  </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submitLogin = () => {
  form.post(route('login.store'), {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  });
};
</script>

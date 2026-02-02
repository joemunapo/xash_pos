<template>
  <div class="min-h-screen flex flex-col bg-page overflow-hidden">
    <!-- Animated background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob bg-primary-light"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000 bg-primary-light"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-4 py-8">
      <div class="w-full max-w-sm mx-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
          <router-link to="/" class="inline-block">
            <div class="logo-container mx-auto mb-4">
              <img src="/logo.png" alt="XASH POS" class="w-20 h-20 object-contain" />
            </div>
          </router-link>
          <h1 class="text-xl font-bold text-gray-800 mb-1">Welcome Back</h1>
          <p class="text-gray-500 text-sm">Sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-lg">
          <form @submit.prevent="handleSubmit" class="space-y-5">
            <!-- Phone Number field -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
              <div class="relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-primary-light rounded-lg flex items-center justify-center">
                  <i class="fas fa-phone text-primary text-sm"></i>
                </div>
                <input
                  v-model="phone_number"
                  type="tel"
                  @input="errors.phone_number = ''; authStore.phoneError = null"
                  class="input-field w-full pl-14 pr-4 py-3 bg-gray-50 border rounded-lg text-gray-900 placeholder-gray-400 transition-all"
                  :class="[
                    errors.phone_number ? 'border-danger border-2 animate-shake' : 'border-gray-200'
                  ]"
                  placeholder="0771234567"
                  required
                />
              </div>
              <p v-if="errors.phone_number" class="text-danger text-xs mt-1.5 flex items-center gap-1">
                <i class="fas fa-exclamation-circle"></i>
                {{ errors.phone_number }}
              </p>
            </div>

            <!-- PIN field -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PIN</label>
              <div class="relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-primary-light rounded-lg flex items-center justify-center">
                  <i class="fas fa-lock text-primary text-sm"></i>
                </div>
                <input
                  v-model="pin"
                  :type="showPin ? 'text' : 'password'"
                  @input="errors.pin = ''; authStore.pinError = null"
                  inputmode="numeric"
                  maxlength="6"
                  class="input-field w-full pl-14 pr-12 py-3 bg-gray-50 border rounded-lg text-gray-900 placeholder-gray-400 transition-all"
                  :class="[
                    errors.pin ? 'border-danger border-2 animate-shake' : 'border-gray-200'
                  ]"
                  placeholder="••••••"
                  required
                />
                <button
                  type="button"
                  @click="showPin = !showPin"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                >
                  <i :class="showPin ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
              <p v-if="errors.pin" class="text-danger text-xs mt-1.5 flex items-center gap-1">
                <i class="fas fa-exclamation-circle"></i>
                {{ errors.pin }}
              </p>
            </div>

            <!-- Help text -->
            <div class="text-center py-2">
              <p class="text-sm text-gray-400">
                <i class="fas fa-info-circle mr-1"></i>
                Forgot PIN? Contact your manager.
              </p>
            </div>

            <!-- Submit button -->
            <button
              type="submit"
              :disabled="authStore.loading"
              class="submit-button w-full py-3.5 bg-primary text-white font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <i v-if="authStore.loading" class="fas fa-spinner fa-spin"></i>
              <span>{{ authStore.loading ? 'Signing in...' : 'Sign In' }}</span>
              <i v-if="!authStore.loading" class="fas fa-arrow-right"></i>
            </button>
          </form>
        </div>

        <!-- Back to home -->
        <div class="text-center mt-6">
          <router-link to="/" class="inline-flex items-center gap-2 text-gray-500 hover:text-primary transition-colors text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Back to home</span>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-20 py-4 border-t border-gray-200 bg-white">
      <div class="px-4">
        <div class="flex flex-col items-center gap-1">
          <div class="flex items-center gap-2">
            <span class="text-gray-800 font-semibold">XASH<span class="text-primary">POS</span></span>
          </div>
          <p class="text-gray-400 text-xs">© {{ new Date().getFullYear() }} XASHPOS. Cashier Terminal.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore, useNetworkStore } from '@/stores'

const authStore = useAuthStore()
const networkStore = useNetworkStore()

const phone_number = ref('')
const pin = ref('')
const showPin = ref(false)

const errors = reactive({
  phone_number: '',
  pin: ''
})

function validateForm() {
  let isValid = true

  // Reset errors
  errors.phone_number = ''
  errors.pin = ''

  // Phone validation
  if (!phone_number.value) {
    errors.phone_number = 'Phone number is required'
    isValid = false
  } else if (!/^\+?[0-9]{10,15}$/.test(phone_number.value)) {
    errors.phone_number = 'Enter a valid phone number (e.g. +263771234567)'
    isValid = false
  }

  // PIN validation
  if (!pin.value) {
    errors.pin = 'PIN is required'
    isValid = false
  } else if (!/^[0-9]{4,6}$/.test(pin.value)) {
    errors.pin = 'PIN must be 4-6 digits'
    isValid = false
  }

  return isValid
}

async function handleSubmit() {
  if (validateForm()) {
    if (networkStore.isOnline) {
      await authStore.login(phone_number.value, pin.value)
    } else {
      await authStore.loginOffline(phone_number.value, pin.value)
    }
    
    // Show server-side/offline errors if any
    if (authStore.phoneError) {
      errors.phone_number = authStore.phoneError
    }
    if (authStore.pinError) {
      errors.pin = authStore.pinError
    }
  }
}
</script>

<style scoped>
.animation-delay-2000 {
  animation-delay: 2s;
}

@keyframes blob {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
}

.animate-blob {
  animation: blob 8s infinite ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.animate-shake {
  animation: shake 0.2s ease-in-out 0s 2;
}

.logo-container {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 1rem;
  padding: 0.75rem;
  box-shadow:
    0 8px 30px -8px rgba(22, 105, 197, 0.12),
    0 0 0 1px rgba(22, 105, 197, 0.05);
  display: inline-block;
}

.input-field:focus {
  outline: none;
  border-color: #1669C5;
  box-shadow: 0 0 0 3px rgba(22, 105, 197, 0.1);
  background-color: #ffffff;
}

.input-field.border-danger:focus {
  border-color: #D32F2F;
  box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
}

.submit-button {
  background-color: #1669C5;
  box-shadow: 0 4px 14px -2px rgba(22, 105, 197, 0.4);
}

.submit-button:hover:not(:disabled) {
  background-color: #1254A3;
  box-shadow: 0 6px 20px -2px rgba(22, 105, 197, 0.5);
  transform: translateY(-1px);
}

.submit-button:active:not(:disabled) {
  transform: translateY(0);
}
</style>

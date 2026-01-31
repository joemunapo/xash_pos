<template>
  <div class="min-h-screen flex flex-col relative overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 bg-[var(--fluxr-dark)]">
      <!-- White animated orbs -->
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-20 animate-blob"></div>
      <div class="absolute top-1/3 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-15 animate-blob animation-delay-2000"></div>
      <div class="absolute -bottom-20 left-1/4 w-56 h-56 bg-white rounded-full filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
      <!-- Dark green transparent overlay -->
      <div class="absolute inset-0 bg-[var(--fluxr-dark)]/30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 flex-1 flex flex-col justify-between">
      <!-- Top Section -->
      <div class="p-4">
        <!-- Back Button -->
        <router-link
          to="/"
          class="inline-flex items-center text-white/70 hover:text-white mb-4 w-fit"
        >
          <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
          </svg>
          Back
        </router-link>

        <!-- Logo -->
        <div class="flex justify-center mb-3">
          <img
            src="@/assets/logo-white.png"
            alt="Fluxr Logo"
            class="h-20 w-20 object-contain drop-shadow-2xl"
          />
        </div>

        <!-- Header Text -->
        <div class="text-center">
          <h1 class="text-2xl font-bold text-white">Create Account</h1>
          <p class="text-sm text-white/60 mt-1">
            Fill in your details to get started
          </p>
        </div>
      </div>

      <!-- Bottom Section: Registration Form Card -->
      <div class="bg-white rounded-t-4xl p-6 pb-8 overflow-y-auto max-h-[70vh]">
        <form @submit.prevent="handleSubmit">
          <!-- First Name -->
          <div class="mb-3">
            <label for="first_name" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              First Name
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-[var(--fluxr-dark)]/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
              </div>
              <input
                id="first_name"
                v-model="first_name"
                type="text"
                placeholder="John"
                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
                :class="{ 'border-red-500': errors.first_name }"
              />
            </div>
            <p v-if="errors.first_name" class="mt-1 text-sm text-red-500">{{ errors.first_name }}</p>
          </div>

          <!-- Last Name -->
          <div class="mb-3">
            <label for="last_name" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              Last Name
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-[var(--fluxr-dark)]/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
              </div>
              <input
                id="last_name"
                v-model="last_name"
                type="text"
                placeholder="Doe"
                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
                :class="{ 'border-red-500': errors.last_name }"
              />
            </div>
            <p v-if="errors.last_name" class="mt-1 text-sm text-red-500">{{ errors.last_name }}</p>
          </div>

          <!-- Phone Number -->
          <div class="mb-3">
            <label for="phone" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              Phone Number
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-[var(--fluxr-dark)]/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                </svg>
              </div>
              <input
                id="phone"
                v-model="phone_number"
                type="tel"
                placeholder="+263771234567"
                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
                :class="{ 'border-red-500': errors.phone_number }"
              />
            </div>
            <p v-if="errors.phone_number" class="mt-1 text-sm text-red-500">{{ errors.phone_number }}</p>
          </div>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-[var(--fluxr-dark)]/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
              </div>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="john@example.com"
                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
                :class="{ 'border-red-500': errors.email }"
              />
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
          </div>

          <!-- Password (Hidden - Backend Auto-generates PIN) -->
          <input v-model.trim="password" type="hidden">
          <input v-model.trim="password_confirmation" type="hidden">

          <!-- Terms & Conditions -->
          <div class="mb-5">
            <label class="flex items-start gap-2 cursor-pointer">
              <input
                v-model="tcs_accepted"
                type="checkbox"
                class="mt-1 w-4 h-4 accent-[var(--fluxr-green-dark)]"
              />
              <span class="text-sm text-gray-600">
                I accept the <a href="/terms" class="text-[var(--fluxr-green-dark)] underline hover:no-underline">Terms & Conditions</a>
              </span>
            </label>
            <p v-if="errors.tcs_accepted" class="mt-1 text-sm text-red-500">{{ errors.tcs_accepted }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="authStore.loading"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg"
          >
            <svg v-if="authStore.loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ authStore.loading ? 'Creating Account...' : 'Create Account' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="my-4 border-t border-gray-200"></div>

        <!-- Sign In Link -->
        <div class="text-center">
          <span class="text-sm text-gray-600">
            Already have an account?
          </span>
          <router-link
              :to="{name:'login'}"
            class="text-sm text-[var(--fluxr-green-dark)] hover:text-[var(--fluxr-dark)] font-medium ml-1"
          >
            Sign In
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores'
import Swal from 'sweetalert2'

const router = useRouter()
const authStore = useAuthStore()

const first_name = ref('')
const last_name = ref('')
const phone_number = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const tcs_accepted = ref(false)

const errors = reactive({
  first_name: '',
  last_name: '',
  phone_number: '',
  email: '',
  password: '',
  password_confirmation: '',
  tcs_accepted: ''
})

function validateForm() {
  let isValid = true

  // Reset errors
  Object.keys(errors).forEach(key => errors[key] = '')

  // First name validation
  if (!first_name.value) {
    errors.first_name = 'First name is required'
    isValid = false
  } else if (first_name.value.length > 100) {
    errors.first_name = 'First name must be less than 100 characters'
    isValid = false
  }

  // Last name validation
  if (!last_name.value) {
    errors.last_name = 'Last name is required'
    isValid = false
  } else if (last_name.value.length > 100) {
    errors.last_name = 'Last name must be less than 100 characters'
    isValid = false
  }

  // Phone validation
  if (!phone_number.value) {
    errors.phone_number = 'Phone number is required'
    isValid = false
  } else if (!/^\+[0-9]{10,15}$/.test(phone_number.value)) {
    errors.phone_number = 'Enter a valid phone number (e.g. +263771234567)'
    isValid = false
  }

  // Email validation (REQUIRED now)
  if (!email.value) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!/.+@.+\..+/.test(email.value)) {
    errors.email = 'Enter a valid email'
    isValid = false
  }

  // Password validation
  if (!password.value) {
    errors.password = 'Password is required'
    isValid = false
  } else if (password.value.length < 6) {
    errors.password = 'Password must be at least 6 characters'
    isValid = false
  }

  // Confirm password validation
  if (!password_confirmation.value) {
    errors.password_confirmation = 'Please confirm your password'
    isValid = false
  } else if (password_confirmation.value !== password.value) {
    errors.password_confirmation = 'Passwords do not match'
    isValid = false
  }

  // Terms & Conditions validation
  if (!tcs_accepted.value) {
    errors.tcs_accepted = 'You must accept the terms and conditions'
    isValid = false
  }

  // Check for server-side errors
  if (authStore.registerErrors.first_name) {
    errors.first_name = authStore.registerErrors.first_name
    isValid = false
  }
  if (authStore.registerErrors.last_name) {
    errors.last_name = authStore.registerErrors.last_name
    isValid = false
  }
  if (authStore.registerErrors.phone_number) {
    errors.phone_number = authStore.registerErrors.phone_number
    isValid = false
  }
  if (authStore.registerErrors.email) {
    errors.email = authStore.registerErrors.email
    isValid = false
  }
  if (authStore.registerErrors.password) {
    errors.password = authStore.registerErrors.password
    isValid = false
  }
  if (authStore.registerErrors.tcs_accepted) {
    errors.tcs_accepted = authStore.registerErrors.tcs_accepted
    isValid = false
  }

  return isValid
}

async function handleSubmit() {
  // Set dummy password values (backend auto-generates PIN)
  password.value = Math.random().toString(36).substring(7)
  password_confirmation.value = password.value

  if (validateForm()) {
    const result = await authStore.register(
      first_name.value,
      last_name.value,
      phone_number.value,
      email.value,
      password.value,
      password_confirmation.value
    )

    if (result.success) {
      await Swal.fire({
        icon: 'success',
        title: 'Registration Successful!',
        html: `
          <p class="mb-4">Your account has been created successfully.</p>
          <div class="bg-gray-100 p-4 rounded-lg mb-4">
            <p class="text-sm text-gray-600 mb-2">Your 5-digit PIN is:</p>
            <p class="text-3xl font-bold text-[var(--fluxr-dark)]">${result.pin}</p>
          </div>
          <p class="text-sm text-red-600">
            <strong>Important:</strong> Please save this PIN securely.
            You will need it to sign in.
          </p>
        `,
        confirmButtonText: 'I have saved my PIN',
        confirmButtonColor: '#0d372b',
        allowOutsideClick: false,
        allowEscapeKey: false
      })
      router.push('/auth/login')
    } else {
      // Re-validate to show server errors
      validateForm()
    }
  }
}
</script>

<style scoped>
h1, h2, h3, h4, h5, h6, p {
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Blob Animation */
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}
</style>

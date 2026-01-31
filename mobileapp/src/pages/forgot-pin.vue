<template>
  <div class="min-h-screen flex flex-col relative overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 bg-[var(--fluxr-dark)]">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-20 animate-blob"></div>
      <div class="absolute top-1/3 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-15 animate-blob animation-delay-2000"></div>
      <div class="absolute -bottom-20 left-1/4 w-56 h-56 bg-white rounded-full filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
      <div class="absolute inset-0 bg-[var(--fluxr-dark)]/30"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 flex-1 flex flex-col justify-between">
      <!-- Top Section -->
      <div class="p-4">
        <!-- Back Button -->
        <router-link
          to="/auth/login"
          class="inline-flex items-center text-white/70 hover:text-white mb-8 w-fit"
        >
          <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
          </svg>
          Back to Login
        </router-link>

        <!-- Logo -->
        <div class="flex justify-center mb-6">
          <img
            src="@/assets/logo-white.png"
            alt="Fluxr Logo"
            class="h-24 w-24 object-contain drop-shadow-2xl"
          />
        </div>

        <!-- Header Text -->
        <div class="text-center">
          <h1 class="text-2xl font-bold text-white">Reset PIN</h1>
          <p class="text-sm text-white/60 mt-1">
            {{ step === 1 ? 'Enter your phone number to receive OTP' : 'Enter OTP and your new PIN' }}
          </p>
        </div>
      </div>

      <!-- Bottom Section: PIN Reset Card -->
      <div class="bg-white rounded-t-4xl p-6 pb-8">
        <!-- Step 1: Phone Number -->
        <form v-if="step === 1" @submit.prevent="handleRequestOtp">
          <div class="mb-4">
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

          <button
            type="submit"
            :disabled="authStore.loading"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg"
          >
            <svg v-if="authStore.loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ authStore.loading ? 'Sending OTP...' : 'Send OTP' }}
          </button>
        </form>

        <!-- Step 2: OTP + New PIN -->
        <form v-if="step === 2" @submit.prevent="handleResetPin">
          <div class="mb-3">
            <label for="otp" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              OTP Code
            </label>
            <input
              id="otp"
              v-model="otp"
              type="tel"
              inputmode="numeric"
              maxlength="6"
              placeholder="Enter 6-digit OTP"
              class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
              :class="{ 'border-red-500': errors.otp }"
            />
            <p v-if="errors.otp" class="mt-1 text-sm text-red-500">{{ errors.otp }}</p>
          </div>

          <div class="mb-3">
            <label for="new_pin" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              New PIN
            </label>
            <input
              id="new_pin"
              v-model="new_pin"
              type="tel"
              inputmode="numeric"
              maxlength="5"
              placeholder="•••••"
              class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
              :class="{ 'border-red-500': errors.new_pin }"
            />
            <p v-if="errors.new_pin" class="mt-1 text-sm text-red-500">{{ errors.new_pin }}</p>
          </div>

          <div class="mb-5">
            <label for="pin_confirmation" class="block text-sm font-medium text-[var(--fluxr-dark)] mb-1">
              Confirm New PIN
            </label>
            <input
              id="pin_confirmation"
              v-model="new_pin_confirmation"
              type="tel"
              inputmode="numeric"
              maxlength="5"
              placeholder="•••••"
              class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--fluxr-green-dark)] focus:border-[var(--fluxr-green-dark)] outline-none transition-all text-[var(--fluxr-dark)] placeholder-gray-400"
              :class="{ 'border-red-500': errors.pin_confirmation }"
            />
            <p v-if="errors.pin_confirmation" class="mt-1 text-sm text-red-500">{{ errors.pin_confirmation }}</p>
          </div>

          <button
            type="submit"
            :disabled="authStore.loading"
            class="w-full bg-[var(--fluxr-dark)] hover:bg-[var(--fluxr-dark-hover)] disabled:opacity-50 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center shadow-lg"
          >
            <svg v-if="authStore.loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ authStore.loading ? 'Resetting PIN...' : 'Reset PIN' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores'

const authStore = useAuthStore()

const step = ref(1)
const phone_number = ref('')
const otp = ref('')
const new_pin = ref('')
const new_pin_confirmation = ref('')

const errors = reactive({
  phone_number: '',
  otp: '',
  new_pin: '',
  pin_confirmation: ''
})

async function handleRequestOtp() {
  errors.phone_number = ''

  if (!phone_number.value) {
    errors.phone_number = 'Phone number is required'
    return
  }

  if (!/^\+[0-9]{10,15}$/.test(phone_number.value)) {
    errors.phone_number = 'Enter a valid phone number (e.g. +263771234567)'
    return
  }

  const result = await authStore.forgotPin(phone_number.value)
  if (result.success) {
    step.value = 2
  }
}

async function handleResetPin() {
  // Reset errors
  errors.otp = ''
  errors.new_pin = ''
  errors.pin_confirmation = ''

  let isValid = true

  if (!otp.value || !/^[0-9]{6}$/.test(otp.value)) {
    errors.otp = 'OTP must be 6 digits'
    isValid = false
  }

  if (!new_pin.value || !/^[0-9]{5}$/.test(new_pin.value)) {
    errors.new_pin = 'PIN must be exactly 5 digits'
    isValid = false
  }

  if (new_pin_confirmation.value !== new_pin.value) {
    errors.pin_confirmation = 'PINs do not match'
    isValid = false
  }

  if (!isValid) return

  await authStore.resetPin(
    phone_number.value,
    otp.value,
    new_pin.value,
    new_pin_confirmation.value
  )
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

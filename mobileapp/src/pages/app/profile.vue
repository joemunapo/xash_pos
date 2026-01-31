<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6 pb-8">
        <div class="flex items-center gap-4 mb-6">
          <button @click="goBack" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fa-solid fa-arrow-left text-white text-lg"></i>
          </button>
          <h1 class="text-white text-xl font-semibold">Profile</h1>
        </div>

        <!-- Profile Avatar -->
        <div class="text-center">
          <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-user text-white text-3xl"></i>
          </div>
          <h2 class="text-white text-xl font-semibold">{{ user?.first_name }} {{ user?.last_name }}</h2>
          <p class="text-white/60 text-sm">{{ user?.phone_number }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 -mt-4 relative z-20">
      <!-- Account Information -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4 p-4">
        <h3 class="text-[var(--fluxr-dark)] font-semibold mb-4">Account Information</h3>
        <div class="space-y-3">
          <div>
            <p class="text-sm text-gray-600">Email</p>
            <p class="font-medium text-[var(--fluxr-dark)]">{{ user?.email }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Phone Number</p>
            <p class="font-medium text-[var(--fluxr-dark)]">{{ user?.phone_number }}</p>
          </div>
        </div>
      </div>

      <!-- Security Section -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
        <button
          @click="showChangePinModal = true"
          class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors border-b border-gray-100"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-key text-green-600"></i>
            </div>
            <span class="text-[var(--fluxr-dark)] font-medium">Change PIN</span>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-400"></i>
        </button>

        <button
          @click="showChangePhoneModal = true"
          class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-phone text-blue-600"></i>
            </div>
            <span class="text-[var(--fluxr-dark)] font-medium">Change Phone Number</span>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-400"></i>
        </button>
      </div>

      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <button
          @click="logout"
          class="w-full flex items-center justify-between p-4 hover:bg-red-50 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
              <i class="fa-solid fa-right-from-bracket text-red-600"></i>
            </div>
            <span class="text-red-600 font-medium">Logout</span>
          </div>
        </button>
      </div>

      <!-- Change PIN Modal -->
      <div v-if="showChangePinModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
          <h3 class="text-xl font-bold text-[var(--fluxr-dark)] mb-4">Change PIN</h3>

          <div class="space-y-4 mb-6">
            <div>
              <label class="text-sm font-medium text-gray-700 block mb-2">Current PIN</label>
              <input
                v-model="pinChange.current_pin"
                type="tel"
                inputmode="numeric"
                maxlength="5"
                placeholder="•••••"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              />
              <p v-if="pinChangeErrors.current_pin" class="text-sm text-red-600 mt-1">{{ pinChangeErrors.current_pin }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700 block mb-2">New PIN</label>
              <input
                v-model="pinChange.new_pin"
                type="tel"
                inputmode="numeric"
                maxlength="5"
                placeholder="•••••"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              />
              <p v-if="pinChangeErrors.new_pin" class="text-sm text-red-600 mt-1">{{ pinChangeErrors.new_pin }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700 block mb-2">Confirm New PIN</label>
              <input
                v-model="pinChange.new_pin_confirmation"
                type="tel"
                inputmode="numeric"
                maxlength="5"
                placeholder="•••••"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              />
              <p v-if="pinChangeErrors.pin_confirmation" class="text-sm text-red-600 mt-1">{{ pinChangeErrors.pin_confirmation }}</p>
            </div>
          </div>

          <div class="flex gap-3">
            <button
              @click="showChangePinModal = false"
              class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="handleChangePin"
              :disabled="changingPin"
              class="flex-1 px-4 py-2.5 bg-[var(--fluxr-green)] text-[var(--fluxr-dark)] rounded-lg font-medium hover:bg-[var(--fluxr-green-dark)] transition-colors disabled:opacity-50"
            >
              {{ changingPin ? 'Changing...' : 'Change PIN' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Change Phone Modal -->
      <div v-if="showChangePhoneModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
          <h3 class="text-xl font-bold text-[var(--fluxr-dark)] mb-4">Change Phone Number</h3>

          <div v-if="phoneChangeStep === 1" class="space-y-4">
            <div>
              <label class="text-sm font-medium text-gray-700 block mb-2">New Phone Number</label>
              <input
                v-model="phoneChange.new_phone"
                type="tel"
                placeholder="+263771234567"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              />
              <p v-if="phoneChangeErrors.new_phone" class="text-sm text-red-600 mt-1">{{ phoneChangeErrors.new_phone }}</p>
            </div>

            <div class="flex gap-3 mt-6">
              <button
                @click="showChangePhoneModal = false; phoneChangeStep = 1"
                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="handleRequestPhoneChange"
                :disabled="changingPhone"
                class="flex-1 px-4 py-2.5 bg-[var(--fluxr-green)] text-[var(--fluxr-dark)] rounded-lg font-medium hover:bg-[var(--fluxr-green-dark)] transition-colors disabled:opacity-50"
              >
                {{ changingPhone ? 'Sending...' : 'Send OTP' }}
              </button>
            </div>
          </div>

          <div v-if="phoneChangeStep === 2" class="space-y-4">
            <p class="text-sm text-gray-600">Enter the OTP sent to {{ phoneChange.new_phone }}</p>

            <div>
              <label class="text-sm font-medium text-gray-700 block mb-2">OTP Code</label>
              <input
                v-model="phoneChange.otp"
                type="tel"
                inputmode="numeric"
                maxlength="6"
                placeholder="Enter 6-digit OTP"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              />
              <p v-if="phoneChangeErrors.otp" class="text-sm text-red-600 mt-1">{{ phoneChangeErrors.otp }}</p>
            </div>

            <div class="flex gap-3 mt-6">
              <button
                @click="phoneChangeStep = 1"
                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
              >
                Back
              </button>
              <button
                @click="handleConfirmPhoneChange"
                :disabled="changingPhone"
                class="flex-1 px-4 py-2.5 bg-[var(--fluxr-green)] text-[var(--fluxr-dark)] rounded-lg font-medium hover:bg-[var(--fluxr-green-dark)] transition-colors disabled:opacity-50"
              >
                {{ changingPhone ? 'Confirming...' : 'Confirm' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore, useAlertStore } from '@/stores'

const router = useRouter()
const authStore = useAuthStore()
const alertStore = useAlertStore()

const user = computed(() => authStore.user)

// PIN Change Modal
const showChangePinModal = ref(false)
const changingPin = ref(false)
const pinChange = reactive({
  current_pin: '',
  new_pin: '',
  new_pin_confirmation: ''
})
const pinChangeErrors = reactive({
  current_pin: '',
  new_pin: '',
  pin_confirmation: ''
})

// Phone Change Modal
const showChangePhoneModal = ref(false)
const changingPhone = ref(false)
const phoneChangeStep = ref(1)
const phoneChange = reactive({
  new_phone: '',
  otp: ''
})
const phoneChangeErrors = reactive({
  new_phone: '',
  otp: ''
})

const goBack = () => {
  router.back()
}

const navigateTo = (path) => {
  router.push(path)
}

const logout = async () => {
  await authStore.logout()
}

const handleChangePin = async () => {
  // Reset errors
  pinChangeErrors.current_pin = ''
  pinChangeErrors.new_pin = ''
  pinChangeErrors.pin_confirmation = ''

  let isValid = true

  // Validate current PIN
  if (!pinChange.current_pin || !/^[0-9]{5}$/.test(pinChange.current_pin)) {
    pinChangeErrors.current_pin = 'Current PIN must be exactly 5 digits'
    isValid = false
  }

  // Validate new PIN
  if (!pinChange.new_pin || !/^[0-9]{5}$/.test(pinChange.new_pin)) {
    pinChangeErrors.new_pin = 'New PIN must be exactly 5 digits'
    isValid = false
  }

  // Validate confirmation matches
  if (pinChange.new_pin !== pinChange.new_pin_confirmation) {
    pinChangeErrors.pin_confirmation = 'PINs do not match'
    isValid = false
  }

  if (!isValid) return

  changingPin.value = true

  try {
    const result = await authStore.changePin(
      pinChange.current_pin,
      pinChange.new_pin,
      pinChange.new_pin_confirmation
    )

    if (result.success) {
      // Close modal and reset form
      showChangePinModal.value = false
      pinChange.current_pin = ''
      pinChange.new_pin = ''
      pinChange.new_pin_confirmation = ''
    } else {
      // Handle server errors
      if (result.error?.errors?.current_pin) {
        pinChangeErrors.current_pin = result.error.errors.current_pin
      }
      if (result.error?.errors?.new_pin) {
        pinChangeErrors.new_pin = result.error.errors.new_pin
      }
    }
  } finally {
    changingPin.value = false
  }
}

const handleRequestPhoneChange = async () => {
  // Reset errors
  phoneChangeErrors.new_phone = ''

  if (!phoneChange.new_phone || !/^\+[0-9]{10,15}$/.test(phoneChange.new_phone)) {
    phoneChangeErrors.new_phone = 'Enter a valid phone number (e.g., +263771234567)'
    return
  }

  changingPhone.value = true

  try {
    const result = await authStore.requestPhoneChange(phoneChange.new_phone)

    if (result.success) {
      // Move to OTP entry step
      phoneChangeStep.value = 2
    } else {
      // Handle server errors
      if (result.error?.errors?.new_phone_number) {
        phoneChangeErrors.new_phone = result.error.errors.new_phone_number
      } else {
        phoneChangeErrors.new_phone = result.error?.message || 'Failed to send OTP'
      }
    }
  } finally {
    changingPhone.value = false
  }
}

const handleConfirmPhoneChange = async () => {
  // Reset errors
  phoneChangeErrors.otp = ''

  if (!phoneChange.otp || !/^[0-9]{6}$/.test(phoneChange.otp)) {
    phoneChangeErrors.otp = 'OTP must be exactly 6 digits'
    return
  }

  changingPhone.value = true

  try {
    const result = await authStore.confirmPhoneChange(
      phoneChange.new_phone,
      phoneChange.otp
    )

    if (result.success) {
      // Close modal and reset form
      showChangePhoneModal.value = false
      phoneChangeStep.value = 1
      phoneChange.new_phone = ''
      phoneChange.otp = ''
    } else {
      // Handle server errors
      if (result.error?.errors?.otp) {
        phoneChangeErrors.otp = result.error.errors.otp
      } else {
        phoneChangeErrors.otp = result.error?.message || 'Failed to confirm phone change'
      }
    }
  } finally {
    changingPhone.value = false
  }
}
</script>

<style scoped>
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
</style>

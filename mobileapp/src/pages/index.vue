<template>
  <div class="min-h-screen flex flex-col bg-page overflow-hidden">
    <!-- Animated background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob bg-primary-light"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000 bg-primary-light"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000 bg-accent-light"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-6 py-12">
      <div class="w-full max-w-sm mx-auto">
        <div class="text-center">
          <!-- Logo -->
          <div class="flex justify-center mb-6">
            <div class="logo-container">
              <img src="/logo.png" alt="XASH POS" class="w-32 h-32 object-contain" />
            </div>
          </div>

          <!-- Tagline -->
          <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Point of Sale Terminal
          </h1>

          <p class="text-gray-500 mb-10 text-sm leading-relaxed px-4">
            Fast, reliable cashier terminal for retail operations. Scan products, process payments, and print receipts.
          </p>

          <!-- CTA Button -->
          <router-link
            :to="{ name: 'login' }"
            class="cta-button inline-flex items-center justify-center w-full px-8 py-4 text-lg font-semibold text-white bg-primary rounded-lg transition-all shadow-lg"
          >
            <i class="fas fa-sign-in-alt mr-3"></i>
            Sign In to POS
          </router-link>

          <p class="text-gray-400 text-sm mt-8">
            Contact your manager for login credentials
          </p>
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
</script>

<style scoped>
.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
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

.logo-container {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 1.5rem;
  padding: 1rem;
  box-shadow:
    0 10px 40px -10px rgba(22, 105, 197, 0.15),
    0 0 0 1px rgba(22, 105, 197, 0.05);
}

.cta-button {
  background-color: #1669C5;
  box-shadow: 0 10px 30px -5px rgba(22, 105, 197, 0.4);
}

.cta-button:hover {
  background-color: #1254A3;
  box-shadow: 0 15px 40px -5px rgba(22, 105, 197, 0.5);
  transform: translateY(-2px);
}

.cta-button:active {
  transform: translateY(0);
}
</style>

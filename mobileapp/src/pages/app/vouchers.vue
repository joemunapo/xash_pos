<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header -->
    <div class="bg-[var(--fluxr-dark)] relative overflow-hidden">
      <div class="absolute -top-20 -left-20 w-64 h-64 bg-white rounded-full filter blur-3xl opacity-10 animate-blob"></div>
      <div class="absolute top-10 -right-20 w-48 h-48 bg-white rounded-full filter blur-3xl opacity-5 animate-blob animation-delay-2000"></div>

      <div class="relative z-10 p-6">
        <div class="flex items-center gap-4 mb-4">
          <button @click="goBack" class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fa-solid fa-arrow-left text-white text-lg"></i>
          </button>
          <div>
            <h1 class="text-white text-xl font-semibold">Vouchers</h1>
            <p class="text-white/60 text-sm">Buy or redeem vouchers</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="p-6 -mt-4 relative z-20">
      <div class="bg-white rounded-lg p-1 shadow-sm mb-6 grid grid-cols-3">
        <button
          @click="activeTab = 'list'"
          :class="[
            'py-2.5 px-2 rounded-md font-medium transition-all text-sm',
            activeTab === 'list'
              ? 'bg-[var(--fluxr-green)] text-[var(--fluxr-dark)]'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          <i class="fa-solid fa-list mr-1"></i>Vouchers
        </button>
        <button
          @click="activeTab = 'buy'"
          :class="[
            'py-2.5 px-2 rounded-md font-medium transition-all text-sm',
            activeTab === 'buy'
              ? 'bg-[var(--fluxr-green)] text-[var(--fluxr-dark)]'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          <i class="fa-solid fa-shopping-cart mr-1"></i>Buy
        </button>
        <button
          @click="activeTab = 'redeem'"
          :class="[
            'py-2.5 px-2 rounded-md font-medium transition-all text-sm',
            activeTab === 'redeem'
              ? 'bg-[var(--fluxr-green)] text-[var(--fluxr-dark)]'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          <i class="fa-solid fa-ticket mr-1"></i>Redeem
        </button>
      </div>

      <!-- My Vouchers Tab -->
      <div v-if="activeTab === 'list'" class="bg-white rounded-2xl p-6 shadow-sm">
        <div v-if="dataLoading" class="text-center py-12">
          <i class="fa-solid fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
          <p class="text-gray-500">Loading your vouchers...</p>
        </div>

        <div v-else-if="myVouchers.length === 0" class="text-center py-12">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-ticket text-gray-400 text-3xl"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-700 mb-2">No Vouchers Yet</h3>
          <p class="text-gray-500 text-sm mb-4">You haven't purchased any vouchers</p>
          <button
            @click="activeTab = 'buy'"
            class="px-6 py-2 bg-[var(--fluxr-green)] text-[var(--fluxr-dark)] rounded-lg font-medium hover:bg-[var(--fluxr-green-dark)] transition-colors"
          >
            Buy Your First Voucher
          </button>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="voucher in myVouchers"
            :key="voucher.id"
            class="border border-gray-200 rounded-lg p-4 hover:border-[var(--fluxr-green)] transition-colors"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                  <h4 class="font-semibold text-gray-900">{{ voucher.product_name || voucher.provider?.name || 'Voucher' }}</h4>
                  <span
                    :class="[
                      'px-2 py-0.5 text-xs font-medium rounded',
                      voucher.status === 'active' ? 'bg-green-100 text-green-800' :
                      voucher.status === 'redeemed' ? 'bg-gray-100 text-gray-800' :
                      'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ voucher.status }}
                  </span>
                  <span
                    v-if="isRedeemableVoucher(voucher)"
                    class="px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-800"
                    title="This voucher can be redeemed for wallet balance"
                  >
                    <i class="fa-solid fa-wallet mr-1"></i>Redeemable
                  </span>
                  <span
                    v-else
                    class="px-2 py-0.5 text-xs font-medium rounded bg-purple-100 text-purple-800"
                    title="Use this voucher at partner sites"
                  >
                    <i class="fa-solid fa-external-link-alt mr-1"></i>External
                  </span>
                </div>
                <p class="text-sm text-gray-500">
                  {{ new Date(voucher.created_at).toLocaleDateString('en-ZA', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-[var(--fluxr-dark)]">R{{ formatAmount(voucher.value) }}</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-3 mb-3">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-600">Voucher Code:</span>
                <button
                  @click="copyToClipboard(voucher.voucher_number)"
                  class="text-[var(--fluxr-green-dark)] hover:text-[var(--fluxr-green)] text-xs font-medium"
                >
                  <i class="fa-solid fa-copy mr-1"></i>Copy
                </button>
              </div>
              <p class="font-mono text-sm font-semibold text-gray-900 mt-1 break-all">
                {{ voucher.voucher_number }}
              </p>
            </div>

            <div class="flex items-center justify-between">
              <div v-if="voucher.redeemed_at" class="text-xs text-gray-500">
                Redeemed: {{ new Date(voucher.redeemed_at).toLocaleDateString('en-ZA') }}
              </div>
              <button
                v-if="voucher.receipt"
                @click="viewReceipt(voucher)"
                class="px-3 py-1.5 text-xs font-medium text-[var(--fluxr-green-dark)] hover:text-white hover:bg-[var(--fluxr-green-dark)] border border-[var(--fluxr-green-dark)] rounded-lg transition-colors"
              >
                <i class="fa-solid fa-receipt mr-1"></i>View Receipt
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Buy Voucher Tab -->
      <div v-if="activeTab === 'buy'" class="bg-white rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="handleBuyVoucher" class="space-y-6">
          <!-- Provider Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Select Provider
            </label>
            <select
              v-model="buyForm.providerId"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
              :disabled="buyLoading || dataLoading"
              required
            >
              <option value="">
                {{ dataLoading ? 'Loading providers...' : 'Choose a provider...' }}
              </option>

              <!-- Live Kazang Products -->
              <option
                v-for="provider in kazangProviders"
                :key="provider.id"
                :value="provider.id"
              >
                {{ provider.name }}
              </option>
            </select>
            <p v-if="dataLoading" class="mt-1 text-xs text-gray-500">
              <i class="fa-solid fa-spinner fa-spin mr-1"></i>Loading live products from Kazang...
            </p>
            <p v-if="!dataLoading && kazangProviders.length === 0" class="mt-1 text-xs text-red-600">
              No products available. Please try again later.
            </p>
            <div v-if="selectedProvider" class="mt-2">
              <p v-if="selectedProvider.description" class="text-xs text-gray-500">
                {{ selectedProvider.description }}
              </p>
              <p v-if="selectedProvider.min_amount || selectedProvider.max_amount" class="text-xs text-gray-500 mt-1">
                <span v-if="selectedProvider.min_amount">Min: R{{ selectedProvider.min_amount }}</span>
                <span v-if="selectedProvider.min_amount && selectedProvider.max_amount"> • </span>
                <span v-if="selectedProvider.max_amount">Max: R{{ selectedProvider.max_amount }}</span>
              </p>
            </div>
          </div>

          <!-- Amount -->
          <div>
            <label for="buyAmount" class="block text-sm font-medium text-gray-700 mb-2">
              Amount (ZAR)
            </label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">R</span>
              <input
                id="buyAmount"
                v-model.number="buyForm.amount"
                type="number"
                step="0.01"
                min="1"
                placeholder="0.00"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent"
                :disabled="buyLoading"
                required
              />
            </div>
            <p class="mt-1 text-xs text-gray-500">Minimum amount: R1.00</p>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-info-circle text-blue-600 text-lg mt-0.5"></i>
              <div>
                <p class="text-blue-800 font-medium text-sm mb-1">How it works</p>
                <ul class="text-blue-700 text-xs space-y-1">
                  <li>• Amount will be deducted from your wallet</li>
                  <li>• You'll receive a voucher code instantly</li>
                  <li>• Share or redeem the voucher anytime</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="buyError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 text-sm">{{ buyError }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="buyLoading || !buyForm.providerId || !buyForm.amount"
            class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="buyLoading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            <i v-else class="fa-solid fa-shopping-cart mr-2"></i>
            {{ buyLoading ? 'Processing...' : `Buy Voucher - R${buyForm.amount || '0.00'}` }}
          </button>
        </form>

        <!-- Success Display -->
        <div v-if="createdVoucher" class="mt-6 p-6 bg-green-50 border-2 border-green-200 rounded-lg">
          <div class="text-center mb-4">
            <i class="fa-solid fa-check-circle text-green-600 text-5xl mb-3"></i>
            <h3 class="text-lg font-semibold text-green-900 mb-1">Voucher Created!</h3>
            <p class="text-sm text-green-700">Share this code with the recipient</p>
          </div>

          <div class="bg-white rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm text-gray-600">Voucher Code:</span>
              <button
                @click="copyVoucherCode"
                class="text-[var(--fluxr-green-dark)] hover:text-[var(--fluxr-green)] text-sm font-medium"
              >
                <i class="fa-solid fa-copy mr-1"></i>{{ copiedVoucher ? 'Copied!' : 'Copy' }}
              </button>
            </div>
            <div class="font-mono text-xl font-bold text-[var(--fluxr-dark)] break-all">
              {{ createdVoucher.voucher_number }}
            </div>
          </div>

          <div class="space-y-2 text-sm">
            <div v-if="createdVoucher.product_name" class="flex justify-between">
              <span class="text-gray-600">Product:</span>
              <span class="font-semibold text-gray-900">{{ createdVoucher.product_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Value:</span>
              <span class="font-semibold text-gray-900">R{{ formatAmount(createdVoucher.value) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Status:</span>
              <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                {{ createdVoucher.status }}
              </span>
            </div>
          </div>

          <div class="mt-4 space-y-2">
            <button
              v-if="createdVoucher.receipt"
              @click="viewReceipt(createdVoucher)"
              class="w-full bg-[var(--fluxr-green-dark)] hover:bg-[var(--fluxr-dark)] text-white font-medium py-3 rounded-lg transition-colors"
            >
              <i class="fa-solid fa-receipt mr-2"></i>View Receipt
            </button>
            <button
              @click="resetBuyForm"
              class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 rounded-lg transition-colors"
            >
              Create Another Voucher
            </button>
          </div>
        </div>
      </div>

      <!-- Redeem Voucher Tab -->
      <div v-if="activeTab === 'redeem'" class="bg-white rounded-2xl p-6 shadow-sm">
        <form @submit.prevent="handleRedeemVoucher" class="space-y-6">
          <!-- Voucher Code Input -->
          <div>
            <label for="voucherCode" class="block text-sm font-medium text-gray-700 mb-2">
              Voucher Code
            </label>
            <input
              id="voucherCode"
              v-model="redeemForm.voucherCode"
              type="text"
              placeholder="Enter voucher code (e.g., VCH-ABCD-1234-EF56)"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--fluxr-green)] focus:border-transparent font-mono"
              :disabled="redeemLoading"
              required
            />
            <p class="mt-1 text-xs text-gray-500">Enter the full voucher code you received</p>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-info-circle text-blue-600 text-lg mt-0.5"></i>
              <div>
                <p class="text-blue-800 font-medium text-sm mb-1">Which vouchers can be redeemed here?</p>
                <ul class="text-blue-700 text-xs space-y-1">
                  <li>• <strong>Blue Vouchers</strong> - Can be converted to wallet balance</li>
                  <li>• <strong>Flash/1Voucher</strong> - Can be converted to wallet balance</li>
                  <li>• <strong>Other vouchers</strong> (Gaming, EasyPay, etc.) - Use at partner sites listed on receipt</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="redeemError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 text-sm">{{ redeemError }}</p>
          </div>

          <!-- Success Message -->
          <div v-if="redeemSuccess" class="p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-check-circle text-green-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-green-800 font-medium">{{ redeemSuccess }}</p>
                <p v-if="redeemedAmount" class="text-green-600 text-sm mt-1">
                  R{{ formatAmount(redeemedAmount) }} added to your wallet
                </p>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="redeemLoading || !redeemForm.voucherCode"
            class="w-full bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-semibold py-4 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <i v-if="redeemLoading" class="fa-solid fa-spinner fa-spin mr-2"></i>
            <i v-else class="fa-solid fa-ticket mr-2"></i>
            {{ redeemLoading ? 'Redeeming...' : 'Redeem Voucher' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Receipt Modal -->
    <div
      v-if="showReceiptModal"
      @click="closeReceiptModal"
      class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
    >
      <div
        @click.stop
        class="bg-white rounded-2xl max-w-lg w-full max-h-[80vh] overflow-y-auto shadow-2xl"
      >
        <!-- Modal Header -->
        <div class="sticky top-0 bg-[var(--fluxr-dark)] text-white p-4 rounded-t-2xl flex items-center justify-between">
          <h3 class="text-lg font-semibold">
            <i class="fa-solid fa-receipt mr-2"></i>Voucher Receipt
          </h3>
          <button
            @click="closeReceiptModal"
            class="p-2 hover:bg-white/10 rounded-full transition-colors"
          >
            <i class="fa-solid fa-times text-xl"></i>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <div v-if="selectedVoucher" class="space-y-4">
            <!-- Product Info -->
            <div class="border-b pb-4">
              <h4 class="font-semibold text-gray-900 mb-2">{{ selectedVoucher.product_name || 'Voucher' }}</h4>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Value:</span>
                <span class="font-semibold">R{{ formatAmount(selectedVoucher.value) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Status:</span>
                <span
                  :class="[
                    'px-2 py-0.5 text-xs font-medium rounded',
                    selectedVoucher.status === 'active' ? 'bg-green-100 text-green-800' :
                    selectedVoucher.status === 'redeemed' ? 'bg-gray-100 text-gray-800' :
                    'bg-red-100 text-red-800'
                  ]"
                >
                  {{ selectedVoucher.status }}
                </span>
              </div>
            </div>

            <!-- Receipt Content -->
            <div class="bg-gray-50 rounded-lg p-4">
              <pre class="text-xs font-mono whitespace-pre-wrap break-words text-gray-800">{{ selectedVoucher.receipt }}</pre>
            </div>

            <!-- Copy Receipt Button -->
            <button
              @click="copyReceipt"
              class="w-full py-3 bg-[var(--fluxr-green)] hover:bg-[var(--fluxr-green-dark)] text-[var(--fluxr-dark)] font-medium rounded-lg transition-colors"
            >
              <i class="fa-solid fa-copy mr-2"></i>{{ copiedReceipt ? 'Copied!' : 'Copy Receipt' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { voucherService } from '@/helpers/voucher'
import { useAlertStore } from '@/stores'

const router = useRouter()
const alertStore = useAlertStore()

const activeTab = ref('list')
const myVouchers = ref([])
const kazangProviders = ref([])
const dataLoading = ref(false)

// Buy Voucher Form
const buyForm = reactive({
  providerId: '',
  amount: null
})
const buyLoading = ref(false)
const buyError = ref('')
const createdVoucher = ref(null)
const copiedVoucher = ref(false)

// Redeem Voucher Form
const redeemForm = reactive({
  voucherCode: ''
})
const redeemLoading = ref(false)
const redeemError = ref('')
const redeemSuccess = ref('')
const redeemedAmount = ref(null)

// Receipt Modal
const showReceiptModal = ref(false)
const selectedVoucher = ref(null)
const copiedReceipt = ref(false)

const selectedProvider = computed(() => {
  return kazangProviders.value.find(p => p.id === buyForm.providerId)
})

const isRedeemableVoucher = (voucher) => {
  const providerCode = voucher.provider?.provider_code?.toUpperCase() || ''
  // Blue and Flash/1Voucher can be redeemed for wallet balance
  return ['BLU', 'BLUE', '1VOUCHER', 'FLASH'].includes(providerCode)
}

const goBack = () => {
  router.back()
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('en-ZA', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    alertStore.success('Voucher code copied to clipboard')
  } catch (error) {
    console.error('Failed to copy:', error)
  }
}

const fetchVouchersAndProducts = async () => {
  dataLoading.value = true
  try {
    console.log('🔄 Fetching vouchers and live Kazang products...')

    const response = await voucherService.listVouchers()

    // User's vouchers
    myVouchers.value = response.data || []
    console.log(`📋 Loaded ${myVouchers.value.length} user vouchers`)

    // Available Kazang products (already filtered by backend)
    kazangProviders.value = response.available_products || []
    console.log(`✨ Loaded ${kazangProviders.value.length} available voucher products`)

    // Log products for debugging
    if (kazangProviders.value.length > 0) {
      console.log('📦 Available Products:', kazangProviders.value)
      kazangProviders.value.forEach(product => {
        console.log(`🎟️ Product: ${product.id} - ${product.name} (${product.category})`, {
          min: product.min_amount,
          max: product.max_amount
        })
      })
    } else {
      console.warn('⚠️ No voucher products available')
    }
  } catch (error) {
    console.error('❌ Failed to fetch data:', error)
    alertStore.error('Failed to load vouchers and products')
  } finally {
    dataLoading.value = false
  }
}

const handleBuyVoucher = async () => {
  buyError.value = ''

  // Validate amount against provider limits
  if (selectedProvider.value) {
    const minAmount = selectedProvider.value.min_amount || 1
    const maxAmount = selectedProvider.value.max_amount || Infinity

    if (buyForm.amount < minAmount) {
      buyError.value = `Minimum amount for ${selectedProvider.value.name} is R${minAmount}`
      return
    }

    if (buyForm.amount > maxAmount) {
      buyError.value = `Maximum amount for ${selectedProvider.value.name} is R${maxAmount}`
      return
    }
  }

  buyLoading.value = true

  try {
    console.log('🛒 Creating voucher:', {
      provider: selectedProvider.value,
      amount: buyForm.amount,
      provider_code: selectedProvider.value.provider_code
    })

    // All products are from Kazang, so use provider_code
    const providerId = selectedProvider.value.provider_code
    const productName = selectedProvider.value.name

    const response = await voucherService.buyVoucher(
      providerId,
      buyForm.amount,
      productName
    )

    console.log('✅ Voucher created:', response)

    createdVoucher.value = response.data?.voucher || response.voucher
    const successMsg = createdVoucher.value?.product_name
      ? `${createdVoucher.value.product_name} created successfully!`
      : `Voucher created successfully! Value: R${formatAmount(buyForm.amount)}`
    alertStore.success(successMsg)

    // Refresh vouchers list
    await fetchVouchersAndProducts()

    // Don't reset form - show the voucher details
  } catch (error) {
    console.error('❌ Failed to create voucher:', error)
    buyError.value = error.message || 'Failed to create voucher. Please try again.'
    alertStore.error(error.message || 'Failed to create voucher')
  } finally {
    buyLoading.value = false
  }
}

const resetBuyForm = () => {
  createdVoucher.value = null
  buyForm.providerId = ''
  buyForm.amount = null
  buyError.value = ''
  copiedVoucher.value = false
}

const copyVoucherCode = async () => {
  if (!createdVoucher.value?.voucher_number) return

  try {
    await navigator.clipboard.writeText(createdVoucher.value.voucher_number)
    copiedVoucher.value = true
    alertStore.success('Voucher code copied to clipboard')

    setTimeout(() => {
      copiedVoucher.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to copy:', error)
  }
}

const handleRedeemVoucher = async () => {
  redeemError.value = ''
  redeemSuccess.value = ''
  redeemedAmount.value = null
  redeemLoading.value = true

  try {
    const response = await voucherService.redeemVoucher(redeemForm.voucherCode)

    redeemSuccess.value = response.message
    redeemedAmount.value = response.transaction?.amount || 0

    alertStore.success(`Voucher redeemed! R${formatAmount(redeemedAmount.value)} added to your wallet`)

    // Refresh voucher list
    await fetchVouchersAndProducts()

    // Reset form after success
    setTimeout(() => {
      redeemForm.voucherCode = ''
      redeemSuccess.value = ''
      redeemedAmount.value = null
    }, 5000)
  } catch (error) {
    console.error('Redemption error:', error)

    // Check if this is an external voucher (Kazang, gaming, etc.)
    if (error.response?.is_external_voucher) {
      redeemError.value = error.message

      // If receipt is available, show it in a modal
      if (error.response.receipt) {
        alertStore.error(error.message)

        // Create a temporary voucher object to show the receipt
        const tempVoucher = {
          voucher_number: error.response.voucher_code || redeemForm.voucherCode,
          product_name: 'External Voucher',
          receipt: error.response.receipt,
          status: 'active',
          value: 0
        }

        // Show receipt modal
        setTimeout(() => {
          viewReceipt(tempVoucher)
        }, 500)
      }
    } else {
      redeemError.value = error.message || 'Failed to redeem voucher. Please check the code and try again.'
      alertStore.error(error.message || 'Failed to redeem voucher')
    }
  } finally {
    redeemLoading.value = false
  }
}

const viewReceipt = (voucher) => {
  selectedVoucher.value = voucher
  showReceiptModal.value = true
  copiedReceipt.value = false
}

const closeReceiptModal = () => {
  showReceiptModal.value = false
  selectedVoucher.value = null
  copiedReceipt.value = false
}

const copyReceipt = async () => {
  if (!selectedVoucher.value?.receipt) return

  try {
    await navigator.clipboard.writeText(selectedVoucher.value.receipt)
    copiedReceipt.value = true
    alertStore.success('Receipt copied to clipboard')

    setTimeout(() => {
      copiedReceipt.value = false
    }, 3000)
  } catch (error) {
    console.error('Failed to copy receipt:', error)
    alertStore.error('Failed to copy receipt')
  }
}

onMounted(async () => {
  console.log('🚀 Vouchers page mounted - Loading data')
  await fetchVouchersAndProducts()
})
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

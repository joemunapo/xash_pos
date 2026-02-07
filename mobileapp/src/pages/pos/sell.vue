<template>
  <div class="min-h-screen bg-page flex flex-col">
    <!-- Header -->
    <div class="bg-primary-dark text-white px-4 py-3 pb-3 flex items-center justify-between sticky top-0 z-40 pt-safe">
      <div class="flex items-center">
        <router-link to="/pos/dashboard" class="p-1 mr-3">
          <i class="fas fa-arrow-left"></i>
        </router-link>
        <h1 class="text-lg font-bold mr-3">New Sale</h1>
        <NetworkIndicator />
      </div>
      <div class="flex items-center">
        <SyncStatusBadge />
        <button id="cart-btn" @click="showCart = true" class="relative p-2 ml-2 transition-transform duration-300">
          <i class="fas fa-shopping-cart text-xl"></i>
          <span
            v-if="cartStore.itemCount > 0"
            class="absolute bg-danger text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"
            style="top: -4px; right: -4px;"
          >
            {{ cartStore.itemCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="px-4 py-3 bg-white shadow-sm">
      <div class="flex items-center">
        <div class="relative flex-1 mr-2">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search products..."
            class="w-full pl-10 pr-10 py-2.5 bg-gray-100 border-0 rounded-lg outline-none"
            @keyup.enter="handleBarcodeSearch"
          />
          <i class="fas fa-search absolute left-3 text-gray-500" style="top: 50%; transform: translateY(-50%);"></i>
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3 text-gray-500"
            style="top: 50%; transform: translateY(-50%);"
          >
            <i class="fas fa-times-circle"></i>
          </button>
        </div>
        
        <div class="flex items-center gap-2">
          <!-- View Toggle -->
          <div class="flex items-center bg-gray-100 rounded-lg p-1">
            <button
              @click="toggleViewMode('grid')"
              :class="[
                'p-2 rounded-md transition-all flex items-center justify-center',
                viewMode === 'grid' ? 'bg-white shadow-sm text-primary' : 'text-gray-500'
              ]"
            >
              <i class="fas fa-th-large text-sm"></i>
            </button>
            <button
              @click="toggleViewMode('list')"
              :class="[
                'p-2 rounded-md transition-all flex items-center justify-center',
                viewMode === 'list' ? 'bg-white shadow-sm text-primary' : 'text-gray-500'
              ]"
            >
              <i class="fas fa-list text-sm"></i>
            </button>
          </div>

          <!-- Barcode Scan Button -->
          <button
            @click="handleCameraScan"
            :disabled="barcodeScannerActive"
            class="p-2.5 bg-primary-dark text-white rounded-lg flex items-center justify-center shadow-sm"
          >
            <i :class="barcodeScannerActive ? 'fas fa-spinner fa-spin' : 'fas fa-barcode'" class="text-lg"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Categories -->
    <div class="px-4 py-2 bg-white border-b overflow-x-auto">
      <div class="flex">
        <button
          @click="selectedCategory = null"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap mr-2',
            !selectedCategory ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'
          ]"
        >
          All
        </button>
        <button
          v-for="category in categories"
          :key="category.id"
          @click="selectedCategory = category.id"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap mr-2',
            selectedCategory === category.id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'
          ]"
        >
          {{ category.name }}
        </button>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="flex-1 p-4 overflow-y-auto pb-24">
      <div v-if="loading" class="flex items-center justify-center h-64">
        <i class="fas fa-spinner fa-spin text-3xl icon-primary"></i>
      </div>

      <div v-else-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-500">
        <i class="fas fa-box-open text-5xl mb-3 icon-gray"></i>
        <p class="mb-4">No products found</p>
        <button
          @click="fetchPOSData(true)"
          :disabled="loading"
          class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg font-medium shadow-sm hover:bg-primary-dark transition-all disabled:opacity-50"
        >
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          <span>Refresh Products</span>
        </button>
      </div>

      <!-- Products List (Grid View) -->
      <div v-if="viewMode === 'grid'" class="flex flex-wrap" style="margin: -0.5rem;">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          @click="isProductAvailable(product) && showQuantityModal(product)"
          class="w-1/2 sm:w-1/3 md:w-1/4 p-1"
        >
          <div
            :class="[
              'bg-white rounded-lg shadow p-3 h-full relative',
              isProductAvailable(product)
                ? 'cursor-pointer'
                : 'opacity-60 cursor-not-allowed'
            ]"
          >
            <div class="bg-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative p-2" style="aspect-ratio: 1;">
              <img
                v-if="getImageUrl(product.id)"
                :src="getImageUrl(product.id)"
                :alt="product.name"
                :class="[
                  'w-full h-full object-contain',
                  !isProductAvailable(product) && 'grayscale'
                ]"
                @error="handleImageError"
                :data-product-id="product.id"
              />
              <i v-if="!getImageUrl(product.id) || imageErrors[product.id]" class="fas fa-box text-4xl icon-gray"></i>

              <!-- Out of Stock Badge -->
              <div
                v-if="!isProductAvailable(product)"
                class="absolute inset-0 flex items-center justify-center bg-overlay"
              >
                <span class="bg-danger text-white text-xs font-bold px-2 py-1 rounded">
                  Out of Stock
                </span>
              </div>
            </div>
            <h3 class="text-sm font-medium text-gray-800 truncate">{{ product.name }}</h3>
            <div class="flex items-center justify-between mt-1">
              <span class="text-primary font-bold text-sm md:text-base">${{ formatMoney(product.branch_price || product.selling_price) }}</span>
              <span
                v-if="product.track_stock"
                :class="[
                  'text-[10px] font-medium',
                  product.stock_quantity > 10 ? 'text-primary' :
                  product.stock_quantity > 0 ? 'text-yellow-600' :
                  'text-danger'
                ]"
              >
                Stock: {{ Math.floor(product.stock_quantity) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Products List (List View) -->
      <div v-else-if="viewMode === 'list'" class="space-y-3">
        <div
          v-for="product in filteredProducts"
          :key="product.id"
          @click="isProductAvailable(product) && showQuantityModal(product)"
          :class="[
            'bg-white rounded-lg shadow p-3 hover:shadow-md transition relative',
            isProductAvailable(product) ? 'cursor-pointer' : 'opacity-60 cursor-not-allowed'
          ]"
        >
          <div class="flex">
            <!-- Product Image -->
            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 p-1 mr-3 relative">
              <img
                v-if="getImageUrl(product.id)"
                :src="getImageUrl(product.id)"
                :alt="product.name"
                :class="[
                  'w-full h-full object-contain',
                  !isProductAvailable(product) && 'grayscale'
                ]"
                @error="handleImageError"
                :data-product-id="product.id"
              />
              <i v-if="!getImageUrl(product.id) || imageErrors[product.id]" class="fas fa-box text-2xl icon-gray"></i>
              
              <!-- Out of Stock Overlay -->
              <div
                v-if="!isProductAvailable(product)"
                class="absolute inset-0 flex items-center justify-center bg-overlay/30"
              >
                <i class="fas fa-ban text-danger text-lg"></i>
              </div>
            </div>
            
            <!-- Product Info -->
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="font-semibold text-gray-800 text-sm truncate">{{ product.name }}</h3>
                  <p class="text-xs text-gray-500">{{ product.category?.name || 'Uncategorized' }}</p>
                </div>
                <div class="text-right">
                  <p class="text-primary font-bold text-base">${{ formatMoney(product.branch_price || product.selling_price) }}</p>
                  <p v-if="product.sku" class="text-[10px] text-gray-400 uppercase tracking-wider">{{ product.sku }}</p>
                </div>
              </div>
              
              <!-- Stock Status Badge -->
              <div class="flex items-center justify-between mt-2">
                <span
                  v-if="product.track_stock"
                  :class="[
                    'text-[10px] px-2 py-0.5 rounded-full font-medium',
                    product.stock_quantity > 10 ? 'status-success' :
                    product.stock_quantity > 0 ? 'status-pending' :
                    'status-failed'
                  ]"
                >
                  <i class="fas fa-box mr-1 text-[8px]"></i>
                  {{ product.stock_quantity <= 0 ? 'Out of Stock' : Math.floor(product.stock_quantity) + ' in stock' }}
                </span>
                <span v-else class="text-[10px] text-gray-400">Stock not tracked</span>
                
                <span v-if="product.barcode" class="text-[10px] text-gray-400">
                  <i class="fas fa-barcode mr-1"></i>{{ product.barcode }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart Summary Bar -->
    <div
      v-if="cartStore.itemCount > 0"
      class="cart-summary-bar"
    >
      <button
        @click="showCart = true"
        class="cart-summary-btn"
      >
        <i class="fas fa-shopping-cart mr-2"></i>
        <span>Cart</span>
        <span class="ml-2 px-2 py-0.5 rounded-full text-sm text-white bg-success">
          {{ cartStore.itemCount }}
        </span>
      </button>
      <button
        @click="proceedToCheckout"
        class="cart-summary-btn"
      >
        <span>Checkout</span>
        <i class="fas fa-arrow-right ml-2"></i>
      </button>
    </div>

    <!-- Cart Slide Panel -->
    <Transition name="slide">
      <div v-if="showCart" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-overlay" @click="showCart = false"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full bg-white shadow-xl flex flex-col" style="max-width: 28rem;">
          <!-- Cart Header -->
          <div class="bg-primary-dark text-white px-4 py-3 flex items-center justify-between">
            <h2 class="text-lg font-bold">Cart</h2>
            <button @click="showCart = false" class="p-1">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-4">
            <div v-if="cartStore.isEmpty" class="flex flex-col items-center justify-center h-full text-gray-500">
              <i class="fas fa-shopping-cart text-5xl mb-3 icon-gray"></i>
              <p>Cart is empty</p>
            </div>

            <div v-else>
              <div
                v-for="(item, index) in cartStore.items"
                :key="index"
                class="bg-gray-100 rounded-lg p-3 mb-3"
              >
                <div class="flex items-start justify-between mb-2">
                  <div class="flex-1">
                    <h3 class="font-medium text-gray-800">{{ item.name }}</h3>
                    <p class="text-sm text-gray-500">${{ formatMoney(item.unit_price) }} / {{ item.unit_name || item.unit }}</p>
                  </div>
                  <button @click="cartStore.removeItem(index)" class="text-danger p-1">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <button
                      @click="cartStore.decrementQuantity(index)"
                      class="w-8 h-8 bg-primary-light text-primary-dark rounded-lg flex items-center justify-center"
                    >
                      <i class="fas fa-minus"></i>
                    </button>
                    <input
                      type="number"
                      :value="item.quantity"
                      @change="(e) => cartStore.updateQuantity(index, parseFloat(e.target.value))"
                      class="w-16 text-center bg-white border rounded-lg py-1 mx-2 cart-quantity-input"
                      :step="item.allow_decimal_qty ? '0.001' : '1'"
                      min="0"
                    />
                    <button
                      @click="cartStore.incrementQuantity(index)"
                      class="w-8 h-8 bg-primary-light text-primary-dark rounded-lg flex items-center justify-center"
                    >
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                  <span class="font-bold text-primary">
                    ${{ formatMoney(item.unit_price * item.quantity) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Cart Footer -->
          <div v-if="!cartStore.isEmpty" class="border-t p-4">
            <div class="flex justify-between text-sm mb-3">
              <span class="text-gray-500">Subtotal</span>
              <span class="font-medium">${{ formatMoney(cartStore.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold mb-3">
              <span>Total</span>
              <span class="text-primary">${{ formatMoney(cartStore.totalAmount) }}</span>
            </div>

            <div class="flex">
              <button
                @click="cartStore.clearCart()"
                class="flex-1 py-3 btn btn-secondary mr-2"
              >
                Clear
              </button>
              <button
                @click="proceedToCheckout"
                class="flex-1 py-3 btn btn-primary ml-2"
              >
                Checkout
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Checkout Modal - Side Panel -->
    <Transition name="slide">
      <div v-if="showCheckout" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-overlay" @click="showCheckout = false"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full bg-white shadow-xl flex flex-col" style="max-width: 28rem;">
          <!-- Header -->
          <div class="bg-header text-white px-6 py-4 flex items-center justify-between">
            <div>
              <h2 class="text-xl font-bold">Complete Payment</h2>
              <p class="text-sm text-primary-light mt-0.5">{{ cartStore.itemCount }} items</p>
            </div>
            <button @click="showCheckout = false" class="p-2 bg-overlay-light rounded-lg">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <!-- Content -->
          <div class="flex-1 overflow-y-auto p-6">
            <!-- Total Amount Card -->
            <div class="bg-primary-light rounded-lg p-5 border-2 mb-6 border-success">
              <p class="text-sm text-primary-dark font-medium mb-1">Total Amount</p>
              <p class="text-4xl font-bold text-primary-dark">${{ formatMoney(cartStore.totalAmount) }}</p>
            </div>

            <!-- Payment Type Toggle -->
            <div class="mb-6">
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-semibold text-gray-700">Payment Type</label>
                <button
                  @click="useSplitPayment = !useSplitPayment"
                  class="text-xs font-medium text-primary flex items-center"
                >
                  <i class="fas mr-1" :class="useSplitPayment ? 'fa-minus-circle' : 'fa-plus-circle'"></i>
                  {{ useSplitPayment ? 'Single Payment' : 'Split Payment' }}
                </button>
              </div>

              <!-- Single Payment Method -->
              <div v-if="!useSplitPayment" class="flex flex-wrap" style="margin: -0.375rem;">
                <div v-for="method in paymentMethods" :key="method.value" class="w-1/3 p-1">
                  <button
                    @click="paymentMethod = method.value"
                    :class="[
                      'w-full p-4 rounded-lg border-2 text-center',
                      paymentMethod === method.value
                        ? 'border-primary bg-primary text-white shadow-lg'
                        : 'border-gray-200 text-gray-700 bg-white'
                    ]"
                  >
                    <i :class="[method.icon, 'text-2xl mb-2 block']"></i>
                    <p class="text-xs font-semibold">{{ method.label }}</p>
                  </button>
                </div>
              </div>

              <!-- Split Payment Methods -->
              <div v-else class="space-y-3">
                <div
                  v-for="(payment, index) in splitPayments"
                  :key="index"
                  class="bg-gray-50 rounded-lg p-3 border-2 border-gray-200"
                >
                  <div class="flex items-center mb-2">
                    <select
                      v-model="payment.method"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary outline-none"
                    >
                      <option value="cash">💵 Cash</option>
                      <option value="ecocash">📱 Ecocash</option>
                      <option value="swipe">💳 Card/Swipe</option>
                    </select>
                    <button
                      v-if="splitPayments.length > 1"
                      @click="removePaymentMethod(index)"
                      class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition ml-2"
                    >
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg font-bold text-gray-400">$</span>
                    <input
                      v-model.number="payment.amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-lg font-bold focus:ring-2 focus:ring-primary outline-none"
                      placeholder="0.00"
                      @input="validateSplitPayments"
                    />
                  </div>
                </div>

                <!-- Add Payment Button -->
                <button
                  v-if="splitPayments.length < 3"
                  @click="addPaymentMethod"
                  class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-600 rounded-lg font-medium hover:border-primary hover:text-primary-dark transition flex items-center justify-center"
                >
                  <i class="fas fa-plus-circle mr-2"></i>
                  Add Payment Method
                </button>

                <!-- Split Payment Summary -->
                <div class="bg-blue-50 rounded-lg p-3 space-y-1 text-sm">
                  <div class="flex justify-between font-medium text-blue-900">
                    <span>Total Amount:</span>
                    <span>${{ formatMoney(cartStore.totalAmount) }}</span>
                  </div>
                  <div class="flex justify-between text-blue-700">
                    <span>Allocated:</span>
                    <span>${{ formatMoney(allocatedAmount) }}</span>
                  </div>
                  <div class="flex justify-between font-bold" :class="remainingAmount === 0 ? 'text-success' : 'text-danger'">
                    <span>Remaining:</span>
                    <span>${{ formatMoney(remainingAmount) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Amount Received (Single Payment Only) -->
            <div v-if="!useSplitPayment">
              <label class="block text-sm font-semibold text-gray-700 mb-3">Amount Received</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-bold text-gray-400">$</span>
                <input
                  v-model.number="amountReceived"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl text-2xl font-bold focus:ring-2 focus:ring-primary focus:border-primary outline-none transition amount-input"
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Change Due -->
            <div v-if="changeAmount > 0" class="bg-yellow-50 rounded-lg p-5 border-2 border-yellow-200 mb-6">
              <p class="text-sm text-yellow-700 font-medium mb-1">Change Due</p>
              <p class="text-4xl font-bold text-yellow-800">${{ formatMoney(changeAmount) }}</p>
            </div>

            <!-- Error Message -->
            <div v-if="checkoutError" class="bg-danger-light border-2 text-danger rounded-lg p-4 flex items-start mb-6" style="border-color: #dc2626;">
              <i class="fas fa-exclamation-circle text-xl mr-3" style="margin-top: 2px;"></i>
              <p class="text-sm font-medium">{{ checkoutError }}</p>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="border-t bg-gray-100 p-6">
            <div class="flex">
              <button
                @click="showCheckout = false"
                class="flex-1 py-4 btn btn-secondary mr-2"
                :disabled="processing"
              >
                Cancel
              </button>
              <button
                @click="completeSale"
                :disabled="processing || (useSplitPayment ? remainingAmount !== 0 : (!amountReceived || parseFloat(amountReceived) < parseFloat(cartStore.totalAmount || 0)))"
                class="flex-1 py-4 btn btn-primary ml-2"
              >
                <span v-if="processing" class="flex items-center justify-center">
                  <i class="fas fa-spinner fa-spin mr-2"></i>
                  Processing...
                </span>
                <span v-else class="flex items-center justify-center">
                  <i class="fas fa-check-circle mr-2"></i>
                  Complete Sale
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Quantity Selection Modal -->
    <Transition name="fade">
      <div v-if="showQuantitySelector" class="modal-backdrop">
        <div class="absolute inset-0 bg-overlay" @click="closeQuantityModal"></div>
        <div class="modal-content">
          <!-- Product Info -->
          <div v-if="selectedProduct" class="mb-4">
            <div class="flex items-center mb-3">
              <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden p-2 mr-3" style="flex-shrink: 0;">
                <img
                  v-if="getImageUrl(selectedProduct.id)"
                  :src="getImageUrl(selectedProduct.id)"
                  :alt="selectedProduct.name"
                  class="w-full h-full object-contain"
                  @error="handleImageError"
                  :data-product-id="selectedProduct.id"
                />
                <i v-if="!getImageUrl(selectedProduct.id) || imageErrors[selectedProduct.id]" class="fas fa-box text-2xl icon-gray"></i>
              </div>
              <div class="flex-1">
                <h3 class="font-bold text-gray-800">{{ selectedProduct.name }}</h3>
                <p class="text-primary font-bold">${{ formatMoney(getCurrentUnitPrice()) }}</p>
                <p v-if="selectedProduct.track_stock" class="text-xs text-gray-500">
                  Available: {{ getAvailableQuantityInUnit() }} {{ selectedUnit?.abbreviation || selectedProduct.unit }}
                </p>
              </div>
            </div>
          </div>

          <!-- Unit Selection -->
          <div v-if="selectedProduct && availableUnits.length > 1" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
            <div class="flex flex-wrap" style="margin: -0.25rem;">
              <button
                v-for="unit in availableUnits"
                :key="unit.abbreviation"
                @click="selectUnit(unit)"
                :class="[
                  'p-2 m-1 rounded-lg border-2 text-left flex-1',
                  selectedUnit?.abbreviation === unit.abbreviation
                    ? 'border-primary-dark bg-primary-dark'
                    : 'border-gray-200 bg-white'
                ]"
                style="min-width: 120px;"
              >
                <div class="flex items-baseline justify-between">
                  <span :class="[
                    'font-medium text-sm',
                    selectedUnit?.abbreviation === unit.abbreviation ? 'text-white' : 'text-gray-800'
                  ]">{{ unit.name }} ({{ unit.quantity }})</span>
                  <span :class="[
                    'text-sm font-bold ml-2',
                    selectedUnit?.abbreviation === unit.abbreviation ? 'text-white' : 'text-primary'
                  ]">${{ formatMoney(unit.selling_price) }}</span>
                </div>
              </button>
            </div>
          </div>

          <!-- Quantity Controls -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
            <div class="flex items-center justify-center">
              <button
                @click="decrementModalQuantity"
                class="w-12 h-12 bg-danger text-white rounded-lg flex items-center justify-center text-xl font-bold"
              >
                <i class="fas fa-minus"></i>
              </button>
              <input
                v-model.number="modalQuantity"
                type="number"
                :step="selectedProduct?.allow_decimal_qty ? '0.01' : '1'"
                min="0"
                class="w-24 text-center text-2xl font-bold bg-gray-100 border-2 border-gray-300 rounded-lg py-3 mx-3 outline-none quantity-input"
                @input="validateModalQuantity"
              />
              <button
                @click="incrementModalQuantity"
                class="w-12 h-12 bg-primary text-white rounded-lg flex items-center justify-center text-xl font-bold"
              >
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>

          <!-- Total -->
          <div class="p-3 bg-primary-light rounded-lg mb-4">
            <div class="flex justify-between items-center">
              <span class="text-gray-700">Total</span>
              <span class="text-2xl font-bold text-primary-dark">
                ${{ formatMoney(getCurrentUnitPrice() * modalQuantity) }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex">
            <button
              @click="closeQuantityModal"
              class="flex-1 py-3 btn btn-secondary mr-2"
            >
              Cancel
            </button>
            <button
              @click="confirmAddToCart"
              :disabled="modalQuantity <= 0"
              class="flex-1 py-3 btn btn-primary ml-2"
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Sale Success Modal -->
    <Transition name="fade">
      <div v-if="showSuccess" class="modal-backdrop">
        <div class="absolute inset-0 bg-overlay"></div>
        <div class="modal-content text-center">
          <div class="modal-icon modal-icon-primary" style="width: 80px; height: 80px;">
            <i class="fas fa-check text-4xl"></i>
          </div>
          <h2 class="text-xl font-bold text-gray-800 mb-2">Sale Complete!</h2>
          <p class="text-gray-500 mb-4">Receipt #{{ lastReceiptNumber }}</p>
          <div class="p-4 bg-gray-100 rounded-lg mb-4">
            <p class="text-sm text-gray-500">Change Due</p>
            <p class="text-2xl font-bold text-primary">${{ formatMoney(lastChangeAmount) }}</p>
          </div>
          <div class="flex gap-3">
            <button
              v-if="lastSaleId && !customerSaved"
              @click="showCustomerModal = true"
              class="flex-1 py-3 btn btn-secondary"
            >
              <i class="fas fa-user-plus mr-2"></i>
              Save Customer
            </button>
            <div v-if="customerSaved" class="flex-1 py-3 bg-green-50 text-green-700 rounded-lg text-sm font-medium flex items-center justify-center">
              <i class="fas fa-check-circle mr-2"></i>
              Customer Saved
            </div>
            <button
              @click="startNewSale"
              class="flex-1 py-3 btn btn-primary"
            >
              Start New Sale
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Save Customer Modal -->
    <Transition name="fade">
      <div v-if="showCustomerModal" class="modal-backdrop" style="z-index: 60;">
        <div class="absolute inset-0 bg-overlay" @click="showCustomerModal = false"></div>
        <div class="modal-content">
          <h2 class="text-lg font-bold text-gray-800 mb-1">Save Customer Details</h2>
          <p class="text-sm text-gray-500 mb-4">Phone or email is required</p>

          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input
                v-model="customerForm.phone"
                type="tel"
                placeholder="e.g. 0771234567"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary focus:border-primary"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="customerForm.email"
                type="email"
                placeholder="e.g. customer@email.com"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary focus:border-primary"
              />
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input
                  v-model="customerForm.first_name"
                  type="text"
                  placeholder="Optional"
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                />
              </div>
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input
                  v-model="customerForm.last_name"
                  type="text"
                  placeholder="Optional"
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                />
              </div>
            </div>
          </div>

          <div v-if="customerError" class="mt-3 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
            {{ customerError }}
          </div>

          <div class="flex gap-3 mt-4">
            <button
              @click="showCustomerModal = false"
              class="flex-1 py-3 btn btn-secondary"
              :disabled="savingCustomer"
            >
              Cancel
            </button>
            <button
              @click="saveCustomer"
              :disabled="savingCustomer || (!customerForm.phone && !customerForm.email)"
              class="flex-1 py-3 btn btn-primary"
            >
              <span v-if="savingCustomer" class="flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Saving...
              </span>
              <span v-else class="flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Save
              </span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useCartStore, useAlertStore, useAuthStore } from '@/stores';
import { useNetworkStore } from '@/stores/network.store';
import { useSyncStore } from '@/stores/sync.store';
import { useOfflineStore } from '@/stores/offline.store';
import { fetchWrapper } from '@/helpers';
import NetworkIndicator from '@/components/NetworkIndicator.vue';
import SyncStatusBadge from '@/components/SyncStatusBadge.vue';
import { useProductImage } from '@/composables/useProductImage';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';
import { buildReceiptPayload, printReceipt } from '@/services/print';

// Beep sound for successful scan
let audioContext = null;
async function playBeep(frequency = 1000, duration = 150, volume = 0.5) {
  try {
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    // Resume audio context if suspended (required on mobile)
    if (audioContext.state === 'suspended') {
      await audioContext.resume();
    }
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();
    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);
    oscillator.frequency.value = frequency;
    oscillator.type = 'square'; // square wave is louder
    gainNode.gain.value = volume;
    oscillator.start();
    setTimeout(() => {
      oscillator.stop();
    }, duration);
    console.log('[POS] Beep played:', frequency, 'Hz');
  } catch (e) {
    console.log('[POS] Could not play beep:', e.message);
  }
}

const cartStore = useCartStore();
const alertStore = useAlertStore();
const authStore = useAuthStore();
const networkStore = useNetworkStore();
const syncStore = useSyncStore();
const offlineStore = useOfflineStore();
const { getProductImageUrl } = useProductImage();
const {
  scan: scanBarcode,
  isScanning: barcodeScannerActive,
  isSunmiDevice,
  initialize: initBarcodeScanner,
  initSunmiScanner,
  startSunmiListener,
  stopSunmiListener
} = useBarcodeScanner();
const baseUrl = import.meta.env.VITE_API_URL;

function getImageUrl(productId) {
  return productImageUrls.value[productId] || null;
}

async function loadProductImages(productsList) {
  // Load images for all products
  for (const product of productsList) {
    if (product.image && product.id) {
      try {
        const imageUrl = await getProductImageUrl(product.image);
        if (imageUrl) {
          productImageUrls.value[product.id] = imageUrl;
        }
      } catch (error) {
        // Silently fail
      }
    }
  }
}

function handleImageError(e) {
  const productId = e.target.getAttribute('data-product-id');
  if (productId) {
    imageErrors.value[productId] = true;
  }
}

const loading = ref(true);
const categories = ref([]);
const products = ref([]);
const productImageUrls = ref({});
const imageErrors = ref({});
const searchQuery = ref('');
const selectedCategory = ref(null);

const showCart = ref(false);
const showCheckout = ref(false);
const showSuccess = ref(false);
const showQuantitySelector = ref(false);
const isAnimating = ref(false);
const viewMode = ref(localStorage.getItem('pos_view_mode') || 'grid');

function toggleViewMode(mode) {
  viewMode.value = mode;
  localStorage.setItem('pos_view_mode', mode);
}

const selectedProduct = ref(null);
const selectedUnit = ref(null);
const modalQuantity = ref(1);

const paymentMethod = ref('cash');
const amountReceived = ref(0);
const processing = ref(false);
const checkoutError = ref('');
const lastReceiptNumber = ref('');
const lastChangeAmount = ref(0);
const lastSaleId = ref(null);

// Customer save state
const showCustomerModal = ref(false);
const savingCustomer = ref(false);
const customerSaved = ref(false);
const customerError = ref('');
const customerForm = ref({
  phone: '',
  email: '',
  first_name: '',
  last_name: '',
});

// Split payment state
const useSplitPayment = ref(false);
const splitPayments = ref([
  { method: 'cash', amount: 0 }
]);

const paymentMethods = [
  { value: 'cash', label: 'Cash', icon: 'fas fa-money-bill' },
  { value: 'card', label: 'Card', icon: 'fas fa-credit-card' },
  { value: 'mobile_money', label: 'Mobile', icon: 'fas fa-mobile-alt' },
];

// Split payment computed properties
const allocatedAmount = computed(() => {
  return splitPayments.value.reduce((sum, payment) => sum + (parseFloat(payment.amount) || 0), 0);
});

const remainingAmount = computed(() => {
  return cartStore.totalAmount - allocatedAmount.value;
});

const availableUnits = computed(() => {
  const productPrice = parseFloat(selectedProduct.value?.branch_price || selectedProduct.value?.selling_price) || 0;
  const productCost = parseFloat(selectedProduct.value?.cost_price) || 0;

  if (!selectedProduct.value?.product_units || selectedProduct.value.product_units.length === 0) {
    // If no units defined, create a default unit from product data
    return [{
      name: selectedProduct.value?.unit || 'Piece',
      abbreviation: selectedProduct.value?.unit || 'pc',
      quantity: 1,
      selling_price: productPrice,
      cost_price: productCost,
      is_default: true,
    }];
  }

  // Fill in missing prices from the product's base price
  return selectedProduct.value.product_units
    .sort((a, b) => a.sort_order - b.sort_order)
    .map(unit => ({
      ...unit,
      selling_price: parseFloat(unit.selling_price) || productPrice,
      cost_price: parseFloat(unit.cost_price) || productCost,
    }));
});

const changeAmount = computed(() => {
  const received = parseFloat(amountReceived.value) || 0;
  const total = parseFloat(cartStore.totalAmount) || 0;
  return Math.max(0, received - total);
});

const filteredProducts = computed(() => {
  let filtered = products.value;

  if (selectedCategory.value) {
    filtered = filtered.filter(p => p.category_id === selectedCategory.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.sku?.toLowerCase().includes(query) ||
      p.barcode?.toString().includes(query)
    );
  }

  return filtered;
});

async function fetchPOSData(forceRefresh = false) {
  loading.value = true;
  try {
    // Use offline store - it handles online/offline automatically
    // If online: fetches from API and caches
    // If offline: loads from cache
    const [productsData, categoriesData] = await Promise.all([
      offlineStore.getProducts(forceRefresh),
      offlineStore.getCategories(forceRefresh)
    ]);

    products.value = productsData || [];
    categories.value = categoriesData || [];

    // Load product images
    await loadProductImages(products.value);

    if (products.value.length === 0 && !networkStore.isOnline) {
      alertStore.warning('You are offline. No cached products available. Please connect to internet.');
    }
  } catch (error) {
    console.error('Failed to fetch POS data:', error);
    alertStore.error('Failed to load data');
  } finally {
    loading.value = false;
  }
}

async function handleBarcodeSearch() {
  if (!searchQuery.value) return;

  try {
    const response = await fetchWrapper.post(`${baseUrl}/pos/scan-barcode`, {
      barcode: searchQuery.value
    });

    if (response.product) {
      addToCart(response.product);
      searchQuery.value = '';
    } else {
      // If not found in API, try a full refresh to get latest products
      await fetchPOSData(true);
    }
  } catch (error) {
    // If API call fails, try a full refresh to get latest products
    await fetchPOSData(true);
  }
}

async function handleCameraScan() {
  try {
    const barcode = await scanBarcode();

    if (!barcode) {
      // User cancelled or no barcode found
      return;
    }

    // First, try to find the product in the local products list
    const localProduct = products.value.find(
      p => p.barcode === barcode || p.plu_code === barcode || p.sku === barcode
    );

    if (localProduct) {
      // Found locally - add directly to cart
      playBeep(); // Success beep
      if (isProductAvailable(localProduct)) {
        showQuantityModal(localProduct);
      } else {
        alertStore.error(`${localProduct.name} is out of stock`);
      }
      return;
    }

    // If not found locally, try the API
    try {
      const response = await fetchWrapper.post(`${baseUrl}/pos/scan-barcode`, {
        barcode: barcode
      });

      if (response.product) {
        playBeep(); // Success beep
        if (isProductAvailable(response.product)) {
          showQuantityModal(response.product);
        } else {
          alertStore.error(`${response.product.name} is out of stock`);
        }
      } else {
        playBeep(400, 200, 0.3); // Error beep
        alertStore.error(`Product not found for barcode: ${barcode}`);
        // Product not found in API, trigger a refresh to get latest products
        await fetchPOSData(true);
      }
    } catch (apiError) {
      playBeep(400, 200, 0.3); // Error beep
      alertStore.error(`Product not found for barcode: ${barcode}`);
      // Product not found in API, trigger a refresh to get latest products
      await fetchPOSData(true);
    }
  } catch (error) {
    console.error('Camera scan error:', error);
    alertStore.error('Failed to scan barcode');
  }
}

function isProductAvailable(product) {
  // If product doesn't track stock, it's always available
  if (!product.track_stock) return true;

  // If tracking stock, check if quantity > 0
  return product.stock_quantity > 0;
}

function selectUnit(unit) {
  selectedUnit.value = unit;
  modalQuantity.value = selectedProduct.value?.allow_decimal_qty ? 0.5 : 1;
}

function getCurrentUnitPrice() {
  return selectedUnit.value?.selling_price || selectedProduct.value?.branch_price || selectedProduct.value?.selling_price || 0;
}

function getAvailableQuantityInUnit() {
  if (!selectedProduct.value?.track_stock) return 0;

  const baseQuantity = selectedProduct.value.stock_quantity || 0;
  const unitMultiplier = selectedUnit.value?.quantity || 1;

  // Calculate how many of this unit are available
  return Math.floor(baseQuantity / unitMultiplier);
}

function showQuantityModal(product) {
  if (!isProductAvailable(product)) {
    alertStore.error(`${product.name} is out of stock`);
    return;
  }

  selectedProduct.value = product;

  // Set default unit (either the default unit or the first one)
  const units = product.product_units || [];
  if (units.length > 0) {
    selectedUnit.value = units.find(u => u.is_default) || units[0];
  } else {
    selectedUnit.value = {
      name: product.unit || 'Piece',
      abbreviation: product.unit || 'pc',
      quantity: 1,
      selling_price: product.branch_price || product.selling_price || 0,
      cost_price: product.cost_price || 0,
      is_default: true,
    };
  }

  modalQuantity.value = product.allow_decimal_qty ? 0.5 : 1;
  showQuantitySelector.value = true;
}

function closeQuantityModal() {
  showQuantitySelector.value = false;
  selectedProduct.value = null;
  selectedUnit.value = null;
  modalQuantity.value = 1;
}

function incrementModalQuantity() {
  const step = selectedProduct.value?.allow_decimal_qty ? 0.5 : 1;
  modalQuantity.value = parseFloat((modalQuantity.value + step).toFixed(3));
  validateModalQuantity();
}

function decrementModalQuantity() {
  const step = selectedProduct.value?.allow_decimal_qty ? 0.5 : 1;
  const newQty = modalQuantity.value - step;
  if (newQty >= 0) {
    modalQuantity.value = parseFloat(newQty.toFixed(3));
  }
}

function validateModalQuantity() {
  if (modalQuantity.value < 0) {
    modalQuantity.value = 0;
  }

  // Check against available stock in the selected unit
  if (selectedProduct.value?.track_stock) {
    const availableInUnit = getAvailableQuantityInUnit();
    if (modalQuantity.value > availableInUnit) {
      modalQuantity.value = availableInUnit;
      alertStore.warning(`Only ${availableInUnit} ${selectedUnit.value?.abbreviation || 'units'} available`);
    }
  }

  // Round to appropriate decimals
  if (selectedProduct.value?.allow_decimal_qty) {
    modalQuantity.value = parseFloat(parseFloat(modalQuantity.value).toFixed(3));
  } else {
    modalQuantity.value = Math.floor(modalQuantity.value);
  }
}

function confirmAddToCart() {
  if (!selectedProduct.value || !selectedUnit.value || modalQuantity.value <= 0) return;

  // Calculate the actual quantity in base units for stock tracking
  const baseQuantity = modalQuantity.value * (selectedUnit.value.quantity || 1);

  // Use getCurrentUnitPrice which has proper fallback to product price
  const unitPrice = getCurrentUnitPrice();

  // Prepare product data with unit information
  const productWithUnit = {
    ...selectedProduct.value,
    unit: selectedUnit.value.abbreviation,
    unit_name: selectedUnit.value.name,
    unit_multiplier: selectedUnit.value.quantity,
    selling_price: unitPrice,
    branch_price: unitPrice,
    cost_price: parseFloat(selectedUnit.value.cost_price) || parseFloat(selectedProduct.value.cost_price) || 0,
  };

  cartStore.addItem(productWithUnit, modalQuantity.value);
  
  // Floating drop animation
  const cartBtn = document.getElementById('cart-btn');
  if (cartBtn) {
    const dropItem = document.createElement('div');
    dropItem.className = 'fixed z-[100] w-12 h-12 bg-primary rounded-full flex items-center justify-center pointer-events-none shadow-lg';
    dropItem.innerHTML = '<i class="fas fa-cart-plus text-lg text-white"></i>';
    dropItem.style.transition = 'all 1s cubic-bezier(0.25, 0.1, 0.25, 1)';

    // Start from center of screen (approx modal position)
    dropItem.style.left = '50%';
    dropItem.style.top = '50%';
    dropItem.style.transform = 'translate(-50%, -50%) scale(1.2)';
    dropItem.style.opacity = '1';

    document.body.appendChild(dropItem);

    const rect = cartBtn.getBoundingClientRect();

    // Force reflow
    dropItem.offsetHeight;

    // Animate to cart position
    setTimeout(() => {
      dropItem.style.left = `${rect.left + rect.width / 2}px`;
      dropItem.style.top = `${rect.top + rect.height / 2}px`;
      dropItem.style.transform = 'translate(-50%, -50%) scale(0.5)';
      dropItem.style.opacity = '0.2';
    }, 10);

    // Remove after animation and trigger cart bounce
    setTimeout(() => {
      dropItem.remove();
      cartBtn.classList.add('animate-bounce-short');
      setTimeout(() => cartBtn.classList.remove('animate-bounce-short'), 500);
    }, 650);
  }
  
  closeQuantityModal();
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

// Split payment functions
function addPaymentMethod() {
  if (splitPayments.value.length < 3) {
    const usedMethods = splitPayments.value.map(p => p.method);
    const availableMethod = ['cash', 'ecocash', 'swipe'].find(m => !usedMethods.includes(m)) || 'cash';
    
    splitPayments.value.push({
      method: availableMethod,
      amount: Math.max(0, remainingAmount.value)
    });
  }
}

function removePaymentMethod(index) {
  if (splitPayments.value.length > 1) {
    splitPayments.value.splice(index, 1);
  }
}

function validateSplitPayments() {
  // Ensure amounts are valid
  splitPayments.value.forEach(payment => {
    if (payment.amount < 0) payment.amount = 0;
    payment.amount = parseFloat(payment.amount) || 0;
  });
}

function proceedToCheckout() {
  showCart.value = false;
  const total = parseFloat(cartStore.totalAmount) || 0;
  amountReceived.value = total;
  checkoutError.value = '';

  // Reset split payment to single payment mode
  useSplitPayment.value = false;
  splitPayments.value = [
    { method: 'cash', amount: 0 }
  ];

  showCheckout.value = true;
}

async function completeSale() {
  // Sanitize amount values
  const totalAmount = parseFloat(cartStore.totalAmount) || 0;
  const amountPaid = parseFloat(useSplitPayment.value ? allocatedAmount.value : amountReceived.value) || 0;

  // Validate payment
  if (useSplitPayment.value) {
    if (remainingAmount.value !== 0) {
      checkoutError.value = 'Payment allocation must equal total amount';
      return;
    }
    if (splitPayments.value.some(p => !p.method || p.amount <= 0)) {
      checkoutError.value = 'All payment methods must have valid amounts';
      return;
    }
  } else {
    if (!amountPaid || amountPaid < totalAmount) {
      checkoutError.value = 'Amount received is less than total';
      return;
    }
  }

  if (!paymentMethod.value && !useSplitPayment.value) {
    checkoutError.value = 'Please select a payment method';
    return;
  }

  processing.value = true;
  checkoutError.value = '';

  try {
    let response;

    // Prepare sale data
    const saleData = {
      items: cartStore.getItemsForSubmission(),
      customer_id: cartStore.customer?.id || null,
      discount_amount: parseFloat(cartStore.discountAmount) || 0,
      notes: cartStore.notes || null,
    };

    // Add payment info based on mode
    if (useSplitPayment.value) {
      saleData.payment_method = 'split';
      saleData.amount_paid = parseFloat(allocatedAmount.value) || 0;
      saleData.payments = splitPayments.value.map(p => ({
        method: p.method,
        amount: parseFloat(p.amount) || 0
      }));
    } else {
      saleData.payment_method = paymentMethod.value;
      saleData.amount_paid = amountPaid;
    }

    // Check network status
    if (networkStore.isOnline) {
      // Online path: Normal API call
      try {
        response = await fetchWrapper.post(`${baseUrl}/pos/sales`, saleData);

        lastReceiptNumber.value = response.sale.receipt_number;
        lastChangeAmount.value = response.sale.change_amount;
        lastSaleId.value = response.sale.id;

        // Update local stock immediately for each item sold
        cartStore.items.forEach(item => {
          const quantityChange = -(item.quantity * (item.unit_multiplier || 1));

          // Also update the local products array for immediate UI update
          const productIndex = products.value.findIndex(p => p.id === item.product_id);
          if (productIndex !== -1) {
            products.value[productIndex].stock_quantity += quantityChange;
          }
        });

      } catch (apiError) {
        // If API call fails due to network error, fallback to offline mode
        if (apiError.message?.includes('Network error') || apiError.isOffline) {
          console.log('API call failed due to network error, falling back to offline mode');
          response = await cartStore.completeSaleOffline(paymentMethod.value, amountReceived.value);
          lastReceiptNumber.value = response.receipt_number;
          lastChangeAmount.value = response.change_amount;
          lastSaleId.value = null;

          // Update the local products array for immediate UI update
          cartStore.items.forEach(item => {
            const quantityChange = -(item.quantity * (item.unit_multiplier || 1));
            const productIndex = products.value.findIndex(p => p.id === item.product_id);
            if (productIndex !== -1) {
              products.value[productIndex].stock_quantity += quantityChange;
            }
          });

          alertStore.warning('Network interrupted. Sale saved offline and will sync later.');
        } else {
          throw apiError;
        }
      }
    } else {
      // Offline path: Save locally and queue for sync
      response = await cartStore.completeSaleOffline(paymentMethod.value, amountReceived.value);
      lastReceiptNumber.value = response.receipt_number;
      lastChangeAmount.value = response.change_amount;
      lastSaleId.value = null;

      // Update the local products array for immediate UI update
      cartStore.items.forEach(item => {
        const quantityChange = -(item.quantity * (item.unit_multiplier || 1));
        const productIndex = products.value.findIndex(p => p.id === item.product_id);
        if (productIndex !== -1) {
          products.value[productIndex].stock_quantity += quantityChange;
        }
      });

      alertStore.warning('You are offline. Sale saved and will sync when connection is restored.');
    }

    // Print receipt automatically
    try {
      console.log('[POS] Attempting to print receipt...');

      const saleForPrint = {
        ...(response?.sale || {}),
        receipt_number: lastReceiptNumber.value,
        created_at: response?.sale?.created_at || new Date().toISOString(),
        items: cartStore.items.map(item => ({
          product_name: item.name,
          quantity: item.quantity,
          unit_price: item.unit_price,
          total: item.quantity * item.unit_price,
        })),
      };

      const receiptData = buildReceiptPayload(saleForPrint, {
        user: authStore.user,
        subtotal: cartStore.subtotal,
        discount_amount: cartStore.discountAmount,
        total_amount: cartStore.totalAmount,
        payment_method: useSplitPayment.value ? 'split' : paymentMethod.value,
        payments: useSplitPayment.value ? splitPayments.value : null,
        amount_paid: useSplitPayment.value ? allocatedAmount.value : amountReceived.value,
        change_amount: lastChangeAmount.value,
        customer_name: cartStore.customer?.name || null,
      });

      await printReceipt(receiptData);
      console.log('[POS] Receipt printed successfully');
    } catch (printError) {
      console.error('[POS] Failed to print receipt:', printError);
      // Don't fail the sale if printing fails, just log it
      alertStore.warning('Sale completed but receipt printing failed');
    }

    showCheckout.value = false;
    showSuccess.value = true;
    cartStore.clearCart();

  } catch (error) {
    checkoutError.value = error.message || 'Failed to complete sale';
    console.error('Sale completion error:', error);
  } finally {
    processing.value = false;
  }
}

async function saveCustomer() {
  if (!customerForm.value.phone && !customerForm.value.email) {
    customerError.value = 'Please provide a phone number or email address';
    return;
  }

  savingCustomer.value = true;
  customerError.value = '';

  try {
    // Create customer
    const customerResponse = await fetchWrapper.post(`${baseUrl}/pos/customers`, customerForm.value);
    const customer = customerResponse.customer;

    // Attach customer to the sale
    if (lastSaleId.value && customer?.id) {
      await fetchWrapper.post(`${baseUrl}/pos/sales/${lastSaleId.value}/customer`, {
        customer_id: customer.id,
      });
    }

    customerSaved.value = true;
    showCustomerModal.value = false;

    if (customerResponse.existing) {
      alertStore.info('Existing customer linked to sale');
    } else {
      alertStore.success('Customer saved and linked to sale');
    }
  } catch (error) {
    customerError.value = error.message || 'Failed to save customer';
  } finally {
    savingCustomer.value = false;
  }
}

async function startNewSale() {
  showSuccess.value = false;
  amountReceived.value = 0;
  paymentMethod.value = 'cash';
  lastSaleId.value = null;

  // Reset customer state
  customerSaved.value = false;
  showCustomerModal.value = false;
  customerError.value = '';
  customerForm.value = { phone: '', email: '', first_name: '', last_name: '' };

  // Reset split payment state
  useSplitPayment.value = false;
  splitPayments.value = [
    { method: 'cash', amount: 0 }
  ];

  // Background refresh - don't await, don't show loading
  refreshProductsInBackground();
}

async function refreshProductsInBackground() {
  try {
    const [productsData, categoriesData] = await Promise.all([
      offlineStore.getProducts(true),
      offlineStore.getCategories(true)
    ]);

    // Silently update products without loading state
    if (productsData?.length > 0) {
      products.value = productsData;
    }
    if (categoriesData?.length > 0) {
      categories.value = categoriesData;
    }
  } catch (error) {
    // Silent fail - local stock is already updated
    console.log('Background refresh failed:', error);
  }
}

// Handle barcode from Sunmi hardware scanner
async function handleHardwareScan(barcode) {
  console.log('[POS] Hardware scan received:', barcode);

  if (!barcode) return;

  // First, try to find the product in the local products list
  const localProduct = products.value.find(
    p => p.barcode === barcode || p.plu_code === barcode || p.sku === barcode
  );

  if (localProduct) {
    // Found locally - add directly to cart
    playBeep(); // Success beep
    if (isProductAvailable(localProduct)) {
      showQuantityModal(localProduct);
    } else {
      alertStore.error(`${localProduct.name} is out of stock`);
    }
    return;
  }

  // If not found locally, try the API
  try {
    const response = await fetchWrapper.post(`${baseUrl}/pos/scan-barcode`, {
      barcode: barcode
    });

    if (response.product) {
      playBeep(); // Success beep
      if (isProductAvailable(response.product)) {
        showQuantityModal(response.product);
      } else {
        alertStore.error(`${response.product.name} is out of stock`);
      }
    } else {
      playBeep(400, 200, 0.3); // Error beep (lower frequency)
      alertStore.error(`Product not found for barcode: ${barcode}`);
      // Product not found in API, trigger a refresh to get latest products
      await fetchPOSData(true);
    }
  } catch (apiError) {
    playBeep(400, 200, 0.3); // Error beep
    alertStore.error(`Product not found for barcode: ${barcode}`);
    // Product not found in API, trigger a refresh to get latest products
    await fetchPOSData(true);
  }
}

onMounted(async () => {
  fetchPOSData();

  // Pre-initialize barcode scanner in background (so it opens faster)
  initBarcodeScanner().catch(() => {});

  // Initialize Sunmi hardware scanner if available
  const isSunmi = await initSunmiScanner();
  if (isSunmi) {
    console.log('[POS] Sunmi device detected, starting hardware scanner listener');
    await startSunmiListener(handleHardwareScan);
  }
});

onUnmounted(() => {
  // Clean up Sunmi scanner listener
  stopSunmiListener();
});
</script>

<style scoped>
/* Hide number input spinners */
.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button,
.amount-input::-webkit-outer-spin-button,
.amount-input::-webkit-inner-spin-button,
.cart-quantity-input::-webkit-outer-spin-button,
.cart-quantity-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.quantity-input[type="number"],
.amount-input[type="number"],
.cart-quantity-input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}

.animate-bounce-short {
  animation: bounce-short 0.5s ease;
}

@keyframes bounce-short {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.3); }
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

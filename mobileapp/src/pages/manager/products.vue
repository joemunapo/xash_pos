<template>
  <div class="space-y-4">
    <!-- Toast Notification -->
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="toast.show"
        class="fixed top-4 right-4 z-[60] max-w-sm w-full rounded-lg shadow-lg p-4"
        :class="toast.type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'"
      >
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <i
              :class="toast.type === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500'"
              class="text-lg"
            ></i>
          </div>
          <div class="flex-1 min-w-0">
            <p
              class="text-sm font-medium"
              :class="toast.type === 'success' ? 'text-green-800' : 'text-red-800'"
            >
              {{ toast.title }}
            </p>
            <p
              class="text-sm mt-1"
              :class="toast.type === 'success' ? 'text-green-600' : 'text-red-600'"
            >
              {{ toast.message }}
            </p>
          </div>
          <button
            @click="toast.show = false"
            class="flex-shrink-0"
            :class="toast.type === 'success' ? 'text-green-400 hover:text-green-600' : 'text-red-400 hover:text-red-600'"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Products</h1>
        <p class="text-gray-600 text-sm">{{ products.length }} products</p>
      </div>
      <button
        @click="openAddModal"
        class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all shadow-sm hover:shadow-md flex items-center gap-2 text-sm"
      >
        <i class="fas fa-plus"></i>
        <span class="hidden md:inline">Add Product</span>
        <span class="md:hidden">Add</span>
      </button>
    </div>

    <!-- Search Bar & Filter Button -->
    <div class="flex items-center justify-end gap-2 mb-5">
      <div class="relative flex-1">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
        <input
          v-model="searchQuery"
          @input="searchProducts"
          type="text"
          placeholder="Search..."
          class="w-full pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:border-primary transition-all shadow-sm"
        />
      </div>

      <!-- Filter Button -->
      <button
        @click="showFilterModal = true"
        class="relative px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2"
      >
        <i class="fas fa-sliders-h text-sm"></i>
        <span class="hidden md:inline text-sm">Filters</span>
        <span
          v-if="hasActiveFilters"
          class="absolute -top-1 -right-1 w-4 h-4 bg-accent text-white text-xs rounded-full flex items-center justify-center"
        >
          {{ activeFilterCount }}
        </span>
      </button>

      <!-- View Toggle -->
      <div class="flex items-center bg-white border border-gray-200 rounded-lg shadow-sm">
        <button
          @click="viewMode = 'list'"
          :class="[
            'px-2.5 py-1.5 rounded-lg text-sm transition-all',
            viewMode === 'list' ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <i class="fas fa-list text-xs"></i>
        </button>
        <button
          @click="viewMode = 'grid'"
          :class="[
            'px-2.5 py-1.5 rounded-lg text-sm transition-all',
            viewMode === 'grid' ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700'
          ]"
        >
          <i class="fas fa-th-large text-xs"></i>
        </button>
      </div>
    </div>
    
    <br></br>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <i class="fas fa-spinner fa-spin text-3xl text-primary"></i>
    </div>

    <!-- Empty State -->
    <div v-else-if="products.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center">
      <i class="fas fa-box text-5xl text-gray-300 mb-4"></i>
      <p class="text-gray-500 text-lg">No products found</p>
      <p class="text-gray-400 text-sm mt-1">Add your first product to get started</p>
    </div>

    <!-- List View -->
    <div v-else-if="viewMode === 'list'" class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Price</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Stock</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
              <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                    <img
                      v-if="product.image"
                      :src="getImageUrl(product.image)"
                      :alt="product.name"
                      class="w-full h-full object-cover"
                      @error="handleImageError($event, product)"
                    />
                    <i v-else class="fas fa-box text-gray-400 text-lg"></i>
                  </div>
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ product.name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ product.category?.name || 'Uncategorized' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                <span class="font-semibold text-gray-800">${{ formatMoney(product.price) }}</span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex flex-col items-end">
                  <span class="font-semibold text-gray-800">{{ product.stock_quantity || 0 }}</span>
                  <span
                    class="text-xs font-medium"
                    :class="product.stock_quantity > 10 ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ product.stock_quantity > 10 ? 'Good' : 'Low' }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-center">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                >
                  {{ product.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-1">
                  <button @click="openViewModal(product)" class="p-1.5 md:px-2.5 md:py-1.5 rounded transition flex items-center gap-1 text-white bg-gray-600 hover:bg-gray-700">
                    <i class="fas fa-eye text-sm"></i>
                    <span class="hidden md:inline text-xs font-medium">View</span>
                  </button>
                  <button @click="openEditModal(product)" class="p-1.5 md:px-2.5 md:py-1.5 bg-primary rounded transition flex items-center gap-1 text-white">
                    <i class="fas fa-edit text-sm"></i>
                    <span class="hidden md:inline text-xs font-medium">Edit</span>
                  </button>
                  <button @click="confirmDelete(product)" class="p-1.5 md:px-2.5 md:py-1.5 bg-danger rounded transition flex items-center gap-1 text-white">
                    <i class="fas fa-trash text-sm"></i>
                    <span class="hidden md:inline text-xs font-medium">Delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Grid View -->
    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <div
        v-for="product in products"
        :key="product.id"
        class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
      >
        <!-- Product Image -->
        <div class="aspect-square bg-gray-100 relative">
          <img
            v-if="product.image"
            :src="getImageUrl(product.image)"
            :alt="product.name"
            class="w-full h-full object-cover"
            @error="handleImageError($event, product)"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <i class="fas fa-box text-gray-300 text-4xl"></i>
          </div>
          <!-- Status Badge -->
          <span
            class="absolute top-2 right-2 px-2 py-0.5 text-xs font-medium rounded-full"
            :class="product.is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
          >
            {{ product.is_active ? 'Active' : 'Inactive' }}
          </span>
          <!-- Stock Badge -->
          <span
            class="absolute top-2 left-2 px-2 py-0.5 text-xs font-medium rounded-full"
            :class="product.stock_quantity > 10 ? 'bg-gray-500 text-white' : 'bg-orange-500 text-white'"
          >
            {{ product.stock_quantity || 0 }}
          </span>
        </div>

        <!-- Product Info -->
        <div class="p-3">
          <h3 class="font-semibold text-gray-800 text-sm truncate" :title="product.name">{{ product.name }}</h3>
          <span class="text-lg font-bold text-green-600">${{ formatMoney(product.price) }}</span>

          <!-- Actions -->
          <div class="flex items-center gap-1 mt-3 pt-3 border-t border-gray-100">
            <button
              @click="openViewModal(product)"
              class="flex-1 py-1.5 text-xs font-medium text-white rounded transition bg-gray-600 hover:bg-gray-700"
            >
              <i class="fas fa-eye md:mr-1"></i><span class="hidden md:inline">View</span>
            </button>
            <button
              @click="openEditModal(product)"
              class="flex-1 py-1.5 text-xs font-medium text-white bg-primary rounded transition"
            >
              <i class="fas fa-edit md:mr-1"></i><span class="hidden md:inline">Edit</span>
            </button>
            <button
              @click="confirmDelete(product)"
              class="flex-1 py-1.5 text-xs font-medium text-white bg-danger rounded transition"
            >
              <i class="fas fa-trash md:mr-1"></i><span class="hidden md:inline">Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Product Modal with Stepper -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="p-4 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
          <div>
            <h2 class="text-lg font-bold text-gray-800">
              {{ isEditMode ? 'Edit Product' : 'Add New Product' }}
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ isEditMode ? 'Update product information' : 'Add a new product to your inventory' }}</p>
          </div>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 p-1">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <!-- Stepper Navigation -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex-shrink-0">
          <div class="flex items-center justify-between">
            <template v-for="(step, index) in steps" :key="index">
              <div
                class="flex items-center cursor-pointer"
                @click="goToStep(index)"
              >
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs transition-all"
                  :class="currentStep === index
                    ? 'bg-primary text-white'
                    : currentStep > index
                      ? 'bg-primary-light text-primary'
                      : 'bg-gray-200 text-gray-500'"
                >
                  <i v-if="currentStep > index" class="fas fa-check text-xs"></i>
                  <span v-else>{{ index + 1 }}</span>
                </div>
                <span
                  class="ml-2 text-xs font-medium hidden sm:block"
                  :class="currentStep === index ? 'text-primary' : 'text-gray-500'"
                >
                  {{ step.title }}
                </span>
              </div>
              <div
                v-if="index < steps.length - 1"
                class="flex-1 mx-3 h-0.5"
                :class="currentStep > index ? 'bg-primary' : 'bg-gray-200'"
              ></div>
            </template>
          </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 overflow-y-auto p-4">
          <form @submit.prevent="saveProduct" class="space-y-4">
            <!-- Step 1: Basic Information -->
            <div v-show="currentStep === 0" class="space-y-4">
              <!-- Image Upload -->
              <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div
                  class="w-24 h-24 bg-white rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden flex-shrink-0 cursor-pointer hover:border-green-500 transition-colors"
                  @click="!isCompressing && $refs.cameraInput.click()"
                >
                  <div v-if="isCompressing" class="text-center">
                    <i class="fas fa-spinner fa-spin text-green-500 text-2xl"></i>
                    <p class="text-xs text-gray-400 mt-1">Processing...</p>
                  </div>
                  <img
                    v-else-if="imagePreview"
                    :src="imagePreview"
                    alt="Product preview"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="text-center">
                    <i class="fas fa-camera text-gray-400 text-2xl"></i>
                    <p class="text-xs text-gray-400 mt-1">Add Image</p>
                  </div>
                </div>
                <div class="flex-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                  <p class="text-xs text-gray-500 mb-2">Take a photo or upload an image (max 2MB)</p>
                  <!-- Hidden file inputs -->
                  <input
                    ref="imageInput"
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    class="hidden"
                    @change="handleImageSelect"
                  />
                  <input
                    ref="cameraInput"
                    type="file"
                    accept="image/*"
                    capture="environment"
                    class="hidden"
                    @change="handleImageSelect"
                  />
                  <div class="flex flex-wrap gap-2">
                    <button
                      type="button"
                      @click="$refs.cameraInput.click()"
                      :disabled="isCompressing"
                      class="px-3 py-1.5 text-xs font-medium bg-primary text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                    >
                      <i :class="isCompressing ? 'fas fa-spinner fa-spin' : 'fas fa-camera'" class="mr-1"></i>Camera
                    </button>
                    <button
                      type="button"
                      @click="$refs.imageInput.click()"
                      :disabled="isCompressing"
                      class="px-3 py-1.5 text-xs font-medium bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
                    >
                      <i class="fas fa-upload mr-1"></i>Gallery
                    </button>
                    <button
                      v-if="imagePreview && !isCompressing"
                      type="button"
                      @click="removeImage"
                      class="px-3 py-1.5 text-xs font-medium bg-danger text-white rounded-lg hover:bg-red-700 transition"
                    >
                      <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all"
                      placeholder="Enter product name"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <div class="relative">
                      <select
                        v-model="form.category_id"
                        class="w-full appearance-none px-4 py-3 pr-10 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-primary focus:bg-white focus:border-primary transition-all cursor-pointer"
                      >
                        <option value="">Select category</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                      </select>
                      <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure</label>
                    <div class="relative">
                      <select
                        v-model="form.unit"
                        class="w-full appearance-none px-4 py-3 pr-10 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all cursor-pointer"
                      >
                        <option value="pcs">Pieces (pcs)</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="g">Grams (g)</option>
                        <option value="l">Liters (l)</option>
                        <option value="ml">Milliliters (ml)</option>
                        <option value="box">Box</option>
                        <option value="pack">Pack</option>
                        <option value="carton">Carton</option>
                      </select>
                      <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                    </div>
                  </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU *</label>
                    <input
                      v-model="form.sku"
                      type="text"
                      required
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all"
                      placeholder="Auto-generated if empty"
                    />
                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                    <div class="flex gap-2">
                      <input
                        v-model="form.barcode"
                        type="text"
                        class="flex-1 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all"
                        placeholder="Scan or enter barcode"
                      />
                      <button
                        type="button"
                        @click="handleScanBarcode"
                        :disabled="isScanningBarcode"
                        class="px-4 py-3 bg-primary text-white rounded-lg hover:bg-green-700 transition flex items-center justify-center"
                      >
                        <i :class="isScanningBarcode ? 'fas fa-spinner fa-spin' : 'fas fa-barcode'"></i>
                      </button>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea
                      v-model="form.description"
                      rows="3"
                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all resize-none"
                      placeholder="Product description (optional)"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Pricing -->
            <div v-show="currentStep === 1" class="space-y-4">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price *</label>
                  <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">$</span>
                    <input
                      v-model="form.cost_price"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all"
                      placeholder="0.00"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price *</label>
                  <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">$</span>
                    <input
                      v-model="form.selling_price"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all"
                      placeholder="0.00"
                    />
                  </div>
                </div>
              </div>

              <!-- Profit Margin Indicator -->
              <div v-if="form.cost_price > 0 && form.selling_price > 0" class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Profit Margin</span>
                  <span
                    :class="profitMargin >= 0 ? 'text-green-600' : 'text-red-600'"
                    class="text-lg font-bold"
                  >
                    {{ profitMargin.toFixed(1) }}%
                  </span>
                </div>
                <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div
                    class="h-full transition-all duration-300"
                    :class="profitMargin >= 0 ? 'bg-green-500' : 'bg-red-500'"
                    :style="{ width: Math.min(Math.max(profitMargin, 0), 100) + '%' }"
                  ></div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Wholesale Price (Optional)</label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">$</span>
                  <input
                    v-model="form.wholesale_price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all"
                    placeholder="0.00"
                  />
                </div>
                <p class="text-xs text-gray-500 mt-1">Price for bulk/wholesale customers</p>
              </div>
            </div>

            <!-- Step 3: Inventory -->
            <div v-show="currentStep === 2" class="space-y-4">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Initial Stock Quantity *</label>
                  <input
                    v-model="form.stock_quantity"
                    type="number"
                    min="0"
                    :required="!isEditMode"
                    :disabled="isEditMode"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all disabled:bg-gray-100 disabled:cursor-not-allowed"
                    placeholder="0"
                  />
                  <p v-if="isEditMode" class="text-xs text-amber-600 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Use Inventory page to adjust stock
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Level</label>
                  <input
                    v-model="form.reorder_level"
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 focus:ring-2 focus:ring-green-500 focus:bg-white focus:border-green-500 transition-all"
                    placeholder="10"
                  />
                  <p class="text-xs text-gray-500 mt-1">Alert when stock falls below this level</p>
                </div>
              </div>

              <!-- Tracking Options -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tracking Options</label>
                <div class="space-y-3">
                  <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors border border-gray-200">
                    <input type="checkbox" v-model="form.track_stock" class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500" />
                    <div class="flex-1">
                      <span class="text-sm font-medium text-gray-800">Track Stock</span>
                      <p class="text-xs text-gray-500">Monitor inventory levels and get low stock alerts</p>
                    </div>
                  </label>
                  <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors border border-gray-200">
                    <input type="checkbox" v-model="form.track_expiry" class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500" />
                    <div class="flex-1">
                      <span class="text-sm font-medium text-gray-800">Track Expiry</span>
                      <p class="text-xs text-gray-500">Monitor expiration dates for perishable items</p>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Status Toggle -->
              <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="flex items-center justify-between cursor-pointer">
                  <div>
                    <span class="text-sm font-medium text-gray-800">Product Active</span>
                    <p class="text-xs text-gray-500">Inactive products won't appear in POS</p>
                  </div>
                  <div class="relative">
                    <input type="checkbox" v-model="form.is_active" class="sr-only peer" />
                    <div class="w-11 h-6 bg-gray-300 peer-focus:ring-2 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                  </div>
                </label>
              </div>
            </div>

            <!-- Error Display -->
            <div v-if="formError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                <p class="text-sm text-red-600">{{ formError }}</p>
              </div>
            </div>
          </form>
        </div>

        <!-- Modal Footer with Navigation -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between flex-shrink-0 bg-gray-50">
          <button
            v-if="currentStep > 0"
            type="button"
            @click="previousStep"
            class="px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium flex items-center gap-1.5"
          >
            <i class="fas fa-arrow-left text-xs"></i>
            Previous
          </button>
          <div v-else></div>

          <div class="flex gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-3 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium"
            >
              Cancel
            </button>
            <button
              v-if="currentStep < steps.length - 1"
              type="button"
              @click="nextStep"
              class="px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center gap-1.5"
            >
              Next
              <i class="fas fa-arrow-right text-xs"></i>
            </button>
            <button
              v-else
              type="button"
              @click="saveProduct"
              :disabled="saving"
              class="px-3 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
              <i v-else class="fas fa-check text-xs"></i>
              {{ saving ? 'Saving...' : isEditMode ? 'Update' : 'Create' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View Product Modal -->
    <div
      v-if="showViewModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeViewModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-xl font-bold text-gray-800">Product Details</h2>
          <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600 p-2">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <div v-if="selectedProduct" class="p-6 space-y-5">
          <!-- Product Header -->
          <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center">
              <i class="fas fa-box text-green-600 text-2xl"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-bold text-gray-800">{{ selectedProduct.name }}</h3>
              <p class="text-sm text-gray-500">SKU: {{ selectedProduct.sku }}</p>
            </div>
            <span
              class="px-3 py-1.5 text-xs font-medium rounded-full"
              :class="selectedProduct.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            >
              {{ selectedProduct.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>

          <!-- Pricing Card -->
          <div class="grid grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <p class="text-xs font-medium text-gray-500 uppercase mb-1">Cost Price</p>
              <p class="text-xl font-bold text-gray-800">${{ formatMoney(selectedProduct.cost_price) }}</p>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
              <p class="text-xs font-medium text-gray-500 uppercase mb-1">Selling Price</p>
              <p class="text-xl font-bold text-green-600">${{ formatMoney(selectedProduct.price) }}</p>
            </div>
          </div>

          <!-- Stock Info -->
          <div class="p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <p class="text-xs font-medium text-gray-500 uppercase">Stock Quantity</p>
              <span
                class="text-xs font-medium px-2 py-1 rounded"
                :class="selectedProduct.stock_quantity > (selectedProduct.reorder_level || 10) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ selectedProduct.stock_quantity > (selectedProduct.reorder_level || 10) ? 'In Stock' : 'Low Stock' }}
              </span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ selectedProduct.stock_quantity || 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Reorder level: {{ selectedProduct.reorder_level || 'Not set' }}</p>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4">
            <button
              @click="closeViewModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Close
            </button>
            <button
              @click="openEditModal(selectedProduct); closeViewModal();"
              class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium flex items-center justify-center gap-2"
            >
              <i class="fas fa-edit"></i>Edit
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="closeDeleteModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="p-6">
          <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Delete Product?</h3>
          <p class="text-gray-600 text-center mb-6">
            Are you sure you want to delete <strong>{{ productToDelete?.name }}</strong>? This action cannot be undone.
          </p>

          <div class="flex gap-3">
            <button
              @click="closeDeleteModal"
              class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
            >
              Cancel
            </button>
            <button
              @click="deleteProduct"
              :disabled="deleting"
              class="flex-1 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium disabled:bg-red-400 disabled:cursor-not-allowed"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bottom Modal -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showFilterModal"
        class="fixed inset-0 bg-black/50 z-50"
        @click="showFilterModal = false"
      ></div>
    </Transition>

    <Transition
      enter-active-class="transition ease-out duration-300 transform"
      enter-from-class="translate-y-full"
      enter-to-class="translate-y-0"
      leave-active-class="transition ease-in duration-200 transform"
      leave-from-class="translate-y-0"
      leave-to-class="translate-y-full"
    >
      <div
        v-if="showFilterModal"
        class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl z-50 max-h-[80vh] overflow-hidden"
      >
        <!-- Handle Bar -->
        <div class="flex justify-center pt-3 pb-2">
          <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between px-5 pb-4 border-b border-gray-100">
          <h3 class="text-lg font-bold text-gray-800">Filters</h3>
          <div class="flex items-center gap-3">
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="text-sm text-danger font-medium"
            >
              Clear All
            </button>
            <button
              @click="showFilterModal = false"
              class="p-1.5 hover:bg-gray-100 rounded-lg transition"
            >
              <i class="fas fa-times text-gray-500"></i>
            </button>
          </div>
        </div>

        <!-- Filter Options -->
        <div class="p-5 space-y-5 overflow-y-auto max-h-[50vh]">
          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Status</label>
            <div class="flex flex-wrap gap-2">
              <button
                @click="filters.status = ''"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.status === '' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                All
              </button>
              <button
                @click="filters.status = 'active'"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.status === 'active' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                <i class="fas fa-check-circle mr-1"></i>Active
              </button>
              <button
                @click="filters.status = 'inactive'"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.status === 'inactive' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                <i class="fas fa-times-circle mr-1"></i>Inactive
              </button>
            </div>
          </div>

          <!-- Stock Filter -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Stock Level</label>
            <div class="flex flex-wrap gap-2">
              <button
                @click="filters.stock = ''"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.stock === '' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                All
              </button>
              <button
                @click="filters.stock = 'high'"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.stock === 'high' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                <i class="fas fa-box mr-1"></i>In Stock
              </button>
              <button
                @click="filters.stock = 'low'"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.stock === 'low' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                <i class="fas fa-exclamation-triangle mr-1"></i>Low Stock
              </button>
            </div>
          </div>

          <!-- Category Filter -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Category</label>
            <div class="flex flex-wrap gap-2">
              <button
                @click="filters.category = ''"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.category === '' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                All
              </button>
              <button
                v-for="cat in categories"
                :key="cat.id"
                @click="filters.category = cat.id"
                :class="[
                  'px-4 py-2 rounded-full text-sm font-medium transition-all',
                  filters.category === cat.id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                ]"
              >
                {{ cat.name }}
              </button>
            </div>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="p-5 border-t border-gray-100 bg-gray-50 flex gap-3">
          <button
            @click="clearFilters"
            class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all"
          >
            <i class="fas fa-redo mr-2"></i>Reset
          </button>
          <button
            @click="applyFilters"
            class="flex-1 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-all"
          >
            <i class="fas fa-check mr-2"></i>Apply
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { fetchWrapper } from '@/helpers';
import { useBarcodeScanner } from '@/composables/useBarcodeScanner';

const baseUrl = import.meta.env.VITE_API_URL;
const backendUrl = baseUrl.replace('/api', '');

// Barcode scanner
const { scan: scanBarcode, isScanning: isScanningBarcode, initialize: initScanner } = useBarcodeScanner();

const loading = ref(true);
const products = ref([]);
const categories = ref([]);
const searchQuery = ref('');
const viewMode = ref('grid'); // 'list' or 'grid'
const showFilterModal = ref(false);

// Get image URL for product
function getImageUrl(imagePath) {
  if (!imagePath) return null;
  // If it's already a full URL, return it
  if (imagePath.startsWith('http')) return imagePath;
  // Handle different path formats
  // Remove leading slash if present
  const cleanPath = imagePath.replace(/^\/+/, '');
  // Construct the URL from backend
  return `${backendUrl}/storage/${cleanPath}`;
}

// Handle image load error - hide broken image and show fallback
function handleImageError(event, product) {
  event.target.style.display = 'none';
}

const filters = ref({
  status: '',
  stock: '',
  category: '',
});

// Computed for filter badge
const hasActiveFilters = computed(() => {
  return filters.value.status !== '' || filters.value.stock !== '' || filters.value.category !== '';
});

const activeFilterCount = computed(() => {
  let count = 0;
  if (filters.value.status !== '') count++;
  if (filters.value.stock !== '') count++;
  if (filters.value.category !== '') count++;
  return count;
});

// Apply filters and close modal
function applyFilters() {
  showFilterModal.value = false;
  fetchProducts();
}

// Clear all filters
function clearFilters() {
  filters.value.status = '';
  filters.value.stock = '';
  filters.value.category = '';
}

// Modal states
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const isEditMode = ref(false);
const saving = ref(false);
const deleting = ref(false);
const formError = ref('');
const selectedProduct = ref(null);
const productToDelete = ref(null);

// Image handling
const imageFile = ref(null);
const imagePreview = ref(null);
const isCompressing = ref(false);

// Compress image using canvas
async function compressImage(file, maxWidth = 1200, maxHeight = 1200, quality = 0.8) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const img = new Image();
      img.onload = () => {
        // Calculate new dimensions
        let { width, height } = img;
        if (width > maxWidth || height > maxHeight) {
          const ratio = Math.min(maxWidth / width, maxHeight / height);
          width = Math.round(width * ratio);
          height = Math.round(height * ratio);
        }

        // Create canvas and draw resized image
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        // Convert to blob
        canvas.toBlob(
          (blob) => {
            if (blob) {
              // Create a new file from the blob
              const compressedFile = new File([blob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now(),
              });
              resolve(compressedFile);
            } else {
              reject(new Error('Failed to compress image'));
            }
          },
          'image/jpeg',
          quality
        );
      };
      img.onerror = () => reject(new Error('Failed to load image'));
      img.src = e.target.result;
    };
    reader.onerror = () => reject(new Error('Failed to read file'));
    reader.readAsDataURL(file);
  });
}

async function handleImageSelect(event) {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith('image/')) {
    showToast('error', 'Error', 'Please select an image file');
    return;
  }

  try {
    isCompressing.value = true;
    let processedFile = file;

    // Compress if larger than 1MB
    if (file.size > 1 * 1024 * 1024) {
      console.log(`Compressing image from ${(file.size / 1024 / 1024).toFixed(2)}MB...`);
      processedFile = await compressImage(file);
      console.log(`Compressed to ${(processedFile.size / 1024 / 1024).toFixed(2)}MB`);
    }

    // Final check - if still too large, compress more aggressively
    if (processedFile.size > 2 * 1024 * 1024) {
      processedFile = await compressImage(file, 1000, 1000, 0.6);
      console.log(`Re-compressed to ${(processedFile.size / 1024 / 1024).toFixed(2)}MB`);
    }

    imageFile.value = processedFile;
    imagePreview.value = URL.createObjectURL(processedFile);
    showToast('success', 'Image Ready', `Size: ${(processedFile.size / 1024).toFixed(0)}KB`);
  } catch (error) {
    console.error('Image processing error:', error);
    showToast('error', 'Error', 'Failed to process image');
  } finally {
    isCompressing.value = false;
    // Reset the input so the same file can be selected again
    event.target.value = '';
  }
}

function removeImage() {
  imageFile.value = null;
  imagePreview.value = null;
}

// Barcode scanning
async function handleScanBarcode() {
  const barcode = await scanBarcode();
  if (barcode) {
    form.value.barcode = barcode;
    showToast('success', 'Scanned', `Barcode: ${barcode}`);
  }
}

// Toast notification
const toast = ref({
  show: false,
  type: 'success',
  title: '',
  message: ''
});

function showToast(type, title, message) {
  toast.value = { show: true, type, title, message };
  setTimeout(() => {
    toast.value.show = false;
  }, 4000);
}

// Stepper
const currentStep = ref(0);
const steps = [
  { title: 'Basic Info', icon: 'fas fa-info-circle' },
  { title: 'Pricing', icon: 'fas fa-dollar-sign' },
  { title: 'Inventory', icon: 'fas fa-boxes' },
];

// Form data
const form = ref({
  name: '',
  sku: '',
  barcode: '',
  description: '',
  category_id: '',
  unit: 'pcs',
  cost_price: '',
  selling_price: '',
  wholesale_price: '',
  stock_quantity: '',
  reorder_level: 10,
  track_stock: true,
  track_expiry: false,
  is_active: true,
});


// Computed
const profitMargin = computed(() => {
  const cost = parseFloat(form.value.cost_price) || 0;
  const sell = parseFloat(form.value.selling_price) || 0;
  if (cost === 0) return 0;
  return ((sell - cost) / cost) * 100;
});

// Methods
async function fetchProducts() {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      search: searchQuery.value,
      ...filters.value
    });
    const response = await fetchWrapper.get(`${baseUrl}/manager/products?${params}`);
    products.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch products:', error);
  } finally {
    loading.value = false;
  }
}

async function fetchCategories() {
  try {
    const response = await fetchWrapper.get(`${baseUrl}/manager/categories`);
    categories.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch categories:', error);
  }
}

function searchProducts() {
  fetchProducts();
}

function formatMoney(amount) {
  return parseFloat(amount || 0).toFixed(2);
}

// Stepper Navigation
function nextStep() {
  if (currentStep.value < steps.length - 1) {
    currentStep.value++;
  }
}

function previousStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

function goToStep(index) {
  currentStep.value = index;
}

// Modal functions
function resetForm() {
  form.value = {
    name: '',
    sku: '',
    barcode: '',
    description: '',
    category_id: '',
    unit: 'pcs',
    cost_price: '',
    selling_price: '',
    wholesale_price: '',
    stock_quantity: '',
    reorder_level: 10,
    track_stock: true,
    track_expiry: false,
    is_active: true,
  };
  currentStep.value = 0;
  formError.value = '';
}

function openAddModal() {
  isEditMode.value = false;
  selectedProduct.value = null;
  resetForm();
  imageFile.value = null;
  imagePreview.value = null;
  showModal.value = true;
}

function openEditModal(product) {
  isEditMode.value = true;
  selectedProduct.value = product;
  form.value = {
    name: product.name,
    sku: product.sku,
    barcode: product.barcode || '',
    description: product.description || '',
    category_id: product.category_id || '',
    unit: product.unit || 'pcs',
    cost_price: product.cost_price,
    selling_price: product.price,
    wholesale_price: product.wholesale_price || '',
    stock_quantity: product.stock_quantity,
    reorder_level: product.reorder_level || 10,
    track_stock: product.track_stock !== false,
    track_expiry: product.track_expiry || false,
    is_active: product.is_active !== false,
  };
  // Set image preview if product has image
  imageFile.value = null;
  imagePreview.value = product.image ? getImageUrl(product.image) : null;
  currentStep.value = 0;
  formError.value = '';
  showModal.value = true;
}

function openViewModal(product) {
  selectedProduct.value = product;
  showViewModal.value = true;
}

function closeModal() {
  showModal.value = false;
  selectedProduct.value = null;
  formError.value = '';
  imageFile.value = null;
  imagePreview.value = null;
}

function closeViewModal() {
  showViewModal.value = false;
  selectedProduct.value = null;
}

function confirmDelete(product) {
  productToDelete.value = product;
  showDeleteModal.value = true;
}

function closeDeleteModal() {
  showDeleteModal.value = false;
  productToDelete.value = null;
}

async function saveProduct() {
  saving.value = true;
  formError.value = '';

  try {
    // Build FormData for file upload support
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('sku', form.value.sku || '');
    if (form.value.barcode) formData.append('barcode', form.value.barcode);
    if (form.value.description) formData.append('description', form.value.description);
    if (form.value.category_id) formData.append('category_id', form.value.category_id);
    formData.append('unit', form.value.unit);
    formData.append('cost_price', form.value.cost_price);
    formData.append('selling_price', form.value.selling_price);
    if (form.value.wholesale_price) formData.append('wholesale_price', form.value.wholesale_price);
    if (form.value.reorder_level) formData.append('reorder_level', form.value.reorder_level);
    formData.append('track_stock', form.value.track_stock ? '1' : '0');
    formData.append('track_expiry', form.value.track_expiry ? '1' : '0');

    // Add image if selected
    if (imageFile.value) {
      formData.append('image', imageFile.value);
    }

    if (isEditMode.value) {
      formData.append('is_active', form.value.is_active ? '1' : '0');
      formData.append('_method', 'PUT'); // Laravel method spoofing for FormData

      await fetchWrapper.postForm(`${baseUrl}/manager/products/${selectedProduct.value.id}`, formData);

      // Refresh products to get updated image
      await fetchProducts();

      closeModal();
      showToast('success', 'Product Updated', `${form.value.name} has been updated successfully.`);
    } else {
      formData.append('stock_quantity', form.value.stock_quantity);

      await fetchWrapper.postForm(`${baseUrl}/manager/products`, formData);
      await fetchProducts();

      closeModal();
      showToast('success', 'Product Created', `${form.value.name} has been added successfully.`);
    }
  } catch (error) {
    console.error('Failed to save product:', error);
    formError.value = error.message || 'Failed to save product. Please try again.';
    showToast('error', 'Error', error.message || 'Failed to save product. Please try again.');
  } finally {
    saving.value = false;
  }
}

async function deleteProduct() {
  deleting.value = true;
  const productName = productToDelete.value.name;
  try {
    await fetchWrapper.delete(`${baseUrl}/manager/products/${productToDelete.value.id}`);
    products.value = products.value.filter(p => p.id !== productToDelete.value.id);
    closeDeleteModal();
    showToast('success', 'Product Deleted', `${productName} has been deleted successfully.`);
  } catch (error) {
    console.error('Failed to delete product:', error);
    showToast('error', 'Error', 'Failed to delete product. Please try again.');
  } finally {
    deleting.value = false;
  }
}

onMounted(() => {
  fetchProducts();
  fetchCategories();
  // Pre-initialize barcode scanner for faster scanning
  initScanner();
});
</script>

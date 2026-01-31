import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { db, cacheImage } from '@/utils/indexeddb';
import { useNetworkStore } from './network.store';
import { useAuthStore } from './auth.store';
import { fetchWrapper } from '@/helpers/fetch-wrapper';

const baseUrl = import.meta.env.VITE_API_URL;
const backendUrl = import.meta.env.VITE_API_URL.replace('/api', '');

// Check if IndexedDB is available and working (Android 7 has issues)
let indexedDBAvailable = null;
async function checkIndexedDBAvailable() {
    if (indexedDBAvailable !== null) return indexedDBAvailable;
    try {
        // Test if we can open the database with a timeout
        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('IndexedDB timeout')), 3000)
        );
        await Promise.race([db.open(), timeoutPromise]);
        indexedDBAvailable = true;
    } catch (error) {
        console.warn('IndexedDB not available, using API fallback:', error.message);
        indexedDBAvailable = false;
    }
    return indexedDBAvailable;
}

export const useOfflineStore = defineStore('offline', () => {
    // State
    const productsLoaded = ref(false);
    const categoriesLoaded = ref(false);
    const lastCacheUpdate = ref(null);
    const cacheExpiryMinutes = ref(60); // 1 hour
    const optimisticStockChanges = ref({});
    const products = ref([]);
    const categories = ref([]);
    const fetchingProducts = ref(false); // Prevent duplicate fetches
    let fetchPromise = null; // Store the in-flight promise for products/categories

    // Load last cache update from localStorage
    const storedCacheTime = localStorage.getItem('lastCacheUpdate');
    if (storedCacheTime) {
        lastCacheUpdate.value = new Date(storedCacheTime);
    }

    // Getters
    const isCacheExpired = computed(() => {
        if (!lastCacheUpdate.value) return true;
        const cacheAge = Date.now() - new Date(lastCacheUpdate.value).getTime();
        return cacheAge > cacheExpiryMinutes.value * 60 * 1000;
    });

    const productsWithOptimisticStock = computed(() => {
        return products.value.map(product => {
            const stockChange = optimisticStockChanges.value[product.id] || 0;
            return {
                ...product,
                stock_quantity: (product.stock_quantity || 0) + stockChange,
                has_pending_changes: stockChange !== 0
            };
        });
    });

    // Actions
    async function cacheProductsAndCategories(data) {
        try {
            const dbAvailable = await checkIndexedDBAvailable();

            // Cache products
            if (data.products && data.products.length > 0) {
                const cachedAt = Date.now();
                const productsToCache = data.products.map(product => ({
                    ...product,
                    cached_at: cachedAt
                }));

                // Only use IndexedDB if available (Android 7 fallback)
                if (dbAvailable) {
                    try {
                        await db.products.clear();
                        await db.products.bulkAdd(productsToCache);
                    } catch (dbError) {
                        console.warn('IndexedDB products cache failed:', dbError.message);
                    }
                }
                products.value = productsToCache;
                productsLoaded.value = true;
            }

            // Cache categories
            if (data.categories && data.categories.length > 0) {
                const cachedAt = Date.now();
                const categoriesToCache = data.categories.map(category => ({
                    ...category,
                    cached_at: cachedAt
                }));

                // Only use IndexedDB if available (Android 7 fallback)
                if (dbAvailable) {
                    try {
                        await db.categories.clear();
                        await db.categories.bulkAdd(categoriesToCache);
                    } catch (dbError) {
                        console.warn('IndexedDB categories cache failed:', dbError.message);
                    }
                }
                categories.value = categoriesToCache;
                categoriesLoaded.value = true;
            }

            // Cache product images in the background (don't wait for completion)
            // Skip image caching on Android 7 (IndexedDB issues)
            if (data.products && data.products.length > 0 && dbAvailable) {
                cacheProductImages(data.products);
            }

            // Update last cache time
            lastCacheUpdate.value = new Date();
            localStorage.setItem('lastCacheUpdate', lastCacheUpdate.value.toISOString());

            return true;
        } catch (error) {
            // Even if caching fails, still update memory state
            if (data.products) {
                products.value = data.products;
                productsLoaded.value = true;
            }
            if (data.categories) {
                categories.value = data.categories;
                categoriesLoaded.value = true;
            }
            return false;
        }
    }

    async function cacheProductImages(productsList) {
        // Collect all image paths that need to be cached
        const imagePaths = productsList
            .filter(product => product.image)
            .map(product => product.image);

        if (imagePaths.length === 0) {
            return;
        }

        // Check which images are already cached
        const uncachedPaths = [];
        for (const path of imagePaths) {
            const imageUrl = path.startsWith('http') ? path : `${backendUrl}/storage/${path}`;
            const cached = await db.images.get(imageUrl);
            if (!cached) {
                uncachedPaths.push(path);
            }
        }

        if (uncachedPaths.length === 0) {
            return;
        }

        try {
            // Use batch API endpoint to get all images as base64
            const response = await fetchWrapper.post(`${baseUrl}/pos/images`, {
                paths: uncachedPaths
            });

            let cachedCount = 0;
            let failedCount = 0;

            // Store each image in IndexedDB
            for (const [path, base64] of Object.entries(response.images)) {
                const imageUrl = path.startsWith('http') ? path : `${backendUrl}/storage/${path}`;
                if (base64) {
                    await db.images.put({
                        url: imageUrl,
                        base64: base64,
                        cached_at: Date.now()
                    });
                    cachedCount++;
                } else {
                    failedCount++;
                }
            }

        } catch (error) {
            // Fallback: try direct fetch for each image
            await cacheProductImagesDirect(uncachedPaths);
        }
    }

    async function cacheProductImagesDirect(imagePaths) {
        // Fallback method: fetch images directly (may fail due to CORS in browser)
        for (const path of imagePaths) {
            const imageUrl = path.startsWith('http') ? path : `${backendUrl}/storage/${path}`;
            try {
                await cacheImage(imageUrl);
            } catch (error) {
                // Silently fail
            }
        }
    }

    async function getProducts(forceRefresh = false) {
        const networkStore = useNetworkStore();
        const authStore = useAuthStore();

        // If already fetching, return the existing promise
        if (fetchingProducts.value && fetchPromise) {
            return fetchPromise;
        }

        // If already loaded and not refreshing, return immediately
        if (productsLoaded.value && !forceRefresh && !isCacheExpired.value) {
            return productsWithOptimisticStock.value;
        }

        // Create the fetch promise
        fetchPromise = (async () => {
            fetchingProducts.value = true;

            try {
                const dbAvailable = await checkIndexedDBAvailable();
                // Only fetch from API if online AND authenticated
                const canFetchFromApi = networkStore.isOnline && authStore.token;
                // On Android 7 (no IndexedDB), always fetch from API when online
                const shouldFetch = canFetchFromApi && (isCacheExpired.value || forceRefresh || !productsLoaded.value || !dbAvailable);

                if (shouldFetch) {
                    const data = await fetchWrapper.get(`${baseUrl}/pos/pos-data`);
                    await cacheProductsAndCategories(data);
                    return productsWithOptimisticStock.value;
                }

                // Otherwise, load from cache (only if IndexedDB is available)
                if (!productsLoaded.value && dbAvailable) {
                    try {
                        const cachedProducts = await db.products.toArray();
                        if (cachedProducts.length > 0) {
                            products.value = cachedProducts;
                            productsLoaded.value = true;
                        }

                        // Also load categories from cache
                        const cachedCategories = await db.categories.toArray();
                        if (cachedCategories.length > 0) {
                            categories.value = cachedCategories;
                            categoriesLoaded.value = true;
                        }
                    } catch (dbError) {
                        console.warn('IndexedDB read failed:', dbError.message);
                    }
                }

                return productsWithOptimisticStock.value;
            } catch (error) {
                // If fetch fails, try loading from cache (only if IndexedDB available)
                const dbAvailable = await checkIndexedDBAvailable();
                if (!productsLoaded.value && dbAvailable) {
                    try {
                        const cachedProducts = await db.products.toArray();
                        if (cachedProducts.length > 0) {
                            products.value = cachedProducts;
                            productsLoaded.value = true;
                        }
                    } catch (dbError) {
                        console.warn('IndexedDB fallback read failed:', dbError.message);
                    }
                }
                return productsWithOptimisticStock.value;
            } finally {
                fetchingProducts.value = false;
                fetchPromise = null;
            }
        })();

        return fetchPromise;
    }

    async function getCategories(forceRefresh = false) {
        // If already loaded and not refreshing, return immediately
        if (categoriesLoaded.value && !forceRefresh && !isCacheExpired.value) {
            return categories.value;
        }

        // If a fetch is already in progress, wait for it (it fetches both products and categories)
        if (fetchingProducts.value && fetchPromise) {
            await fetchPromise;
            return categories.value;
        }

        // If products are being fetched or will be fetched, let getProducts handle it
        // Just load from cache if available (only if IndexedDB works)
        const dbAvailable = await checkIndexedDBAvailable();
        if (!categoriesLoaded.value && dbAvailable) {
            try {
                const cachedCategories = await db.categories.toArray();
                if (cachedCategories.length > 0) {
                    categories.value = cachedCategories;
                    categoriesLoaded.value = true;
                }
            } catch (error) {
                console.warn('IndexedDB categories read failed:', error.message);
            }
        }

        return categories.value;
    }

    function updateProductStock(productId, quantityChange) {
        // Update optimistic stock changes
        if (!optimisticStockChanges.value[productId]) {
            optimisticStockChanges.value[productId] = 0;
        }
        optimisticStockChanges.value[productId] += quantityChange;
    }

    function clearOptimisticStockChanges() {
        optimisticStockChanges.value = {};
    }

    function clearOptimisticStockForProduct(productId) {
        if (optimisticStockChanges.value[productId]) {
            delete optimisticStockChanges.value[productId];
        }
    }

    async function syncCachedData() {
        const networkStore = useNetworkStore();
        const authStore = useAuthStore();

        if (!networkStore.isOnline) {
            return false;
        }

        if (!authStore.token) {
            return false;
        }

        try {
            const data = await fetchWrapper.get(`${baseUrl}/pos/pos-data`);
            await cacheProductsAndCategories(data);
            return true;
        } catch (error) {
            return false;
        }
    }

    async function clearCache() {
        // Clear memory state first
        products.value = [];
        categories.value = [];
        productsLoaded.value = false;
        categoriesLoaded.value = false;
        lastCacheUpdate.value = null;
        localStorage.removeItem('lastCacheUpdate');

        // Try to clear IndexedDB (may fail on Android 7)
        const dbAvailable = await checkIndexedDBAvailable();
        if (dbAvailable) {
            try {
                await db.products.clear();
                await db.categories.clear();
            } catch (error) {
                console.warn('IndexedDB clear failed:', error.message);
            }
        }
    }

    return {
        // State
        productsLoaded,
        categoriesLoaded,
        lastCacheUpdate,
        cacheExpiryMinutes,
        optimisticStockChanges,
        products,
        categories,

        // Getters
        isCacheExpired,
        productsWithOptimisticStock,

        // Actions
        cacheProductsAndCategories,
        getProducts,
        getCategories,
        updateProductStock,
        clearOptimisticStockChanges,
        clearOptimisticStockForProduct,
        syncCachedData,
        clearCache
    };
});

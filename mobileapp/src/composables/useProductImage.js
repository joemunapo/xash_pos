import { ref } from 'vue';
import { useNetworkStore } from '@/stores/network.store';
import { getCachedImage, cacheImage, db } from '@/utils/indexeddb';
import { fetchWrapper, blobToBase64 } from '@/helpers/fetch-wrapper';

const backendUrl = import.meta.env.VITE_API_URL.replace('/api', '');
const baseUrl = import.meta.env.VITE_API_URL;

// Check if IndexedDB is available (Android 7 has issues)
let indexedDBAvailable = null;
async function checkIndexedDBAvailable() {
    if (indexedDBAvailable !== null) return indexedDBAvailable;
    try {
        const timeoutPromise = new Promise((_, reject) =>
            setTimeout(() => reject(new Error('IndexedDB timeout')), 3000)
        );
        await Promise.race([db.open(), timeoutPromise]);
        indexedDBAvailable = true;
    } catch (error) {
        console.warn('IndexedDB not available for images:', error.message);
        indexedDBAvailable = false;
    }
    return indexedDBAvailable;
}

// In-memory cache for Android 7 (when IndexedDB is not available)
const memoryCache = new Map();

/**
 * Composable for handling product images with OFFLINE-FIRST approach
 * Images are always served from cache (base64). When online and not cached,
 * the image is fetched, cached, and then the base64 is returned.
 * On Android 7 (IndexedDB issues), uses in-memory cache instead.
 */
export function useProductImage() {
    const networkStore = useNetworkStore();

    /**
     * Get image URL - OFFLINE FIRST
     * Always returns base64 from cache if available.
     * If not cached and online, fetches and caches the image first.
     * @param {string} imagePath - The image path from the product
     * @returns {Promise<string|null>} - Base64 data URL or null
     */
    async function getProductImageUrl(imagePath) {
        if (!imagePath) return null;

        // Construct the full URL (for cache lookup key)
        const fullUrl = imagePath.startsWith('http')
            ? imagePath
            : `${backendUrl}/storage/${imagePath}`;

        // Check if IndexedDB is available
        const dbAvailable = await checkIndexedDBAvailable();

        // On Android 7 (no IndexedDB), use memory cache
        if (!dbAvailable) {
            // Check memory cache first
            if (memoryCache.has(fullUrl)) {
                return memoryCache.get(fullUrl);
            }

            // If offline, return null
            if (!networkStore.isOnline) {
                return null;
            }

            // Fetch via API (not direct URL to avoid CORS)
            try {
                const base64 = await fetchImageViaApi(imagePath);
                if (base64) {
                    // Store in memory cache (limited to 50 images)
                    if (memoryCache.size >= 50) {
                        const firstKey = memoryCache.keys().next().value;
                        memoryCache.delete(firstKey);
                    }
                    memoryCache.set(fullUrl, base64);
                    return base64;
                }
            } catch (error) {
                console.warn('Failed to fetch image via API:', error.message);
            }
            return null;
        }

        // IndexedDB available - use normal caching
        // ALWAYS try cache first (offline-first approach)
        try {
            const cachedImage = await getCachedImage(fullUrl);
            if (cachedImage) {
                return cachedImage;
            }
        } catch (error) {
            // Cache read failed, continue to fetch
        }

        // Not in cache - if offline, return null
        if (!networkStore.isOnline) {
            return null;
        }

        // Online and not cached - fetch via API and cache it
        try {
            const base64 = await fetchAndCacheImage(imagePath);
            return base64;
        } catch (error) {
            return null;
        }
    }

    /**
     * Fetch image via API endpoint (no caching)
     * Uses the /api/pos/image endpoint to avoid CORS issues
     */
    async function fetchImageViaApi(imagePath) {
        try {
            const response = await fetchWrapper.get(`${baseUrl}/pos/image?path=${encodeURIComponent(imagePath)}`);
            if (response && response.base64) {
                return response.base64;
            }
            return null;
        } catch (error) {
            console.warn('Failed to fetch image:', error.message || error);
            return null;
        }
    }

    /**
     * Fetch image via API endpoint and cache it
     * Uses the /api/pos/image endpoint to avoid CORS issues
     */
    async function fetchAndCacheImage(imagePath) {
        try {
            // Use the API endpoint to get base64 image
            const response = await fetchWrapper.get(`${baseUrl}/pos/image?path=${encodeURIComponent(imagePath)}`);

            if (response && response.base64) {
                const fullUrl = imagePath.startsWith('http')
                    ? imagePath
                    : `${backendUrl}/storage/${imagePath}`;

                // Try to cache the image
                const dbAvailable = await checkIndexedDBAvailable();
                if (dbAvailable) {
                    try {
                        if (!db.isOpen()) {
                            await db.open();
                        }
                        await db.images.put({
                            url: fullUrl,
                            base64: response.base64,
                            cached_at: Date.now()
                        });
                    } catch (dbError) {
                        console.warn('Failed to cache image:', dbError.message);
                    }
                }

                return response.base64;
            }

            return null;
        } catch (error) {
            return null;
        }
    }

    /**
     * Preload and cache an image (call this when online to ensure image is cached)
     */
    async function preloadImage(imagePath) {
        if (!imagePath || !networkStore.isOnline) return false;

        const fullUrl = imagePath.startsWith('http')
            ? imagePath
            : `${backendUrl}/storage/${imagePath}`;

        // Check if already cached
        const dbAvailable = await checkIndexedDBAvailable();

        if (dbAvailable) {
            const cached = await getCachedImage(fullUrl);
            if (cached) return true;
        } else {
            if (memoryCache.has(fullUrl)) return true;
        }

        // Fetch and cache
        const base64 = await fetchAndCacheImage(imagePath);
        return !!base64;
    }

    return {
        getProductImageUrl,
        fetchAndCacheImage,
        preloadImage
    };
}

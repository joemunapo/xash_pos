import Dexie from 'dexie';
import { blobToBase64 } from '@/helpers/fetch-wrapper.js';

// Initialize Dexie database
export const db = new Dexie('xashpos_offline');

// Define database schema
db.version(1).stores({
    // Products cache
    products: 'id, sku, barcode, category_id, name, cached_at',

    // Categories cache
    categories: 'id, name, cached_at',

    // Pending sales queue (offline sales awaiting sync)
    pending_sales: 'temp_id, created_at, attempts',

    // Synced sales (today's successfully synced sales)
    synced_sales: 'sale_id, receipt_number, synced_at',

    // Sync log (audit trail)
    sync_log: '++id, action, timestamp',

    // Cached product images
    images: 'url, cached_at'
});

/**
 * Initialize database
 */
export async function initializeDB() {
    try {
        await db.open();
        return true;
    } catch (error) {
        return false;
    }
}

/**
 * Clear all cached data
 */
export async function clearCache() {
    try {
        await db.products.clear();
        await db.categories.clear();
        console.log('Cache cleared successfully');
    } catch (error) {
        console.error('Failed to clear cache:', error);
    }
}

/**
 * Clear old synced sales (keep only today's)
 */
export async function clearOldSyncedSales() {
    try {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayTimestamp = today.getTime();

        await db.synced_sales
            .where('synced_at')
            .below(todayTimestamp)
            .delete();

        console.log('Old synced sales cleared');
    } catch (error) {
        console.error('Failed to clear old synced sales:', error);
    }
}

/**
 * Get database stats
 */
export async function getDBStats() {
    try {
        const stats = {
            products: await db.products.count(),
            categories: await db.categories.count(),
            pending_sales: await db.pending_sales.count(),
            synced_sales: await db.synced_sales.count(),
            sync_log: await db.sync_log.count(),
            images: await db.images.count()
        };
        return stats;
    } catch (error) {
        console.error('Failed to get DB stats:', error);
        return null;
    }
}

/**
 * Cache an image from URL as base64
 */
export async function cacheImage(url) {
    if (!url) return null;

    try {
        // Check if already cached
        const cached = await db.images.get(url);
        if (cached) {
            return cached.base64;
        }

        // Download image (no CORS needed when using img.src)
        const base64 = await fetchImageAsBase64(url);

        if (base64) {
            // Store in IndexedDB as base64
            await db.images.put({
                url: url,
                base64: base64,
                cached_at: Date.now()
            });
            return base64;
        }

        return null;
    } catch (error) {
        console.warn(`Failed to cache image ${url}:`, error.message);
        return null;
    }
}

/**
 * Fetch image as base64 using fetch API with proper CORS handling
 */
async function fetchImageAsBase64(url) {
    try {
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Accept': 'image/*'
            }
        });

        if (!response.ok) {
            console.warn(`Failed to fetch image: ${response.status}`);
            return null;
        }

        const blob = await response.blob();
        const base64 = await blobToBase64(blob);
        return base64;
    } catch (error) {
        console.warn(`Failed to fetch image ${url}:`, error.message);
        return null;
    }
}

/**
 * Get cached image (returns base64 string)
 */
export async function getCachedImage(url) {
    if (!url) return null;

    try {
        // Ensure database is open
        if (!db.isOpen()) {
            await db.open();
        }

        const cached = await db.images.get(url);
        if (cached && cached.base64) {
            return cached.base64;
        }
        return null;
    } catch (error) {
        console.error(`Failed to get cached image ${url}:`, error);
        return null;
    }
}

/**
 * Clear old cached images (older than 7 days)
 */
export async function clearOldImages() {
    try {
        const sevenDaysAgo = Date.now() - (7 * 24 * 60 * 60 * 1000);
        await db.images
            .where('cached_at')
            .below(sevenDaysAgo)
            .delete();
        console.log('Old cached images cleared');
    } catch (error) {
        console.error('Failed to clear old images:', error);
    }
}

export default db;

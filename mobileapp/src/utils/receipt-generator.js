/**
 * Generate a unique temporary receipt number for offline sales
 * Format: TEMP-{timestamp}-{random}
 * Example: TEMP-1704123456789-A3F9
 *
 * @returns {string} Temporary receipt number
 */
export function generateTempReceipt() {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substring(2, 6).toUpperCase();
    return `TEMP-${timestamp}-${random}`;
}

/**
 * Check if a receipt number is a temporary receipt
 *
 * @param {string} receiptNumber - Receipt number to check
 * @returns {boolean} True if temporary receipt, false otherwise
 */
export function isTempReceipt(receiptNumber) {
    if (!receiptNumber) return false;
    return receiptNumber.toString().startsWith('TEMP-');
}

/**
 * Extract timestamp from temporary receipt
 *
 * @param {string} tempReceipt - Temporary receipt number
 * @returns {number|null} Timestamp or null if invalid
 */
export function extractTimestamp(tempReceipt) {
    if (!isTempReceipt(tempReceipt)) return null;

    const parts = tempReceipt.split('-');
    if (parts.length < 2) return null;

    const timestamp = parseInt(parts[1]);
    return isNaN(timestamp) ? null : timestamp;
}

/**
 * Get human-readable date from temporary receipt
 *
 * @param {string} tempReceipt - Temporary receipt number
 * @returns {Date|null} Date object or null if invalid
 */
export function getTempReceiptDate(tempReceipt) {
    const timestamp = extractTimestamp(tempReceipt);
    return timestamp ? new Date(timestamp) : null;
}

export default {
    generateTempReceipt,
    isTempReceipt,
    extractTimestamp,
    getTempReceiptDate
};

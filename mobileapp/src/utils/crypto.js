/**
 * Simple hashing utility using Browser Crypto API for secure PIN storage offline
 */

/**
 * Hash a PIN using SHA-256
 * @param {string} pin - The PIN to hash
 * @returns {Promise<string>} - The hex representation of the hash
 */
export async function hashPin(pin) {
    if (!pin) return '';

    try {
        const msgUint8 = new TextEncoder().encode(pin); // encode as (utf-8) Uint8Array
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgUint8); // hash the message
        const hashArray = Array.from(new Uint8Array(hashBuffer)); // convert buffer to byte array
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join(''); // convert bytes to hex string
        return hashHex;
    } catch (error) {
        console.error('Hashing failed:', error);
        // Fallback for environments where crypto.subtle is not available
        return btoa(pin).split('').reverse().join('');
    }
}

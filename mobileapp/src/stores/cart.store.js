import { defineStore } from 'pinia';
import { generateTempReceipt } from '@/utils/receipt-generator';
import { useSyncStore } from './sync.store';
import { useOfflineStore } from './offline.store';
import { useAuthStore } from './auth.store';

export const useCartStore = defineStore({
    id: 'cart',
    state: () => ({
        items: [],
        customer: null,
        discountAmount: 0,
        notes: '',
    }),
    getters: {
        itemCount: (state) => state.items.length,

        totalQuantity: (state) => {
            return state.items.reduce((sum, item) => sum + item.quantity, 0);
        },

        subtotal: (state) => {
            const total = state.items.reduce((sum, item) => {
                return sum + (item.unit_price * item.quantity) - (item.discount_amount || 0);
            }, 0);
            return Math.round(total * 100) / 100; // Round to 2 decimal places
        },

        taxAmount: (state) => {
            // Tax disabled
            return 0;
        },

        totalAmount(state) {
            const total = this.subtotal - state.discountAmount;
            return Math.round(total * 100) / 100; // Round to 2 decimal places
        },

        isEmpty: (state) => state.items.length === 0,
    },
    actions: {
        addItem(product, quantity = 1) {
            // Check if the same product with the same unit already exists
            const existingIndex = this.items.findIndex(
                item => item.product_id === product.id &&
                        item.variant_id === (product.variant_id || null) &&
                        item.unit === product.unit
            );

            if (existingIndex !== -1) {
                // Update existing item quantity
                this.items[existingIndex].quantity += quantity;
            } else {
                // Add new item
                this.items.push({
                    product_id: product.id,
                    variant_id: product.variant_id || null,
                    name: product.name,
                    sku: product.sku,
                    unit: product.unit,
                    unit_name: product.unit_name || product.unit,
                    unit_multiplier: product.unit_multiplier || 1,
                    unit_price: Math.round(parseFloat(product.branch_price || product.selling_price) * 100) / 100,
                    cost_price: Math.round(parseFloat(product.cost_price || 0) * 100) / 100,
                    quantity: quantity,
                    discount_amount: 0,
                    is_taxable: product.is_taxable,
                    tax_rate: Math.round(parseFloat(product.tax_rate || 0) * 100) / 100,
                    image: product.image,
                    allow_decimal_qty: product.allow_decimal_qty,
                    stock_quantity: product.stock_quantity || 0,
                });
            }
        },

        updateQuantity(index, quantity) {
            if (quantity <= 0) {
                this.removeItem(index);
            } else {
                this.items[index].quantity = quantity;
            }
        },

        incrementQuantity(index) {
            this.items[index].quantity += 1;
        },

        decrementQuantity(index) {
            if (this.items[index].quantity > 1) {
                this.items[index].quantity -= 1;
            } else {
                this.removeItem(index);
            }
        },

        setItemDiscount(index, discount) {
            this.items[index].discount_amount = Math.round(parseFloat(discount || 0) * 100) / 100;
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        setCustomer(customer) {
            this.customer = customer;
        },

        setDiscount(amount) {
            this.discountAmount = Math.round(parseFloat(amount || 0) * 100) / 100;
        },

        setNotes(notes) {
            this.notes = notes;
        },

        clearCart() {
            this.items = [];
            this.customer = null;
            this.discountAmount = 0;
            this.notes = '';
        },

        // Prepare items for API submission
        getItemsForSubmission() {
            return this.items.map(item => ({
                product_id: item.product_id,
                variant_id: item.variant_id,
                quantity: parseFloat(item.quantity * (item.unit_multiplier || 1)) || 1, // Convert to base units
                unit_price: parseFloat(item.unit_price) || 0,
                unit: item.unit,
                discount_amount: parseFloat(item.discount_amount) || 0,
            }));
        },

        // Complete sale offline (without API call)
        async completeSaleOffline(paymentMethod, amountReceived) {
            const syncStore = useSyncStore();
            const offlineStore = useOfflineStore();
            const authStore = useAuthStore();

            const tempReceipt = generateTempReceipt();
            const paid = parseFloat(amountReceived) || 0;
            const total = parseFloat(this.totalAmount) || 0;
            const changeAmount = paid - total;

            const saleData = {
                temp_receipt_number: tempReceipt,
                items: this.getItemsForSubmission(),
                payment_method: paymentMethod || 'cash',
                amount_paid: paid,
                subtotal: parseFloat(this.subtotal) || 0,
                tax_amount: 0,
                discount_amount: parseFloat(this.discountAmount) || 0,
                total_amount: total,
                change_amount: changeAmount,
                customer_id: this.customer?.id || null,
                notes: this.notes || null,
                created_at: new Date().toISOString(),
                status: 'pending_sync',
                user_id: authStore.user?.id || null,
                branch_id: authStore.user?.primary_branch_id || null,
            };

            // Add to sync queue
            await syncStore.addPendingSale(saleData);

            // Update optimistic stock for each item
            this.items.forEach(item => {
                const quantityChange = -(item.quantity * (item.unit_multiplier || 1));
                offlineStore.updateProductStock(item.product_id, quantityChange);
            });

            console.log(`Offline sale created: ${tempReceipt}`);

            return {
                receipt_number: tempReceipt,
                change_amount: changeAmount,
                offline: true
            };
        },
    },
    // Persist cart in localStorage
    persist: {
        key: 'pos-cart',
        storage: localStorage,
    },
});

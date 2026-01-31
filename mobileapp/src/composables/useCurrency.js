import { ref, computed } from 'vue';

const currentCurrency = ref(localStorage.getItem('selectedCurrency') || 'USD');

// Exchange rates (should ideally come from API)
const exchangeRates = ref({
    USD: { ZIG: 13.50, ZAR: 18.50 },
    ZIG: { USD: 0.074074, ZAR: 1.37 },
    ZAR: { USD: 0.054054, ZIG: 0.729927 }
});

export function useCurrency() {
    const setCurrency = (currency) => {
        currentCurrency.value = currency;
        localStorage.setItem('selectedCurrency', currency);
    };

    const convert = (amount, fromCurrency, toCurrency = currentCurrency.value) => {
        if (fromCurrency === toCurrency) return amount;

        const rate = exchangeRates.value[fromCurrency]?.[toCurrency];
        if (!rate) return amount;

        return amount * rate;
    };

    const formatCurrency = (amount, currency = currentCurrency.value) => {
        const symbols = {
            USD: '$',
            ZIG: 'ZG',
            ZAR: 'R'
        };

        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);

        return `${symbols[currency] || currency} ${formatted}`;
    };

    const getCurrencySymbol = (currency = currentCurrency.value) => {
        const symbols = {
            USD: '$',
            ZIG: 'ZG',
            ZAR: 'R'
        };
        return symbols[currency] || currency;
    };

    return {
        currentCurrency: computed(() => currentCurrency.value),
        setCurrency,
        convert,
        formatCurrency,
        getCurrencySymbol,
        exchangeRates: computed(() => exchangeRates.value)
    };
}

<template>
    <div class="flex items-center gap-2">
        <div v-if="!networkStore.isOnline"
             class="flex items-center gap-1.5 px-2.5 py-1 bg-yellow-500 text-white text-xs font-medium rounded-lg">
            <i class="fas fa-wifi-slash"></i>
            <span>Offline</span>
        </div>
        <div v-else-if="networkStore.connectionType === 'cellular'"
             class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-500 text-white text-xs font-medium rounded-lg">
            <i class="fas fa-signal"></i>
            <span>Cellular</span>
        </div>
    </div>
</template>

<script setup>
import { useNetworkStore } from '@/stores/network.store';

const networkStore = useNetworkStore();
</script>

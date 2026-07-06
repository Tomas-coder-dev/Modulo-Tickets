<script setup>
defineProps({
    modelValue: { type: String, default: '' },
});
defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex justify-end mb-6">
        <div class="relative w-full sm:w-64">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
            <input
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                type="text"
                placeholder="Buscar tickets..."
                class="pl-9 pr-3 py-2 text-sm rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 w-full"
            />
        </div>
    </div>
</template>
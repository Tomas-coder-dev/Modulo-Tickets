<script setup>
import TicketCard from './TicketCard.vue';

defineProps({
    label: { type: String, required: true },
    status: { type: String, required: true },
    tickets: { type: Array, default: () => [] },
    pillClass: { type: String, default: 'bg-gray-100 text-gray-700' },
    badgeClass: { type: String, default: 'bg-gray-500' },
});
defineEmits(['open-comments', 'open-seguimiento']);
</script>

<template>
    <div class="flex-shrink-0 w-[80vw] xs:w-72 sm:w-64 xl:w-auto snap-center flex flex-col min-w-0">
        <div class="flex items-center mb-3">
            <span class="text-sm font-bold px-3 py-1.5 rounded-full flex items-center gap-2" :class="pillClass">
                {{ label }}
                <span class="w-5 h-5 rounded-full text-white text-[11px] font-bold flex items-center justify-center" :class="badgeClass">
                    {{ tickets.length }}
                </span>
            </span>
        </div>

        <div class="space-y-2 max-h-[70vh] overflow-y-auto pr-1">
            <TicketCard
                v-for="t in tickets"
                :key="t.id"
                :ticket="t"
                @open-comments="$emit('open-comments', $event)"
                @open-seguimiento="$emit('open-seguimiento', $event)"
            />

            <p v-if="!tickets.length" class="text-xs text-gray-400 text-center py-6 bg-white/60 rounded-lg border border-dashed border-gray-200">
                Sin tickets
            </p>
        </div>
    </div>
</template>
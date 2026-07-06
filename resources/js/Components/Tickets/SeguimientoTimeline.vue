<script setup>
import { computed } from 'vue';

const props = defineProps({ 
    ticket: { type: Object, required: true } 
});

const STATUS_LABELS = { 
    pendiente: 'Pendiente', 
    asignado_ti: 'Asignado a TI', 
    en_curso: 'En curso', 
    levantado: 'Levantado', 
    testing: 'Testing (Conforme)'
};

const formatDate = (d) => d ? new Date(d).toLocaleString('es-PE', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) : '—';

const movements = computed(() =>
    (props.ticket.status_histories || []).filter(h => h.type === 'status_change')
);
</script>

<template>
    <div class="space-y-3">
        <h4 class="text-sm font-bold text-gray-700">Historial</h4>
        
        <div v-if="!movements.length" class="text-sm text-gray-400">
            Sin movimientos registrados aún.
        </div>

        <div v-for="h in movements" :key="h.id" class="border-l-2 border-gray-200 pl-4 pb-3 relative">
            <span class="absolute -left-[5px] top-1 w-2 h-2 rounded-full bg-[#2563EB]" />
            <p class="text-sm font-semibold text-gray-800">{{ STATUS_LABELS[h.status] || h.status }}</p>
            <p v-if="h.comment" class="text-xs text-gray-500">{{ h.comment }}</p>
            <p class="text-[11px] text-gray-400">{{ formatDate(h.created_at) }}</p>
        </div>
    </div>
</template>
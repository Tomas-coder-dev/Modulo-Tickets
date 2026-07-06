<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import UserAvatar from '@/Components/UserAvatar.vue';

const props = defineProps({
    ticket: { type: Object, required: true },
});

defineEmits(['open-comments', 'open-seguimiento']);

const activeTab = ref('comentarios');
const localComment = ref('');
const isSubmitting = ref(false);

const PRIORITY = {
    1: { text: 'Crítico', badge: 'bg-red-100 text-red-700' },
    2: { text: 'Alto', badge: 'bg-orange-100 text-orange-700' },
    3: { text: 'Medio', badge: 'bg-green-100 text-green-700' },
};

const STATUS_LABELS = {
    pendiente: 'Pendiente',
    asignado_ti: 'Asignado a TI',
    en_curso: 'En curso',
    levantado: 'Levantado',
    testing: 'Testing (Conforme)',
};

const priority = computed(() => PRIORITY[props.ticket.priority] ?? null);

function formatName(user) {
    if (!user) return null;
    const name = user.name || '';
    const lastName = user.last_name || '';
    return lastName ? `${name} ${lastName.charAt(0)}.` : name;
}

function formatDate(dateString) {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('es-PE', { day: '2-digit', month: 'short' });
}

const creatorName = computed(() => formatName(props.ticket.user) || 'Desconocido');
const assignedName = computed(() => formatName(props.ticket.assigned_to));

function findHistoryDate(status) {
    if (!props.ticket.status_histories) return null;
    const found = [...props.ticket.status_histories].reverse().find(h => h.status === status);
    return found ? found.created_at : null;
}

const footerInfo = computed(() => {
    const s = props.ticket.status;
    if (s === 'asignado_ti') {
        return { label: 'Asignado', date: findHistoryDate('asignado_ti') ?? props.ticket.created_at, user: props.ticket.assigned_to };
    }
    if (s === 'en_curso') {
        return { label: 'En curso desde', date: findHistoryDate('en_curso'), user: props.ticket.assigned_to };
    }
    if (s === 'levantado') {
        return { label: 'Resuelto', date: findHistoryDate('levantado'), user: props.ticket.assigned_to };
    }
    if (s === 'testing') {
        return { label: 'Validado por', date: findHistoryDate('testing'), user: props.ticket.user };
    }
    return { label: 'Creado', date: props.ticket.created_at, user: props.ticket.user };
});

const latestComments = computed(() => {
    if (!props.ticket.status_histories) return [];
    return props.ticket.status_histories
        .filter(h => h.comment && h.comment.trim() !== '' && h.comment !== 'Ticket registrado')
        .slice(-2);
});

const latestHistory = computed(() => {
    if (!props.ticket.status_histories) return [];
    return props.ticket.status_histories.slice(-2);
});

function sendInlineComment() {
    if (!localComment.value.trim() || isSubmitting.value) return;
    isSubmitting.value = true;
    router.post(`/tickets/${props.ticket.id}/comments`, {
        comment: localComment.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { localComment.value = ''; },
        onFinish: () => { isSubmitting.value = false; },
    });
}
</script>

<template>
    <div class="w-full text-left bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col gap-3">

        <div class="flex items-start justify-between gap-2">
            <span class="text-xs font-bold text-blue-600">#TK-{{ String(ticket.id).padStart(4, '0') }}</span>
            <span v-if="priority" class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" :class="priority.badge">
                {{ priority.text }}
            </span>
        </div>

        <div>
            <h3 class="text-sm font-extrabold text-gray-900 leading-snug line-clamp-2">
                {{ ticket.description }}
            </h3>
            <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] text-gray-500 font-medium mt-1">
                <span>{{ ticket.issue_type }}</span>
                <span>·</span>
                <span>{{ ticket.module }}</span>
            </div>
        </div>

        <div v-if="ticket.assigned_to" class="flex items-center gap-2.5">
            <UserAvatar
                :user="ticket.assigned_to"
                size="w-8 h-8"
                bg-class="bg-blue-600"
                text-class="text-white"
            />
            <div class="min-w-0">
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide leading-none mb-0.5">Usuario de soporte</p>
                <p class="text-xs font-bold text-gray-800 truncate">{{ assignedName }}</p>
            </div>
        </div>
        <p v-else class="text-[11px] text-gray-400 italic">Sin asignar</p>

        <div v-if="ticket.due_date" class="flex items-center gap-1.5 text-[11px] text-gray-500 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Vence: {{ formatDate(ticket.due_date) }}
        </div>

        <div class="grid grid-cols-2 gap-2 mt-1">
            <button
                type="button"
                @click.stop="activeTab = 'comentarios'"
                class="text-[11px] font-bold py-1.5 rounded-lg transition-all text-center border"
                :class="activeTab === 'comentarios' ? 'bg-green-600 border-green-600 text-white shadow-sm' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
            >
                Comentarios
            </button>
            <button
                type="button"
                @click.stop="activeTab = 'seguimiento'"
                class="text-[11px] font-bold py-1.5 rounded-lg transition-all text-center border"
                :class="activeTab === 'seguimiento' ? 'bg-gray-600 border-gray-600 text-white shadow-sm' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
            >
                Seguimiento
            </button>
        </div>

        <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-100 mt-1 flex-1 flex flex-col justify-between min-h-[110px]">

            <div v-if="activeTab === 'comentarios'" class="space-y-2 h-full flex flex-col">
                <button
                    type="button"
                    @click.stop="$emit('open-comments', ticket.id)"
                    class="text-[11px] text-blue-600 hover:text-blue-800 hover:underline font-semibold text-left"
                >
                    Ver todos ({{ ticket.comments_count ?? 0 }})
                </button>
                <div class="space-y-1.5 flex-1">
                    <p v-if="!latestComments.length" class="text-[11px] text-gray-400 italic text-center py-2">Sin comentarios.</p>
                    <p v-for="c in latestComments" :key="c.id" class="text-[11px] text-gray-700 bg-white px-2 py-1.5 rounded border border-gray-100 shadow-sm line-clamp-1">
                        {{ c.comment }}
                    </p>
                </div>
                <div
                    class="flex gap-1 items-center border border-gray-300 rounded-lg bg-white px-2 py-1 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition-all shadow-sm mt-2"
                    @click.stop
                >
                    <input
                        v-model="localComment"
                        type="text"
                        placeholder="Escribe un comentario..."
                        class="w-full text-[11px] border-0 p-1 focus:ring-0 text-gray-700 placeholder-gray-400"
                        @keydown.enter.prevent="sendInlineComment"
                        :disabled="isSubmitting"
                    />
                    <button
                        type="button"
                        @click.stop="sendInlineComment"
                        :disabled="!localComment.trim() || isSubmitting"
                        class="text-blue-600 hover:text-blue-800 disabled:opacity-30 transition-opacity p-1"
                    >
                        <svg class="w-4 h-4 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    </button>
                </div>
            </div>

            <div v-else class="space-y-2 h-full flex flex-col">
                <button
                    type="button"
                    @click.stop="$emit('open-seguimiento', ticket.id)"
                    class="text-[11px] text-blue-600 hover:text-blue-800 hover:underline font-semibold text-left"
                >
                    Ver todo el seguimiento
                </button>
                <div class="space-y-2 flex-1 pt-1 text-[10px] text-gray-600">
                    <p v-if="!latestHistory.length" class="text-gray-400 italic text-center py-2">Sin movimientos.</p>
                    <div v-for="h in latestHistory" :key="h.id" class="flex gap-2 items-start">
                        <div class="w-1.5 h-1.5 rounded-full bg-gray-400 mt-1 shrink-0"></div>
                        <p class="leading-tight">
                            <span class="font-bold text-gray-800">{{ STATUS_LABELS[h.status] || h.status }}:</span> {{ h.comment || 'Cambio de estado' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
            <UserAvatar
                :user="footerInfo.user"
                size="w-6 h-6"
                text-size="text-[9px]"
                bg-class="bg-gray-200"
                text-class="text-gray-600"
            />
            <p class="text-[10px] text-gray-400 font-medium">
                {{ footerInfo.label }} el {{ formatDate(footerInfo.date) }}
                <span v-if="footerInfo.user" class="text-gray-500"> · {{ formatName(footerInfo.user) }}</span>
            </p>
        </div>

    </div>
</template>
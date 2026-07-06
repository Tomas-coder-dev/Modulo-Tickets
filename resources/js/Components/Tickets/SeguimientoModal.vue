<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import SeguimientoTimeline from './SeguimientoTimeline.vue';
import UserAvatar from '@/Components/UserAvatar.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    ticketId: { type: Number, default: null },
    tiUsers: { type: Array, default: () => [] },
    readOnly: { type: Boolean, default: false },
});
const emit = defineEmits(['close', 'updated']);

const canManage = computed(() =>
    ['admin', 'ti'].includes(usePage().props.auth.user?.role)
);

const ticket = ref(null);
const isLoading = ref(false);
const loadError = ref(null);
const comment = ref('');
const isSubmitting = ref(false);
const assignForm = ref({ assigned_to: '', priority: '2', due_date: '' });

const IMAGE_EXT = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
const VIDEO_EXT = ['mp4', 'webm', 'ogg'];

const STATUS_LABELS = {
    pendiente: 'Pendiente',
    asignado_ti: 'Asignado a TI',
    en_curso: 'En curso',
    levantado: 'Levantado',
    testing: 'Resuelto',
};

const STATUS_PILL = {
    pendiente: 'bg-orange-100 text-orange-700',
    asignado_ti: 'bg-blue-100 text-blue-700',
    en_curso: 'bg-purple-100 text-purple-700',
    levantado: 'bg-red-100 text-red-700',
    testing: 'bg-green-100 text-green-700',
};

const statusLabel = computed(() => STATUS_LABELS[ticket.value?.status] ?? ticket.value?.status);
const statusPill = computed(() => STATUS_PILL[ticket.value?.status] ?? 'bg-gray-100 text-gray-700');

const attachmentUrl = computed(() => ticket.value?.attachment ? `/storage/${ticket.value.attachment}` : null);

const attachmentExt = computed(() => {
    if (!ticket.value?.attachment) return null;
    const parts = ticket.value.attachment.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : null;
});

const attachmentType = computed(() => {
    if (!attachmentExt.value) return null;
    if (IMAGE_EXT.includes(attachmentExt.value)) return 'image';
    if (VIDEO_EXT.includes(attachmentExt.value)) return 'video';
    if (attachmentExt.value === 'pdf') return 'pdf';
    return 'other';
});

async function load() {
    if (!props.ticketId) return;
    isLoading.value = true;
    loadError.value = null;
    try {
        const { data } = await axios.get(`/tickets/${props.ticketId}`, {
            headers: { Accept: 'application/json' },
        });
        ticket.value = data.ticket;
    } catch (error) {
        loadError.value = error.response?.status
            ? `Error ${error.response.status}: ${error.response.data?.message ?? 'no se pudo cargar el ticket'}`
            : 'No se pudo conectar con el servidor';
    } finally {
        isLoading.value = false;
    }
}

watch(() => [props.show, props.ticketId], ([show]) => {
    if (show) load();
    else { ticket.value = null; comment.value = ''; loadError.value = null; }
});

function nextStatusFor(status) {
    return { pendiente: null, asignado_ti: 'en_curso', en_curso: 'levantado', levantado: 'testing' }[status] ?? null;
}

function assign() {
    isSubmitting.value = true;
    router.post(`/admin/tickets/${props.ticketId}/assign`, {
        ...assignForm.value,
        comment: comment.value,
    }, {
        onSuccess: () => { load(); emit('updated'); comment.value = ''; },
        onFinish: () => { isSubmitting.value = false; },
    });
}

function advance() {
    const next = nextStatusFor(ticket.value.status);
    if (!next) return;
    isSubmitting.value = true;
    router.post(`/admin/tickets/${props.ticketId}/status`, {
        status: next,
        comment: comment.value,
    }, {
        onSuccess: () => { load(); emit('updated'); comment.value = ''; },
        onFinish: () => { isSubmitting.value = false; },
    });
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-gray-900/60 backdrop-blur-sm p-0 sm:p-4"
                @click.self="$emit('close')"
            >
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95"
                >
                    <div
                        v-if="show"
                        class="bg-white w-full sm:max-w-2xl max-h-[92vh] sm:max-h-[85vh] rounded-t-3xl sm:rounded-2xl shadow-2xl flex flex-col overflow-hidden"
                    >
                        <div class="shrink-0 bg-gradient-to-r from-[#2563EB] to-[#1d4ed8] px-5 sm:px-7 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-white font-bold text-base sm:text-lg leading-tight truncate">
                                        Ticket {{ ticket ? `#TK-${String(ticket.id).padStart(4, '0')}` : '' }}
                                    </h2>
                                    <span v-if="ticket" class="text-[11px] font-semibold px-2 py-0.5 rounded-full inline-block mt-1" :class="statusPill">
                                        {{ statusLabel }}
                                    </span>
                                </div>
                            </div>
                            <button
                                @click="$emit('close')"
                                class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto">
                            <div v-if="isLoading" class="p-10 flex flex-col items-center justify-center gap-3 text-gray-400">
                                <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                                <p class="text-sm font-medium">Cargando ticket...</p>
                            </div>

                            <div v-else-if="loadError" class="p-10 flex flex-col items-center justify-center gap-3 text-center">
                                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-600 font-semibold">{{ loadError }}</p>
                                <button @click="load" class="text-sm font-semibold text-blue-600 hover:text-blue-700 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors">
                                    Reintentar
                                </button>
                            </div>

                            <div v-else-if="ticket" class="p-5 sm:p-7 space-y-6">

                                <div class="bg-gray-50 rounded-2xl p-4 sm:p-5 border border-gray-100">
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-gray-500 mb-2">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            {{ ticket.issue_type }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            {{ ticket.module }}
                                        </span>
                                    </div>
                                    <p class="text-gray-800 text-sm sm:text-base leading-relaxed">{{ ticket.description }}</p>
                                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-200">
                                        <UserAvatar
                                            :user="ticket.user"
                                            size="w-6 h-6"
                                            text-size="text-[10px]"
                                            bg-class="bg-gray-200"
                                            text-class="text-gray-600"
                                        />
                                        <p class="text-xs text-gray-500">
                                            Reportado por <span class="font-semibold text-gray-700">{{ ticket.user?.name }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div v-if="attachmentUrl" class="space-y-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Evidencia adjunta</span>

                                    <div v-if="attachmentType === 'other'" class="flex items-center justify-between gap-3 bg-blue-50 rounded-xl p-3.5 border border-blue-100">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                                <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </div>
                                            <span class="text-sm font-medium text-blue-700 truncate">Archivo adjunto</span>
                                        </div>
                                        <a :href="attachmentUrl" target="_blank" class="shrink-0 text-xs font-bold text-blue-600 hover:text-blue-700 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                                            Ver
                                        </a>
                                    </div>

                                    <template v-else>
                                        <img v-if="attachmentType === 'image'" :src="attachmentUrl" alt="Evidencia del ticket" class="max-h-80 w-full object-contain rounded-xl border border-gray-100 bg-gray-50" />
                                        <video v-else-if="attachmentType === 'video'" :src="attachmentUrl" controls class="max-h-80 w-full rounded-xl border border-gray-100 bg-black" />
                                        <iframe v-else-if="attachmentType === 'pdf'" :src="attachmentUrl" class="w-full h-80 rounded-xl border border-gray-100" />

                                        <a :href="attachmentUrl" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            Abrir en pestaña nueva
                                        </a>
                                    </template>
                                </div>

                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-3 block">Historial</span>
                                    <SeguimientoTimeline :ticket="ticket" />
                                </div>

                                <div v-if="!readOnly" class="border-t border-gray-100 pt-5 space-y-4">
                                    <div>
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2 block">
                                            Agregar comentario
                                        </label>
                                        <textarea
                                            v-model="comment"
                                            rows="2"
                                            placeholder="Escribe un comentario para este ticket..."
                                            class="w-full text-sm rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 p-3.5 transition-colors resize-none"
                                        />
                                    </div>

                                    <div v-if="canManage && ticket.status === 'pendiente'" class="bg-blue-50/60 rounded-xl p-4 border border-blue-100 space-y-3">
                                        <p class="text-xs font-bold text-blue-800 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm6 3c0-1.657-2.686-3-6-3s-6 1.343-6 3" /></svg>
                                            Asignar y avanzar estado
                                        </p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <select v-model="assignForm.assigned_to" class="text-sm rounded-lg border-gray-200 bg-white p-2.5 focus:border-blue-500 focus:ring-blue-500">
                                                <option value="" disabled>Asignar a...</option>
                                                <option v-for="u in tiUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
                                            </select>
                                            <select v-model="assignForm.priority" class="text-sm rounded-lg border-gray-200 bg-white p-2.5 focus:border-blue-500 focus:ring-blue-500">
                                                <option value="1">Alta</option>
                                                <option value="2">Media</option>
                                                <option value="3">Baja</option>
                                            </select>
                                            <input v-model="assignForm.due_date" type="datetime-local" class="text-sm rounded-lg border-gray-200 bg-white p-2.5 sm:col-span-2 focus:border-blue-500 focus:ring-blue-500" />
                                        </div>
                                        <button
                                            @click="assign"
                                            :disabled="!assignForm.assigned_to || isSubmitting"
                                            class="w-full text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-lg px-4 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                        >
                                            Asignar y avanzar estado
                                        </button>
                                    </div>

                                    <button
                                        v-else-if="canManage && nextStatusFor(ticket.status)"
                                        @click="advance"
                                        :disabled="isSubmitting"
                                        class="w-full sm:w-auto text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-lg px-5 py-2.5 disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center justify-center gap-2"
                                    >
                                        Avanzar a "{{ STATUS_LABELS[nextStatusFor(ticket.status)] }}"
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    show: { type: Boolean, default: false },
    ticketId: { type: Number, default: null },
});

const emit = defineEmits(['close', 'updated']);

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const ticket = ref(null);
const isLoading = ref(false);
const loadError = ref(null);
const newComment = ref('');
const isSubmitting = ref(false);

const ROLE_STYLES = {
    admin: {
        bubble: 'bg-purple-50 border border-purple-100',
        name: 'text-purple-800',
        avatar: 'bg-purple-600',
        tag: 'bg-purple-100 text-purple-700',
        label: 'Admin',
    },
    ti: {
        bubble: 'bg-blue-50 border border-blue-100',
        name: 'text-blue-800',
        avatar: 'bg-blue-600',
        tag: 'bg-blue-100 text-blue-700',
        label: 'TI',
    },
    usuario: {
        bubble: 'bg-emerald-50 border border-emerald-100',
        name: 'text-emerald-800',
        avatar: 'bg-emerald-600',
        tag: 'bg-emerald-100 text-emerald-700',
        label: 'Cliente',
    },
    default: {
        bubble: 'bg-gray-50 border border-gray-100',
        name: 'text-gray-800',
        avatar: 'bg-gray-500',
        tag: 'bg-gray-100 text-gray-700',
        label: 'Sistema',
    },
};

function roleStyle(role) {
    return ROLE_STYLES[role] || ROLE_STYLES.default;
}

function initials(user) {
    if (!user) return '?';
    const n = user.name ? user.name.charAt(0) : '';
    const l = user.last_name ? user.last_name.charAt(0) : '';
    return (n + l).toUpperCase() || '?';
}

function isMine(comment) {
    return comment.changed_by?.id === currentUserId.value;
}

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
        loadError.value = 'Error al cargar los comentarios.';
    } finally {
        isLoading.value = false;
    }
}

watch(() => [props.show, props.ticketId], ([show]) => {
    if (show) load();
    else { ticket.value = null; loadError.value = null; newComment.value = ''; }
});

const comments = computed(() => {
    if (!ticket.value || !ticket.value.status_histories) return [];
    return ticket.value.status_histories.filter(h => h.comment && h.comment.trim() !== '');
});

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function submitComment() {
    if (!newComment.value.trim() || isSubmitting.value) return;
    isSubmitting.value = true;
    router.post(`/tickets/${props.ticketId}/comments`, {
        comment: newComment.value,
    }, {
        onSuccess: () => { 
            newComment.value = ''; 
            load(); 
            emit('updated'); 
        },
        onFinish: () => { isSubmitting.value = false; }
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
                class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4"
                @click.self="$emit('close')"
            >
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="show"
                        class="bg-white w-full max-w-lg max-h-[85vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden"
                    >
                        <div class="shrink-0 bg-[#009C3B] px-6 py-4 flex items-center justify-between">
                            <h2 class="text-white font-bold text-lg">Comentarios</h2>
                            <button
                                @click="$emit('close')"
                                class="text-white/80 hover:text-white transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                            <div v-if="isLoading" class="flex justify-center items-center py-10">
                                <svg class="w-8 h-8 text-gray-300 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
                            </div>
                            <div v-else-if="loadError" class="text-center text-red-500 font-medium py-10">{{ loadError }}</div>
                            <div v-else-if="comments.length === 0" class="text-center text-gray-400 font-medium py-10">Sin comentarios aún.</div>

                            <div v-else class="space-y-3">
                                <div
                                    v-for="comment in comments"
                                    :key="comment.id"
                                    class="flex gap-2.5"
                                    :class="isMine(comment) ? 'flex-row-reverse' : 'flex-row'"
                                >
                                    <div
                                        class="w-8 h-8 rounded-full text-white text-[11px] font-bold flex items-center justify-center shrink-0 shadow-sm"
                                        :class="roleStyle(comment.changed_by?.role).avatar"
                                    >
                                        {{ initials(comment.changed_by) }}
                                    </div>

                                    <div class="max-w-[78%] rounded-2xl p-3.5 shadow-sm" :class="roleStyle(comment.changed_by?.role).bubble">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="font-extrabold text-xs" :class="roleStyle(comment.changed_by?.role).name">
                                                {{ comment.changed_by?.name }} {{ comment.changed_by?.last_name }}
                                            </span>
                                            <span
                                                class="text-[9px] font-bold px-1.5 py-0.5 rounded-full uppercase tracking-wide"
                                                :class="roleStyle(comment.changed_by?.role).tag"
                                            >
                                                {{ roleStyle(comment.changed_by?.role).label }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ comment.comment }}</p>
                                        <div class="text-[10px] text-gray-400 mt-2 font-semibold" :class="isMine(comment) ? 'text-right' : 'text-left'">
                                            {{ formatDate(comment.created_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 bg-white border-t border-gray-100 shrink-0">
                            <div class="relative flex items-center gap-2">
                                <input
                                    v-model="newComment"
                                    type="text"
                                    placeholder="Escribir comentario..."
                                    class="w-full text-sm rounded-xl border-gray-200 bg-gray-50 p-3.5 focus:bg-white focus:border-[#009C3B] focus:ring-1 focus:ring-[#009C3B] transition-colors shadow-sm"
                                    @keydown.enter.prevent="submitComment"
                                    :disabled="isSubmitting"
                                />
                                <button 
                                    @click="submitComment"
                                    :disabled="!newComment.trim() || isSubmitting"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-[#009C3B] hover:bg-[#007a2e] text-white rounded-lg transition-colors disabled:opacity-50 shadow-sm"
                                >
                                    <svg class="w-4 h-4 transform -rotate-45 -ml-0.5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
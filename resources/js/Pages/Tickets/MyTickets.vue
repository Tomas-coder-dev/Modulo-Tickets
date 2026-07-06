<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import SeguimientoModal from '@/Components/Tickets/SeguimientoModal.vue';
import CommentsModal from '@/Components/Tickets/CommentsModal.vue';
import TicketCreatedPopup from '@/Components/TicketCreatedPopup.vue';
import TicketCard from '@/Components/Tickets/TicketCard.vue';

const props = defineProps({
    tickets: { type: Array, default: () => [] },
});

const showModal = ref(false);
const showCommentsModal = ref(false);
const activeTicketId = ref(null);

function openSeguimiento(id) {
    activeTicketId.value = id;
    showModal.value = true;
}

function openComments(id) {
    activeTicketId.value = id;
    showCommentsModal.value = true;
}

const showNewTicketModal = ref(false);
const showCreatedPopup = ref(false);
const fileInput = ref(null);

const form = useForm({
    issue_type: '',
    module: '',
    affected_user: '',
    description: '',
    attachment: null,
});

function openNewTicketModal() {
    showNewTicketModal.value = true;
}

function closeNewTicketModal() {
    showNewTicketModal.value = false;
    form.reset();
    form.clearErrors();
    if (fileInput.value) fileInput.value.value = '';
}

function handleFileChange(event) {
    form.attachment = event.target.files[0];
}

function submitTicket() {
    form.post(route('tickets.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
            showNewTicketModal.value = false;
            showCreatedPopup.value = true;
            router.reload({ only: ['tickets'] });
        },
    });
}

const sortedTickets = computed(() =>
    [...props.tickets].sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at))
);
</script>

<template>
    <Head title="Mis Tickets" />

    <AuthenticatedLayout>
        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-extrabold text-gray-900">Mis Tickets</h1>
                <button
                    type="button"
                    @click="openNewTicketModal"
                    class="inline-flex items-center gap-1.5 bg-[#2563EB] hover:bg-blue-700 text-white text-sm font-bold px-4 py-2.5 rounded-lg shadow-sm transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Ticket
                </button>
            </div>

            <div v-if="!sortedTickets.length" class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
                <p class="text-sm text-gray-400">Aún no has creado ningún ticket.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <TicketCard
                    v-for="t in sortedTickets"
                    :key="t.id"
                    :ticket="t"
                    @open-seguimiento="openSeguimiento"
                    @open-comments="openComments"
                />
            </div>
        </div>

        <SeguimientoModal
            :show="showModal"
            :ticket-id="activeTicketId"
            :ti-users="[]"
            :read-only="true"
            @close="showModal = false"
        />

        <CommentsModal
            :show="showCommentsModal"
            :ticket-id="activeTicketId"
            @close="showCommentsModal = false"
            @updated="() => router.reload({ only: ['tickets'] })"
        />

        <div v-if="showNewTicketModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl relative">

                <button
                    type="button"
                    @click="closeNewTicketModal"
                    class="absolute top-6 right-6 text-gray-400 hover:text-gray-700 transition-colors bg-gray-50 hover:bg-gray-100 p-2 rounded-full"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="p-8 sm:p-10">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-2">Formulario de Reclamación</h1>
                    <p class="text-sm text-gray-500 mb-8">Por favor, completa todos los campos para registrar tu reclamación.</p>

                    <form @submit.prevent="submitTicket">

                        <div class="mb-6">
                            <label for="issue_type" class="block text-sm font-bold text-gray-700 mb-1.5">
                                Tipo de problema <span class="text-red-500">*</span>
                            </label>
                            <select id="issue_type" v-model="form.issue_type" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 bg-white" required>
                                <option value="" disabled>Selecciona un tipo de problema</option>
                                <option value="Error de sistema">Error de sistema</option>
                                <option value="Duda funcional">Duda funcional</option>
                                <option value="Solicitud de mejora">Solicitud de mejora</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.issue_type" />
                        </div>

                        <div class="mb-6">
                            <label for="module" class="block text-sm font-bold text-gray-700 mb-1.5">
                                Modulo del problema <span class="text-red-500">*</span>
                            </label>
                            <select id="module" v-model="form.module" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 bg-white" required>
                                <option value="" disabled>Selecciona un módulo</option>
                                <option value="Toda la plataforma">Toda la plataforma</option>
                                <option value="Login">Login</option>
                                <option value="Reportes">Reportes</option>
                                <option value="Gestión de usuarios">Gestión de usuarios</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.module" />
                        </div>

                        <div class="mb-6">
                            <label for="affected_user" class="block text-sm font-bold text-gray-700 mb-1.5">
                                Usuario que presenta el problema <span class="text-red-500">*</span>
                            </label>
                            <select id="affected_user" v-model="form.affected_user" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 bg-white" required>
                                <option value="" disabled>Selecciona un usuario</option>
                                <option value="El problema me pasa a mí">El problema me pasa a mí</option>
                                <option value="A otro usuario">A otro usuario</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.affected_user" />
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-bold text-gray-700 mb-1.5">
                                Descripción del problema <span class="text-red-500">*</span>
                            </label>
                            <textarea id="description" v-model="form.description" rows="4" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3" placeholder="Proporciona detalles específicos sobre lo ocurrido..." required></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="mb-8">
                            <label for="attachment" class="block text-sm font-bold text-gray-700 mb-1.5">
                                Adjuntar una captura del error <span class="text-red-500">*</span>
                            </label>
                            <input ref="fileInput" id="attachment" type="file" @change="handleFileChange" accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx" class="block w-full rounded-xl border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer" required />
                            <InputError class="mt-2" :message="form.errors.attachment" />
                        </div>

                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" :class="{ 'opacity-75 cursor-not-allowed': form.processing }" :disabled="form.processing">
                            Enviar reclamación
                        </button>

                    </form>

                    <div class="mt-6 flex justify-center items-center text-[11px] text-gray-400">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Tus datos están protegidos por encriptación de grado empresarial.
                    </div>
                </div>
            </div>
        </div>

        <TicketCreatedPopup :show="showCreatedPopup" @close="showCreatedPopup = false" />
    </AuthenticatedLayout>
</template>
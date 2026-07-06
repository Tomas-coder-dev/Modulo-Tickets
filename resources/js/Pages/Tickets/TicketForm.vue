<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import TicketCreatedPopup from '@/Components/TicketCreatedPopup.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    issue_type: '',
    module: '',
    affected_user: '',
    description: '',
    attachment: null,
});

const fileInput = ref(null);
const showPopup = ref(false);

const submit = () => {
    form.post(route('tickets.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
            showPopup.value = true;
        },
    });
};

const handleFileChange = (event) => {
    form.attachment = event.target.files[0];
};
</script>

<template>
    <Head title="Formulario de Reclamación" />

    <div class="min-h-screen bg-[#F5F6F8] flex flex-col justify-center items-center py-10 px-4 font-sans relative">

        <div class="w-full max-w-[700px] bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8 sm:p-10 relative">

            <Link :href="route('tickets.mine')" class="absolute top-6 right-6 text-gray-400 hover:text-gray-700 transition-colors bg-gray-50 hover:bg-gray-100 p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </Link>

            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Formulario de Reclamación</h1>
            <p class="text-sm text-gray-500 mb-8">Por favor, completa todos los campos para registrar tu reclamación.</p>

            <form @submit.prevent="submit">

                <div class="mb-6">
                    <label for="issue_type" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Tipo de problema <span class="text-red-500">*</span>
                    </label>
                    <select id="issue_type" v-model="form.issue_type" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 bg-white" required>
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
                    <select id="module" v-model="form.module" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 bg-white" required>
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
                    <select id="affected_user" v-model="form.affected_user" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 bg-white" required>
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
                    <textarea id="description" v-model="form.description" rows="4" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3" placeholder="Proporciona detalles específicos sobre lo ocurrido..." required></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mb-8">
                    <label for="attachment" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Adjuntar una captura del error <span class="text-red-500">*</span>
                    </label>
                    <input ref="fileInput" id="attachment" type="file" @change="handleFileChange" accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx" class="block w-full rounded-lg border border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm bg-white file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer" required />
                    <p class="mt-2 text-xs text-gray-500">Jpg, png, mp4, word, pdf</p>
                    <InputError class="mt-2" :message="form.errors.attachment" />
                </div>

                <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-lg shadow-sm text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" :class="{ 'opacity-75 cursor-not-allowed': form.processing }" :disabled="form.processing">
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

        <div class="absolute bottom-6 right-8 text-xs text-gray-500 font-semibold tracking-wide hidden md:block">
            Portal de Soporte <span class="text-gray-300 mx-2 font-normal">|</span> Firefly One
        </div>

        <TicketCreatedPopup :show="showPopup" @close="showPopup = false" />

    </div>
</template>
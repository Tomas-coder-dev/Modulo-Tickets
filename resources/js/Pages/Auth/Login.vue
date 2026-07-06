<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="min-h-screen bg-[#F5F6F8] flex flex-col justify-center items-center relative p-4 font-sans">

        <div class="w-full max-w-[480px] bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] p-8 sm:p-10">
            
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">¡Bienvenido!</h1>
                    <h2 class="text-lg font-bold text-gray-900 mt-1">Iniciar sesión</h2>
                    <p class="text-xs text-gray-400 mt-1">Plataforma de gestión de soporte</p>
                </div>
                <img src="/images/logo.svg" alt="Firefly One" class="h-12 object-contain" />
            </div>

            <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                
                <div class="mb-5">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Correo electrónico <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 placeholder-gray-400"
                        placeholder="ejemplo@correo.com"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mb-8">
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-3 placeholder-gray-400"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <button
                    type="submit"
                    class="w-full flex justify-center py-3.5 px-4 rounded-lg shadow-sm text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    Iniciar sesión
                </button>
            </form>

            <div class="mt-5 flex justify-center items-center text-[11px] text-gray-400">
                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Tus datos están protegidos por encriptación de grado empresarial.
            </div>

        </div>

        <div class="absolute bottom-6 right-8 text-xs text-gray-500 font-semibold tracking-wide">
            Portal de Soporte <span class="text-gray-300 mx-2 font-normal">|</span> Firefly One
        </div>

    </div>
</template>
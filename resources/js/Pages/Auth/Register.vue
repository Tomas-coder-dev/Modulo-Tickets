<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    last_name: '',
    document_number: '',
    phone: '',
    email: '',
    company: '',
    platform: '',
    role: '',
    password: '',
    password_confirmation: '',
    avatar: null,
});

const avatarPreview = ref(null);

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    form.avatar = file;

    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    } else {
        avatarPreview.value = null;
    }
};

const submit = () => {
    form.post(route('register'), {
        forceFormData: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Creación de usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Registrar Usuario
            </h2>
        </template>

        <div class="py-8 sm:py-10 px-4 font-sans flex justify-center">
            <div class="w-full max-w-[760px] bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-gray-100 overflow-hidden">

                <div class="px-6 sm:px-10 pt-8 pb-6 border-b border-gray-100">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Nuevo usuario</h1>
                </div>

                <form @submit.prevent="submit" class="px-6 sm:px-10 py-8">

                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Foto de perfil</p>
                        <div class="flex items-center gap-5">
                            <div class="shrink-0">
                                <img
                                    v-if="avatarPreview"
                                    :src="avatarPreview"
                                    alt="Vista previa"
                                    class="h-16 w-16 rounded-full object-cover border border-gray-200"
                                />
                                <div
                                    v-else
                                    class="h-16 w-16 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center"
                                >
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <input
                                    id="avatar"
                                    type="file"
                                    accept="image/*"
                                    @change="handleAvatarChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <p class="text-xs text-gray-400 mt-1.5">JPG, PNG o GIF. Máximo 2MB.</p>
                                <InputError class="mt-1.5" :message="form.errors.avatar" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Datos personales</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <input id="name" type="text" v-model="form.name" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="Escribe tu nombre" required autofocus />
                                <InputError class="mt-1.5" :message="form.errors.name" />
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Apellido <span class="text-red-500">*</span>
                                </label>
                                <input id="last_name" type="text" v-model="form.last_name" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="Escribe tu apellido" required />
                                <InputError class="mt-1.5" :message="form.errors.last_name" />
                            </div>

                            <div>
                                <label for="document_number" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Nro de documento <span class="text-red-500">*</span>
                                </label>
                                <input id="document_number" type="text" v-model="form.document_number" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="Número de documento" required />
                                <InputError class="mt-1.5" :message="form.errors.document_number" />
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Teléfono <span class="text-red-500">*</span>
                                </label>
                                <input id="phone" type="text" v-model="form.phone" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="Número de celular" required />
                                <InputError class="mt-1.5" :message="form.errors.phone" />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="email" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Correo electrónico <span class="text-red-500">*</span>
                                </label>
                                <input id="email" type="email" v-model="form.email" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="ejemplo@correo.com" required autocomplete="username" />
                                <InputError class="mt-1.5" :message="form.errors.email" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Acceso a la plataforma</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="role" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Rol en la plataforma <span class="text-red-500">*</span>
                                </label>
                                <select id="role" v-model="form.role" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5 bg-white" required>
                                    <option value="" disabled>Selecciona un rol</option>
                                    <option value="admin">Administrador</option>
                                    <option value="ti">Usuario TI</option>
                                    <option value="usuario">Usuario Soporte</option>
                                </select>
                                <InputError class="mt-1.5" :message="form.errors.role" />
                            </div>

                            <div>
                                <label for="company" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Empresa <span class="text-red-500">*</span>
                                </label>
                                <select id="company" v-model="form.company" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5 bg-white" required>
                                    <option value="" disabled>Selecciona una empresa</option>
                                    <option value="La Positiva Seguros">La Positiva Seguros</option>
                                    <option value="Sanna">Sanna</option>
                                    <option value="Profuturo">Profuturo</option>
                                    <option value="Mapfre">Mapfre</option>
                                </select>
                                <InputError class="mt-1.5" :message="form.errors.company" />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="platform" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Plataforma <span class="text-red-500">*</span>
                                </label>
                                <select id="platform" v-model="form.platform" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5 bg-white" required>
                                    <option value="" disabled>Selecciona una plataforma</option>
                                    <option value="Mi Asesor Positivo">Mi Asesor Positivo</option>
                                    <option value="Planning Corredores">Planning Corredores</option>
                                    <option value="Protechting 2.0">Protechting 2.0</option>
                                </select>
                                <InputError class="mt-1.5" :message="form.errors.platform" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Seguridad</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="password" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Contraseña <span class="text-red-500">*</span>
                                </label>
                                <input id="password" type="password" v-model="form.password" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="••••••••" required autocomplete="new-password" />
                                <InputError class="mt-1.5" :message="form.errors.password" />
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1.5">
                                    Confirmar contraseña <span class="text-red-500">*</span>
                                </label>
                                <input id="password_confirmation" type="password" v-model="form.password_confirmation" class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-4 py-2.5" placeholder="••••••••" required autocomplete="new-password" />
                                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-lg shadow-sm text-sm font-bold text-white bg-[#2563EB] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" :class="{ 'opacity-75 cursor-not-allowed': form.processing }" :disabled="form.processing">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        {{ form.processing ? 'Creando usuario...' : 'Crear usuario' }}
                    </button>

                </form>

                <div class="px-6 sm:px-10 pb-8 flex justify-center items-center text-[11px] text-gray-400">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Tus datos están protegidos por encriptación de grado empresarial.
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
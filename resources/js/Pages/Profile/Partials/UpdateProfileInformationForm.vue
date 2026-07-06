<script setup>
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
    remove_avatar: false,
});

const avatarPreview = ref(user.avatar_url ?? null);
const isDragging = ref(false);
const fileInput = ref(null);

const initials = computed(() => {
    return (user.name || '')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase())
        .join('');
});

function setAvatarFile(file) {
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        form.errors.avatar = 'El archivo debe ser una imagen.';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        form.errors.avatar = 'La imagen no debe superar los 2MB.';
        return;
    }

    form.clearErrors('avatar');
    form.avatar = file;
    form.remove_avatar = false;
    avatarPreview.value = URL.createObjectURL(file);
}

const handleFileInput = (e) => setAvatarFile(e.target.files[0]);

const handleDrop = (e) => {
    isDragging.value = false;
    setAvatarFile(e.dataTransfer.files[0]);
};

const removeAvatar = () => {
    form.avatar = null;
    form.remove_avatar = true;
    avatarPreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
    form.patch(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Información del perfil
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Actualiza tu foto, nombre y correo electrónico.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel value="Foto de perfil" />

                <div
                    class="mt-2 flex flex-col sm:flex-row sm:items-center gap-4"
                >
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        @click="fileInput.click()"
                        class="relative shrink-0 h-20 w-20 rounded-full cursor-pointer group ring-offset-2 transition-all duration-150"
                        :class="isDragging ? 'ring-2 ring-indigo-500' : ''"
                    >
                        <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            alt="Avatar"
                            class="h-20 w-20 rounded-full object-cover border border-gray-200 transition-opacity group-hover:opacity-75"
                        />
                        <div
                            v-else
                            class="h-20 w-20 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-400 font-semibold text-lg transition-colors group-hover:bg-indigo-100"
                        >
                            {{ initials || '?' }}
                        </div>

                        <div
                            class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/40 flex items-center justify-center transition-colors"
                        >
                            <svg
                                class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1">
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleFileInput"
                        />

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="fileInput.click()"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors"
                            >
                                Cambiar foto
                            </button>
                            <span v-if="avatarPreview" class="text-gray-300">·</span>
                            <button
                                v-if="avatarPreview"
                                type="button"
                                @click="removeAvatar"
                                class="text-sm font-semibold text-gray-500 hover:text-red-600 transition-colors"
                            >
                                Quitar
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Arrastra una imagen o haz clic. JPG o PNG, máx. 2MB.
                        </p>

                        <InputError class="mt-1.5" :message="form.errors.avatar" />
                    </div>
                </div>
            </div>

            <div>
                <InputLabel for="name" value="Nombre" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Correo electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Tu correo electrónico no ha sido verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Haz clic aquí para reenviar el correo de verificación.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">
                    <svg
                        v-if="form.processing"
                        class="w-4 h-4 mr-2 animate-spin"
                        fill="none" viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    {{ form.processing ? 'Guardando...' : 'Guardar' }}
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 flex items-center gap-1"
                    >
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
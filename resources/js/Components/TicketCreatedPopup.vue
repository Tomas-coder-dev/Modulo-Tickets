<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-500/50 transition-opacity">
        
        <div class="bg-white rounded-[20px] shadow-2xl w-full max-w-[420px] p-10 text-center flex flex-col items-center relative transform transition-all">
            
            <div class="w-[60px] h-[60px] bg-[#ECFDF5] rounded-full flex items-center justify-center mb-6 border-[6px] border-white shadow-[0_0_0_2px_#ECFDF5]">
                <svg class="w-8 h-8 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h2 class="text-[22px] font-extrabold text-gray-900 mb-3 leading-tight tracking-tight">
                Reclamación enviada con<br>éxito
            </h2>

            <p class="text-[14px] text-gray-500 mb-8 px-2 leading-relaxed font-medium">
                Tu reclamación ha sido registrada correctamente. Te contactaremos pronto.
            </p>

            <button @click="$emit('close')" class="w-full bg-[#00C48C] hover:bg-[#00A878] text-white font-bold py-3.5 px-4 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00C48C] shadow-sm">
                Aceptar
            </button>
            
        </div>
    </div>
</template>
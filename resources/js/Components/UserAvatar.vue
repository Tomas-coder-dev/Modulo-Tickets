<script setup>
import { computed } from 'vue';

const props = defineProps({
    user: { type: Object, default: null },
    size: { type: String, default: 'w-8 h-8' },
    textSize: { type: String, default: 'text-[11px]' },
    bgClass: { type: String, default: 'bg-gray-200' },
    textClass: { type: String, default: 'text-gray-600' },
});

const initials = computed(() => {
    if (!props.user) return '?';
    const n = props.user.name ? props.user.name.charAt(0) : '';
    const l = props.user.last_name ? props.user.last_name.charAt(0) : '';
    return (n + l).toUpperCase() || '?';
});
</script>

<template>
    <img
        v-if="user?.avatar_url"
        :src="user.avatar_url"
        :alt="user?.name"
        class="rounded-full object-cover shrink-0"
        :class="size"
    />
    <div
        v-else
        class="rounded-full flex items-center justify-center font-bold shrink-0"
        :class="[size, textSize, bgClass, textClass]"
    >
        {{ initials }}
    </div>
</template>
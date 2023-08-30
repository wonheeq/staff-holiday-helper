<script setup>
//import NavLink from '@/Components/NavLink.vue';
const props = defineProps({
    options: Object,
    activeScreen: String,
});
const emit = defineEmits(['screen-changed']);
</script>
<template>
    <div class="flex space-x-4">
        <button class="w-80 text-2xl text-center p-2 rounded-tl-md rounded-tr-md"
            :class="{ 'bg-white': activeScreen===option.id, 'bg-gray-300': activeScreen!==option.id }"
            v-for="option in options"
            @click="activeScreen = option.id; emit('screen-changed', option.id)"
        >
            {{ option.title }}
        </button>
    </div>
</template>
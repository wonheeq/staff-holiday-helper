<script setup>
import { ref } from 'vue';

let emit = defineEmits(['optionSelected']);

let props = defineProps({
    label: String,
});

let isHover = ref(false);
</script>

<template>
<button
    class="w-full p-1 border-b text-xs 1080:text-base"
    :class="{
        'inline-flex border-b-2 border-transparent  text-sm font-medium leading-5 text-gray-500 focus:outline-none transition duration-150 ease-in-out': !isHover,
        'inline-flex border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-black hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out': isHover
    }"
    @mouseenter="isHover=true"
    @mouseleave="isHover=false"
    @click="emit('optionSelected', label )"
>
    {{ label }}
</button>
</template>
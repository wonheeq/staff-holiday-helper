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
    class="w-full p-1 border-b text-xs 1080:text-sm 1440:text-base 4k:text-2xl"
    :class="{
        'inline-flex border-b-2 border-transparent font-medium leading-5 text-gray-500 focus:outline-none transition duration-150 ease-in-out': !isHover,
        'inline-flex border-b-2 font-medium leading-5 text-gray-500 hover:text-black hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out': isHover
    }"
    @mouseenter="isHover=true"
    @mouseleave="isHover=false"
    @click="emit('optionSelected', label )"
>
    {{ label }}
</button>
</template>
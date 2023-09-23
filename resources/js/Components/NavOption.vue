<script setup>
import { ref, computed } from 'vue';
import { useDark } from "@vueuse/core";
const isDark = useDark();
let active = ref(false);

const classes = computed(() => {
    return isDark.value
    ?
        'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-300 hover:text-white hover:border-white focus:outline-none focus:text-white focus:border-white transition duration-150 ease-in-out'
    :
        'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-black hover:border-black focus:outline-none focus:text-black focus:border-black transition duration-150 ease-in-out'
});
</script>
<template>
    <button :class="classes" @mouseenter="active=true" @mouseleave="active=false">
        <slot />
    </button>
</template>
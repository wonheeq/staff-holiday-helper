<script setup>
//import NavLink from '@/Components/NavLink.vue';
import { ref, defineEmits, defineProps } from 'vue';
const props = defineProps({ options: Object });
const emit = defineEmits(['screenChanged']);
let selected = ref("applications");
</script>
<template>
    <div class="flex space-x-4">
        <button class="w-80 text-2xl text-center p-2 rounded-tl-md rounded-tr-md"
            :class="{ 'bg-white': selected===option.id, 'bg-gray-300': selected!==option.id }"
            v-for="option in options"
            @click="selected = option.id; $emit('screenChanged', option.id)"
        >
            {{ option.title }}
        </button>
    </div>
</template>
<script setup>
import { ref } from 'vue';

let status = ref('U');
let emit = defineEmits(['statusUpdated']);

</script>
<template>
    <div class="flex space-x-4">
        <button class="rounded-md border border-black p-2 px-6"
            :class="status === 'Y' ? 'bg-green-500': ''"
            @click="status = 'Y'; emit('statusUpdated', status)"
        >
            Accept
        </button>
        <button class="rounded-md border border-black p-2 px-6"
            :class="status === 'N' ? 'bg-red-500': ''"
            @click="status = 'N'; emit('statusUpdated', status)"
        >
            Reject
        </button>
    </div>
</template>
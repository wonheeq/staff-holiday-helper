<script setup>
import { ref } from 'vue';

let status = ref('U');

</script>
<template>
    <div class="flex space-x-4">
        <button class="rounded-md border border-black p-2"
            :class="status === 'Y' ? 'bg-green-400': ''"
            @click="status = 'Y'"
        >
            Accept
        </button>
        <button class="rounded-md border border-black p-2"
            :class="status === 'N' ? 'bg-red-400': ''"
            @click="status = 'N'"
        >
            Reject
        </button>
    </div>
</template>
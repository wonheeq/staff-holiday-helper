<template>
    <button class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2">

        <svg v-show="isLoading" class="absolute left-1/2 -ml-5 -mt-1 w-10 h-10 text-white animate-spin" fill="none"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                fill="currentColor"></path>
        </svg>


        <span :class="{'invisible': isLoading}">Reset Password</span>
    </button>
</template>


<script setup>
import { ref } from "vue";

const isLoading = ref(true);
</script>

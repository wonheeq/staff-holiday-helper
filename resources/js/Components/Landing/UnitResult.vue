<!--
    File: UnitResult.vue
    Purpose: Vue Component for the Unit Lookup Results for use in Landing.vue
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<script setup>
const props = defineProps({
    unitId: { type: String, required: true },
    unitName: { type: String, required: true },
    email: { type: String, required: true },
    name: { type: String, required: true },
});
</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">
    <!-- Box/Background -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <!-- PlaceHolder Results -->
        <h1 class="font-bold text-2xl mb-1">Showing Result For:</h1>
        <h2 class="font-bold mb-5">{{ unitName }} ({{ unitId }})</h2>
        <p class="mb-1">Currently Responsible: {{ name }}</p>
        <p class="mb-7">Staff Email: {{ email }}</p>

        <!-- Back/Search Aagain -->
        <button
            @click="$emit('resultBack')"
            class="w-full font-bold text-2xl bg-blue-300 p-2 mb-5"
        >Search Again</button>

    </div>
</div>
</template>



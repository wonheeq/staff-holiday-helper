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
    <div class=" laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <!-- Results Display -->
        <h1 class="font-bold text-2xl 4k:text-3xl mb-1">Showing Result For:</h1>
        <h2 class="font-bold mb-4 4k:text-2xl">{{ unitName }} ({{ unitId }})</h2>
        <p class="mb-1 4k:text-xl">Currently Responsible: {{ name }}</p>
        <p class="mb-7 4k:text-xl">Staff Email: {{ email }}</p>

        <!-- Back/Search Aagain -->
        <button
            @click="$emit('resultBack')"
            class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
        >Back</button>
    </div>
</div>
</template>



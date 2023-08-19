<script setup>
const props = defineProps({
    results: { type: Object, required: true },
});


</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">
    <!-- Box/Background -->
    <div class=" laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <!-- Results Display -->
        <h1 class="font-bold text-2xl 4k:text-3xl mb-1">Showing Results For:</h1>
        <h2 class="font-bold mb-1 4k:text-2xl">{{ results.data.unitName }} ({{ results.data.unitId }})</h2>
        <h2 class="font-bold mb-1 4k:text-2xl">Currently Responsible Staff:</h2>

        <h2 class="mt-5 mb-1 font-bold 4k:text-xl">Course Coordinator:</h2>
        <h1 class="mb-1 4k:text-xl indent-10" >Name: {{ results.data.courseCoord[1] }}</h1>
        <h1 class="mb-1 4k:text-xl indent-10" >Email: {{ results.data.courseCoord[0] }}</h1>

        <h2 class="mt-3 mb-1 font-bold 4k:text-xl">Major Coordinator:</h2>
        <h1 class="mb-1 4k:text-xl indent-10" >Name: {{ results.data.majorCoord[1] }}</h1>
        <h1 class="mb-1 4k:text-xl indent-10" >Email: {{ results.data.majorCoord[0] }}</h1>

        <h2 class="mt-3 mb-1 font-bold 4k:text-xl">Unit Coordinator:</h2>
        <h1 class="mb-1 4k:text-xl indent-10" >Name: {{ results.data.unitCoord[1] }}</h1>
        <h1 class="mb-1 4k:text-xl indent-10" >Email: {{ results.data.unitCoord[0] }}</h1>

        <h2 class="mt-3 mb-1 font-bold 4k:text-xl">Lecturers:</h2>



        <!-- Back/Search Aagain -->
        <button
            @click="$emit('resultBack')"
            class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
        >Back</button>
    </div>
</div>
</template>



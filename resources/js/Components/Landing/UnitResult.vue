<script setup>
const props = defineProps({
    results: { type: Object, required: true },
});

// Builds the list of lecturers to display
function getList()
{
    var lecturers = props.results.data.lecturers;
    var str = '';
    // loop and append details of each lecturer
    lecturers.forEach(function(lecturer){
        var name = lecturer[1];
        var email = lecturer[0];
        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';
    });
    // chop trailing whitespace off
    str = str.slice(0, -6);
    return str;
}

</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">
    <!-- Box/Background -->
    <div class=" laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] bg-white p-5 drop-shadow-md
                 laptop:h-fit 1080:h-fit 1440:h-fit 4k:h-fit
                 laptop:max-h-[80%] 1080:max-h-[80%] 1440:max-h-[60%] 4k:max-h-[60%]" >

        <!-- Results Heading -->
        <h1 class="font-bold text-2xl 4k:text-3xl mb-1">Showing Results For:</h1>
        <h2 class="font-bold mb-1 4k:text-2xl">{{ results.data.unitName }} ({{ results.data.unitId }})</h2>
        <h2 class="font-bold mb-1 4k:text-2xl">Currently Responsible Staff:</h2>

        <!-- Results Display -->
        <div class="mb-7 overflow-auto h-fit 1440:max-h-[37rem] 1080:max-h-[39rem] laptop:max-h-[23rem] 4k:max-h-[50rem]">

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
            <div class="mb-7 ml-10 4k:text-xl whitespace-pre-line">{{  getList() }}</div>
        </div>

        <!-- Back/Search Aagain -->
        <button
            @click="$emit('resultBack')"
            class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
        >Back</button>
    </div>
</div>
</template>



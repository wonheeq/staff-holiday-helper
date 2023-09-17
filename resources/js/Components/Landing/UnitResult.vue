<script setup>
import { onMounted, ref } from 'vue';
const props = defineProps({
    results: { type: Object, required: true },
});
let hasUC = ref(true);
let hasLec = ref(true);

onMounted(() => {
    if( props.results.data.unitCoord == '') {
        hasUC.value = false;
    }

    if( props.results.data.lecturers.length == 0) {
        hasLec.value = false;
    }
})

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
        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';        str += 'Name: ' + name + '\n';
        str += 'Email: ' + email + '\n\n';
    });
    // chop trailing whitespace off
    str = str.slice(0, -6);
    return str;
}


function isMobile() {
    if( screen.availWidth <= 760 ) {
        return true;
    }
    else {
        return false;
    }
}
</script>

<template>
<div v-if="isMobile()">
    <div class="w-screen h-screen flex justify-center items-center ">
        <!-- Box/Background -->
        <div class="w-[80%]  bg-white p-5 drop-shadow-md w-[80%] max-h-[75%] flex flex-col" >

            <!-- Results Heading -->
            <h1 class="font-bold text-2xl mb-1">Showing Results For:</h1>
            <h2 class="font-bold mb-1 ">{{ results.data.unitName }} ({{ results.data.unitId }})</h2>
            <h2 class="font-bold mb-1 ">Currently Responsible Staff:</h2>

            <!-- Results Display -->
            <div class="mb-7 overflow-auto ">

                <!-- Unit Coordinator -->
                <div>
                    <h2 class="mt-3 mb-1 font-bold ">Unit Coordinator:</h2>
                    <div v-show="hasUC">
                        <h1 class="mb-1 indent-7" >Name: {{ results.data.unitCoord[1] }}</h1>
                        <h1 class="mb-1 indent-7" >Email: {{ results.data.unitCoord[0] }}</h1>
                    </div>
                    <h1 v-show="!hasUC" class="mb-1 indent-7">None Found</h1>
                </div>

                <!-- Lecturers -->
                <div>
                    <h2 class="mt-3 mb-1 font-bold ">Lecturers:</h2>
                    <div v-show="hasLec" class="mb-7 ml-7 whitespace-pre-line">{{  getList() }}</div>
                    <h1 v-show="!hasLec" class="mb-1 indent-7">None Found</h1>
                </div>

            </div>

            <!-- Back/Search Aagain -->
            <button
                @click="$emit('resultBack')"
                class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2"
            >Back</button>
        </div>
    </div>
</div>


<div v-else>
    <div class="w-screen h-screen flex justify-center items-center ">
        <!-- Box/Background -->
        <div class=" laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] bg-white p-5 drop-shadow-md
                     laptop:h-fit 1080:h-fit 1440:h-fit 4k:h-fit
                     laptop:max-h-[80%] 1080:max-h-[80%] 1440:max-h-[60%] 4k:max-h-[60%]
                     flex flex-col" >

            <!-- Results Heading -->
            <h1 class="font-bold text-2xl 4k:text-3xl mb-1">Showing Results For:</h1>
            <h2 class="font-bold mb-1 4k:text-2xl">{{ results.data.unitName }} ({{ results.data.unitId }})</h2>
            <h2 class="font-bold mb-1 4k:text-2xl">Currently Responsible Staff:</h2>

            <!-- Results Display -->
            <div class="mb-7 overflow-auto  1440:max-h-[37rem] 1080:max-h-[32rem] laptop:max-h-[23rem] 4k:max-h-[50rem]">

                <!-- Unit Coordinator -->
                <div>
                    <h2 class="mt-3 mb-1 font-bold 4k:text-xl">Unit Coordinator:</h2>
                    <div v-show="hasUC">
                        <h1 class="mb-1 4k:text-xl indent-10" >Name: {{ results.data.unitCoord[1] }}</h1>
                        <h1 class="mb-1 4k:text-xl indent-10" >Email: {{ results.data.unitCoord[0] }}</h1>
                    </div>
                    <h1 v-show="!hasUC" class="mb-1 4k:text-xl indent-10">None Found</h1>
                </div>

                <!-- Lecturers -->
                <div>
                    <h2 class="mt-3 mb-1 font-bold 4k:text-xl">Lecturers:</h2>
                    <div v-show="hasLec" class="mb-7 ml-10 4k:text-xl whitespace-pre-line">{{  getList() }}</div>
                    <h1 v-show="!hasLec" class="mb-1 4k:text-xl indent-10">None Found</h1>
                </div>

            </div>

            <!-- Back/Search Aagain -->
            <button
                @click="$emit('resultBack')"
                class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
            >Back</button>
        </div>
    </div>
</div>

</template>



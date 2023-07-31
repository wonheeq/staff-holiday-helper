<script setup>
import { reactive } from 'vue';
import CreateSubpagePeriod from './CreateSubpagePeriod.vue';
import CreateSubpageNominations from './CreateSubpageNominations.vue';
import CalendarSmall from '../CalendarSmall.vue';

let props = defineProps({ subpageClass: String });

let period = reactive({
    start: null,
    end: null,
});
</script>
<template>
    <div class="flex bg-transparent w-full h-full">
        <div class="w-5/6 flex flex-col p-4 mr-4" :class="subpageClass">
            <p class="text-5xl h-[8%] font-bold">
                Create New Leave Application:
            </p>
            <div class="grid grid-cols-2 h-[92%]">
                <CreateSubpagePeriod :period="period"/>
                <CreateSubpageNominations />
            </div>
        </div>
        <CalendarSmall class="w-1/6 flex flex-col" :disableEnlarge="true"/>
    </div>
</template>
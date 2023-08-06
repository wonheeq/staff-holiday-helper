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

function resetFields() {
    period.start = null;
    period.end = null;
}
</script>
<template>
    <div class="flex bg-transparent subpage-height">
        <div class="w-5/6 flex flex-col p-4 mr-4 subpage-height" :class="subpageClass">
            <p class="text-5xl h-[8%] font-bold">
                Create New Leave Application:
            </p>
            <div class="grid grid-cols-3 h-[92%]">
                <CreateSubpagePeriod :period="period" class="h-full" />
                <CreateSubpageNominations class="col-span-2" @resetFields="resetFields()"/>
            </div>
        </div>
        <CalendarSmall class="w-1/6 flex flex-col h-full" :disableEnlarge="true"/>
    </div>
</template>
<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
</style>
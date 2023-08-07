<script setup>
import Modal from '../Modal.vue';
import CreateSubpagePeriod from './CreateSubpagePeriod.vue';
import CreateSubpageNominations from './CreateSubpageNominations.vue';
import CalendarSmall from '../CalendarSmall.vue';
import { reactive } from 'vue';
import { storeToRefs } from 'pinia';
import { useNominationStore } from '@/stores/NominationStore';
let nominationStore = useNominationStore();
const { nominations, isSelfNominateAll } = storeToRefs(nominationStore);
let emit = defineEmits(['close']);
let props = defineProps({
    applicationNo: Number,
    subpageClass: String,
    period: Object,
});

function resetFields() {
    props.period.start = null;
    props.period.end = null;

    for (let nomination of nominations.value) {
        nomination.nomination = "";
        nomination.selected = false;
        nomination.visible = true;
    }
    isSelfNominateAll.value = false;
}
</script>
<template>
<Modal>
    <div class="flex bg-transparent subpage-height w-screen px-4 mt-auto mb-4">
        <div class="w-5/6 flex flex-col p-4 mr-4 subpage-height rounded-tl-md" :class="subpageClass">
            <div class="h-[8%] flex justify-between">
                <p class="text-5xl font-bold">
                    Edit Leave Application (ID: {{ applicationNo }}):
                </p>
                <button class="h-full"
                    @click="resetFields(); $emit('close')"
                >
                    <img src="/images/close.svg" class="h-full w-full"/>
                </button>
            </div>
            <div class="grid grid-cols-3 h-[92%]">
                <CreateSubpagePeriod :period="period" :isEditing="true" class="h-full" />
                <CreateSubpageNominations
                    :isEditing="true"
                    :applicationNo="applicationNo"
                    @resetFields="resetFields()"
                    class="col-span-2"
                />
            </div>
        </div>
        <CalendarSmall class="w-1/6 flex flex-col h-full" :disableEnlarge="true"/>
    </div>
</Modal>
</template>
<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
</style>
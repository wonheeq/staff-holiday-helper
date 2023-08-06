<script setup>
import { reactive } from 'vue';
import CreateSubpagePeriod from './CreateSubpagePeriod.vue';
import CreateSubpageNominations from './CreateSubpageNominations.vue';
import CalendarSmall from '../CalendarSmall.vue';
import { storeToRefs } from 'pinia';
import { useNominationStore } from '@/stores/NominationStore';
import Swal from 'sweetalert2';
let nominationStore = useNominationStore();
const { nominations } = storeToRefs(nominationStore);
let props = defineProps({ subpageClass: String });

let period = reactive({
    start: null,
    end: null,
});

function resetFields() {
    period.start = null;
    period.end = null;
}

function calcDateDiff(d1, d2) {
    return new Date(d1) - new Date(d2);
}

let errors = [];
function validateApplication(data) {
    errors = [];

    // Date is empty
    if (period.end == null || period.start == null) {
        errors.push("Dates cannot be empty")
    }

    // End date is earlier than start date
    if (period.end != null && period.start != null && calcDateDiff(period.end, period.start) < 0) {
        errors.push("End date/time cannot be earlier than start date/time");
    }

    // End date is equal to start date
    if (period.end != null && period.start != null && period.end == period.start) {
        errors.push("End date/time cannot be the same as the start date/time");
    }

    // A date is in the past
    let currentDate = new Date();
    if (period.end != null && period.start != null &&
        (calcDateDiff(period.start, currentDate) < 0 || calcDateDiff(period.end, currentDate) < 0)) {
        errors.push("Dates cannot be in the past");
    }

    // Not self nominated for all and a nomination is missing/empty
    if (!data.selfNominateAll && nominations.value.filter(nomination => nomination.nomination == "").length > 0) {
        errors.push("Missing nomination/s");
    }

    // Selected "Self Nomination" for all nominations but did not select agreement
    if (!data.selfNominateAll && nominations.value.filter(nomination => nomination.nomination == "Self Nomination").length == nominations.value.length) {
        errors.push("Selected Self Nomination for all nominations but have not agreed to the no nominations terms.");
    }

    return errors.length == 0;
}

function createApplication(data) {
    if (validateApplication(data)) {

    }
    else {
        Swal.fire({
           icon: "error",
           text:  errors
        });
    }
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
                <CreateSubpageNominations
                    class="col-span-2"
                    @resetFields="resetFields()"
                    @submitApplication="(data) => createApplication(data)"
                    />
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
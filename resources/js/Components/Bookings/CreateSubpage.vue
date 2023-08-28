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

    for (let nomination of nominations.value) {
        nomination.nomination = "";
        nomination.selected = false;
        nomination.visible = true;
    }
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
        (calcDateDiff(period.start, currentDate) <= 0 || calcDateDiff(period.end, currentDate) <= 0)) {
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

function formatNomineeNo(nominee, accountNo) {
    if (nominee == "Self Nomination") {
        return accountNo;
    }

    // Should be formatted as "(XXXXXXX) - ZZZZZZZZZZZZ"
    // We want to extract XXXXXXX, so start from index 1, 7 characters
    return nominee.substr(1, 7);
}

function formatNominations(accountNo) {
    let result = [];

    for (let nomination of nominations.value) {
        result.push({
            accountRoleId: nomination.accountRoleId,
            nomineeNo: formatNomineeNo(nomination.nomination, accountNo),
        });
    }

    return result;
}

function createApplication(data) {
    if (validateApplication(data)) {
        data.selfNominateAll = data.selfNominateAll || nominations.value.filter(nomination => nomination.nomination == "Self Nomination").length == nominations.value.length;
        data.nominations = formatNominations(data.accountNo);
        data.sDate = period.start;
        data.eDate = period.end;

        resetFields();
        
        axios.post('/api/createApplication', data)
            .then(res => {
                if (res.status == 200) {
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully created application.'
                    });
                }
            }).catch(err => {
            console.log(err)
        });
    }
    else {
        Swal.fire({
           icon: "error",
           title: "Error",
           text:  errors
        });
    }
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
    <div v-if="isMobile()" class="w-full">
        <div class="w-full h-fit bg-white rounded-b-md p-2">
            <p class="text-xl font-bold">
                Create New Leave Application:
            </p>
            <div>
                <CreateSubpagePeriod :period="period" />
                <CreateSubpageNominations
                    @resetFields="resetFields()"
                    @submitApplication="(data) => createApplication(data)"
                    />
            </div>
        </div>
        <CalendarSmall
            class="flex drop-shadow-md mt-2"
            :disableEnlarge="true"
            @enlarge-calendar="calendarLarge=true"    
        />
        <div class="h-2">

        </div>
    </div>
    <div v-else class="flex bg-transparent subpage-height">
        <div class="w-4/5 1080:w-[85%] 1440:w-5/6 flex flex-col p-4 mr-4 subpage-height" :class="subpageClass">
            <p class="text-3xl 1080:text-4xl 1440:text-5xl 4k:text-6xl h-[8%] font-bold">
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
        <CalendarSmall class="w-1/5 1080:w-[15%] 1440:w-1/6 flex flex-col h-full" :disableEnlarge="true"/>
    </div>
</template>
<style>
.subpage-height {
    height: calc(0.95 * 93vh - 3rem);
}
</style>
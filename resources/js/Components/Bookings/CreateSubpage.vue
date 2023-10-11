<script setup>
import { reactive, computed, inject } from 'vue';
import CreateSubpagePeriod from './CreateSubpagePeriod.vue';
import CreateSubpageNominations from './CreateSubpageNominations.vue';
import CalendarSmall from '../CalendarSmall.vue';
import { storeToRefs } from 'pinia';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { useNominationStore } from '@/stores/NominationStore'; 
import Swal from 'sweetalert2';
import { useCalendarStore } from '@/stores/CalendarStore';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { usePage } from '@inertiajs/vue3'
import { useDark } from "@vueuse/core";
const isDark = useDark();
const dayJS = inject("dayJS");
const page = usePage();
const user = computed(() => page.props.auth.user);
const calendarStore = useCalendarStore();
const { fetchCalendarData } = calendarStore;
const applicationStore = useApplicationStore();
const { addNewApplication } = applicationStore;
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
let nominationStore = useNominationStore();
const { nominations } = storeToRefs(nominationStore);
let props = defineProps({ subpageClass: String });


const coeff = 1000 * 60 * 5;
function initDate(minutes) {
    // Get current date
    // Add x minutes to the date
    // Round down the date to the nearest 5 minutes
    return new Date(Math.round(new Date(new Date().getTime() + minutes*60000).getTime() / coeff) * coeff);
}

let period = reactive({
    start: initDate(10),
    end: initDate(40),
});

function resetFields() {
    period.start = initDate(10);
    period.end = initDate(40);

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
            subordinateNo: nomination.subordinateNo || null
        });
    }

    return result;
}

function formatDate(date) {
    return dayJS(date).format('YYYY-MM-DDTHH:mm');
}

function createApplication(data) {
    period.end = formatDate(period.end);
    period.start = formatDate(period.start);
    if (validateApplication(data)) {
        data.selfNominateAll = data.selfNominateAll || nominations.value.filter(nomination => nomination.nomination == "Self Nomination").length == nominations.value.length;
        data.nominations = formatNominations(data.accountNo);
        data.sDate = period.start;
        data.eDate = period.end;
        
        axios.post('/api/createApplication', data)
            .then(res => {
                if (res.status == 200) {
                    let newApp = res.data;
                    addNewApplication(newApp);
                    fetchCalendarData(user.value.accountNo);
                    
                    resetFields();
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully created application.'
                    });
                    
                }
            }).catch(err => {
            Swal.fire({
                icon: "error",
                title: 'Failed to create application.',
                text: err.response.data
            });
        });
    }
    else {
        Swal.fire({
           icon: "error",
           title: "Error",
           text:  errors.join(", ")
        });
    }
}
</script>
<template>
    <div>
        <div v-if="isMobile" class="w-full">
            <div class="w-full rounded-b-md p-2" :class="isDark?'bg-gray-800':'bg-white'">
                <p class="text-xl font-bold">
                    Create New Leave Application:
                </p>
                <div class="">
                    <CreateSubpagePeriod :period="period" />
                    <CreateSubpageNominations
                        @resetFields="resetFields()"
                        @submitApplication="(data) => createApplication(data)"
                        />
                </div>
            </div>
            <CalendarSmall
                class="flex drop-shadow-md mt-2"
                disableEnlarge
            />
        </div>
        <div v-else class="flex subpage-height">
            <div class="w-4/5 1080:w-[85%] 1440:w-5/6 flex flex-col p-4 mr-4 rounded-r-md rounded-bl-md subpage-height" :class="isDark?'bg-gray-800':'bg-white'">
                <p class="text-3xl 1080:text-4xl 1440:text-5xl 4k:text-6xl h-[8%] font-bold">
                    Create New Leave Application:
                </p>
                <div class="flex h-[92%]">
                    <CreateSubpagePeriod :period="period" class="h-full w-1/3" />
                    <CreateSubpageNominations
                        class="w-2/3"
                        @resetFields="resetFields()"
                        @submitApplication="(data) => createApplication(data)"
                        />
                </div>
            </div>
            <CalendarSmall class="w-1/5 1080:w-[15%] 1440:w-1/6 flex flex-col h-full"
                disableEnlarge
            />
        </div>
        <div v-if="isMobile" class="h-2">
        </div>
    </div>
    
</template>
<style>
.subpage-height {
    height: calc(0.95 * 93vh - 3rem);
}
</style>
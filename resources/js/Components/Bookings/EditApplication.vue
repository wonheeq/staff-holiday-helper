<script setup>
import Modal from '../Modal.vue';
import CreateSubpagePeriod from './CreateSubpagePeriod.vue';
import CreateSubpageNominations from './CreateSubpageNominations.vue';
import CalendarSmall from '../CalendarSmall.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { storeToRefs } from 'pinia';
import { useNominationStore } from '@/stores/NominationStore';
import { useApplicationStore } from '@/stores/ApplicationStore';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useCalendarStore } from '@/stores/CalendarStore';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
let calendarStore = useCalendarStore();
const { fetchCalendarData } = calendarStore;
let applicationStore = useApplicationStore();
const { fetchApplications } = applicationStore;
let nominationStore = useNominationStore();
const { nominations, isSelfNominateAll } = storeToRefs(nominationStore);
const page = usePage();
const user = computed(() => page.props.auth.user);

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

function calcDateDiff(d1, d2) {
    return new Date(d1) - new Date(d2);
}

let errors = [];
function validateApplication(data) {
    errors = [];

    // Date is empty
    if (props.period.end == null || props.period.start == null) {
        errors.push("Dates cannot be empty")
    }

    // End date is earlier than start date
    if (props.period.end != null && props.period.start != null && calcDateDiff(props.period.end, props.period.start) < 0) {
        errors.push("End date/time cannot be earlier than start date/time");
    }

    // End date is equal to start date
    if (props.period.end != null && props.period.start != null && props.period.end == props.period.start) {
        errors.push("End date/time cannot be the same as the start date/time");
    }

    // A date is in the past
    let currentDate = new Date();
    if (props.period.end != null && props.period.start != null &&
        (calcDateDiff(props.period.start, currentDate) <= 0 || calcDateDiff(props.period.end, currentDate) <= 0)) {
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

function handleEditApplication(data) {
    if (validateApplication(data)) {
        data.selfNominateAll = data.selfNominateAll || nominations.value.filter(nomination => nomination.nomination == "Self Nomination").length == nominations.value.length;
        data.nominations = formatNominations(data.accountNo);
        data.sDate = props.period.start;
        data.eDate = props.period.end;
        data.applicationNo = props.applicationNo;

        resetFields();
        
        axios.post('/api/editApplication', data)
            .then(res => {
                if (res.status == 200) {
                    Swal.fire({
                        icon: "success",
                        title: 'Successfully edited application.'
                    }).then(() => {
                        fetchApplications(user.value.accountNo);
                        fetchCalendarData(user.value.accountNo);
                        resetFields();
                        emit('close');
                    });
                }
                else {
                    Swal.fire({
                        icon: "error",
                        title: 'Error',
                        text: res.data,
                    });
                }
            }).catch(err =>
            {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:  err
                });
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
</script>
<template>
<Modal>
    <div class="flex flex-col laptop:flex-row bg-transparent w-screen px-2 mt-2 mb-2 laptop:px-4 laptop:mt-auto laptop:mb-4">
        <div v-if="isMobile" class="w-full bg-white p-2 rounded-md">
            <div class="h-[4%] flex justify-between">
                <p class="text-xl font-bold">
                    Edit Leave Application (ID: {{ applicationNo }}):
                </p>
                <button class="h-full"
                    @click="resetFields(); $emit('close')"
                >
                    <img src="/images/close.svg" class="h-full w-full"/>
                </button>
            </div>
            <div>
                <CreateSubpagePeriod :period="props.period" :isEditing="true" class="h-full" />
                <CreateSubpageNominations
                    :isEditing="true"
                    :applicationNo="applicationNo"
                    @resetFields="resetFields()"
                    @submitApplication="(data) => handleEditApplication(data)"
                />
            </div>
            <div class="h-2">
            </div>
        </div>
        <div v-else class="w-[80%] flex flex-col p-4 mr-4 subpage-height rounded-tl-md" :class="subpageClass">
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
                <CreateSubpagePeriod :period="props.period" :isEditing="true" class="h-full" />
                <CreateSubpageNominations
                    :isEditing="true"
                    :applicationNo="applicationNo"
                    @resetFields="resetFields()"
                    @submitApplication="(data) => handleEditApplication(data)"
                    class="col-span-2"
                />
            </div>
        </div>
        <CalendarSmall class="mt-2 laptop:mt-0 laptop:w-[20%] flex flex-col" :disableEnlarge="true"/>
    </div>
</Modal>
</template>
<style>
.subpage-height {
    height: calc(0.95 * (93vh - 3rem));
}
</style>
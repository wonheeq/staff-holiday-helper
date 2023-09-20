<script setup>
import Modal from '@/Components/Modal.vue';
import { ref, watch, computed } from 'vue';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import Swal from 'sweetalert2';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3'
import { useDark } from "@vueuse/core";
const isDark = useDark();
const page = usePage();
const user = computed(() => page.props.auth.user);

const deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
});

let props = defineProps({
    data: Object,
});

let emit = defineEmits(['close']);
let buttonActive = ref(false);
let rejectReason = ref("");

watch(rejectReason, () => {
    if (rejectReason.value !== "") {
        buttonActive.value = true;
    }
    else {
        buttonActive.value = false;
    }
});

// Disable submit button and close modal
function handleClose() {
    buttonActive.value = false;
    rejectReason.value = "";
    emit('close');
}

function handleSelection(selection) {
    rejectReason.value = selection;
    buttonActive.value = true;
}

function handleApproveApp() {
    let data = {
        'accountNo': user.value.accountNo,
        'applicationNo': props.data.applicationNo,
    };   
    axios.post('/api/acceptApplication', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to approve application, please try again.',
                    text: res.data.error
                });
                console.log(res);
            }
            else {
                if(props.data.message != null){
                    props.data.message.acknowledged = 1;
                    props.data.message.updated_at = new Date();
                }
                
                Swal.fire({
                    icon: "success",
                    title: 'Successfully approved the application.',
                }).then(() => {
                    handleClose();
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to approve application, please try again.',
        });
    });
}

function handleRejectApp() {
    let data = {
        'accountNo': user.value.accountNo,
        'applicationNo': props.data.applicationNo,
        'rejectReason': rejectReason.value
    };   
    axios.post('/api/rejectApplication', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to reject application, please try again.',
                    text: res.data.error
                });
                console.log(res);
            }
            else {
                if(props.data.message != null){
                    props.data.message.acknowledged = 1;
                    props.data.message.updated_at = new Date();
                }
                Swal.fire({
                    icon: "success",
                    title: 'Successfully rejected the application.',
                }).then(() => {
                    handleClose();
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to reject application, please try again.',
        });
    });
}

const options = [
    "Not enough leave remaining.",
    "Application was not made with enough notice.",
    "Leave period is a public holiday."
];

const disabledClass = "p-4 w-1/3 rounded-md text-white text-2xl font-bold bg-gray-300";
const buttonClass = "p-4 w-1/3 rounded-md text-white text-2xl font-bold";
</script>

<template>
<Modal>
    <div class="w-3/5 1080:w-1/2 1440:w-2/6 h-[32rem] 1080:h-[48rem] rounded-md p-4" :class="isDark?'bg-gray-800':'bg-white'" v-if="props.data">
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-2xl 1080:text-3xl 4k:text-5xl font-bold" :class="isDark?'text-white':''">
                Reviewing Application by {{ props.data.applicantName }}
            </p>
            <button @click="handleClose()">
                    <img src="/images/close.svg"
                    class="close-button h-full"
                    :class="isDark?'darkModeImage':''"
                />
            </button>
        </div>
        <div class="flex h-[6%] items-center justify-between">
            <p class="text-xl 1080:text-2xl 4k:text-3xl" :class="isDark?'text-white':''">
                Duration: {{ props.data.duration }}
            </p>
        </div>
        <div class="h-[45%] py-4">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div class="mb-2"
                        v-for="nomination in props.data.nominations" :key="nomination.id">
                        <p v-if="nomination.nomineeNo !== props.data.applicantNo"
                            class="text-lg 1080:text-xl 4k:text-2xl"
                            :class="isDark?'text-white':''"
                        >
                            {{ nomination.nomineeName }}
                        </p>
                        <p v-if="nomination.nomineeNo == props.data.applicantNo"
                            class="text-lg 1080:text-xl 4k:text-2xl"
                            :class="isDark?'text-white':''"
                        >
                            Self-Nomination
                        </p>
                        <div>
                            <p v-for="role in nomination.roles" :class="isDark?'text-white':''">
                                →{{ role }}
                            </p>
                        </div>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div class="h-[29%] py-4">
            <p class="h-[20%] text-lg 1080:text-xl 4k:text-2xl" :class="isDark?'text-white':''">
                Select Reject Reason or Enter Custom Message:
            </p>
            <input type="text" v-model="rejectReason" class="h-[40%] w-full border-gray-300 border-2 rounded-md p-2" :class="isDark?'text-white bg-gray-800':''"/>
            <vSelect :options="options" :clearable="false"
                style="width: 100%; height: 2rem; background-color: white; 
                border: solid; border-color: #6b7280; border-width: 1px;
                --vs-border-style: none; --vs-search-input-placeholder-color: #6b7280"                                 
                v-model="multiSelectNominee"
                @option:selected="(selectedOption) => handleSelection(selectedOption)"
                :isDisabled="selfNominateAll"
            />
        </div>
        <div class="h-[10%] flex justify-between">
            <button :class="buttonClass" class="bg-green-500" @click="handleApproveApp()">
                Approve
            </button>
            <button :class="rejectReason=='' ? disabledClass : buttonClass + ' bg-red-500'"
                :disabled="!buttonActive"
                @click="handleRejectApp()"
            >
                Reject
            </button>
        </div>
    </div>
</Modal>
</template>

<style>
.darkModeImage {
    filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(0deg) brightness(95%) contrast(100%);
}
.close-button {
    height: 70px;
    width: auto;
}
/* 1080p */
@media 
(min-width: 1920px) {
    .close-button {
        height: 70px;
        width: auto;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .close-button {
        height: 80px;
        width: auto;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .close-button {
        height: 110px;
        width: auto;
    }
}
</style>
<script setup>
import ApplicationInfoOptions from './ApplicationInfoOptions.vue';
import ApplicationNominationData from './ApplicationNominationData.vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3'
const page = usePage();
const user = computed(() => page.props.auth.user);
let props = defineProps({ source: Object });
let emit = defineEmits(['cancelApplication', 'editApplication']);
const statusText = {
    "P": "Pending",
    "U": "Undecided",
    "Y": "Approved",
    "N": "Denied",
    "C": "Cancelled",
};
const statusColour = {
    "P": "text-orange-500",
    "U": "text-blue-500",
    "Y": "text-green-500",
    "N": "text-red-500",
    "C": "text-gray-500",
};

let toggleContent = ref(false);
let toggleImage = (isVisible) => {
    if (isVisible) {
        return '/images/triangle_up.svg';
    }

    return '/images/triangle_down.svg';
}

function alertFailedCancelApplication() {
    Swal.fire({
        title: "Failed to Cancel Application",
        text: "Please try again later.",
    });
}

async function handleCancelApplication() {
    Swal.fire({
        title: "Cancel Application",
        text: "Are you sure you want to cancel this application?",
        showCancelButton: true,
        confirmButtonText: "Yes, cancel it",
        cancelButtonText: "No, do not cancel it",
    }).then((result) => {
        if (result.isConfirmed) {
            axios.get("/api/cancelApplication/" + user.value.accountNo + "/" + props.source.applicationNo)
            .then((response) => {
                if (response.status == 200) {
                    emit('cancelApplication');
                }
                else {
                    alertFailedCancelApplication();
                }
            })
            .catch((error) => {
                console.log(error);
                alertFailedCancelApplication();
            });
        }
    });
}

function handleEditApplication() {
    emit('editApplication');
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
    <div v-if="isMobile()" class="flex flex-col">
        <div class="flex flex-col bg-gray-200 p-2">
            <p class="text-base font-bold">{{ source.sDate }} - {{ source.eDate }}</p>
            <p :class="statusColour[source.status]">
                {{ statusText[source.status] }}
            </p>
            <ApplicationNominationData
                v-show="toggleContent"
                :nominations="source.nominations"
                :appStatus="source.status"
                :rejectReason="source.rejectReason"
            />
            <div v-show="toggleContent">
                <div class="flex flex-row text-sm">
                    <p class="text-sm font-medium mr-2">Application ID:</p>
                    {{ source.applicationNo }}
                </div>                
                <div>
                    <p class="text-sm font-medium">Substitute/s:</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                        <div class="text-sm" v-if="nomination.nomineeNo != user.accountNo">
                            → {{ nomination.name }} - [{{ nomination.nomineeNo }}@curtin.edu.au]
                            <p class="ml-5">{{ nomination.task }}</p>
                        </div>
                        <div class="text-sm" v-if="nomination.nomineeNo == user.accountNo">
                            → Self Nominated    {{ nomination.task }}
                        </div>
                    </div>
                    <p class="text-sm" v-if="source.isSelfNominatedAll">
                        → N/A - Self nominated for all roles
                    </p>
                </div>
                <div class="flex flex-row text-sm">
                    <p class="text-sm font-medium mr-2">Last Edited:</p>
                    {{ new Date(source.updated_at).toLocaleString() }}
                </div>
            </div>
        </div>
        <div class="flex flex-row bg-white space-x-0.5">
            <button @click="handleCancelApplication()" v-if="source.status!='C'" class="mt-0.5 bg-gray-200">
                <img class="h-6 w-8" src="/images/delete.svg" />
            </button>
            <button
                class="mt-0.5 bg-gray-200 text-center h-8 w-full"
                @click="toggleContent=!toggleContent"
            >
                <img :src="toggleImage(toggleContent)" class="toggleImageIcon"/>
            </button>
            <button @click="handleEditApplication()" class="mt-0.5 bg-gray-200">
                <img class="h-6 w-8" src="/images/edit.svg" />
            </button>
        </div>
    </div>
    <div v-else class="flex flex-row bg-white">
        <div class="flex flex-col w-5/6 bg-gray-200 p-2">
            <p class="text-xl font-bold">{{ source.sDate }} - {{ source.eDate }}</p>
            <div v-show="toggleContent">
                <div class="flex flex-row">
                    <p class="font-medium mr-2">Application ID:</p>
                    {{ source.applicationNo }}
                </div>
                <div>
                    <p class="font-medium">Substitute/s:</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomination in source.nominations">
                        <p v-if="nomination.nomineeNo != user.accountNo">
                            → {{ nomination.name }} - [{{ nomination.nomineeNo }}@curtin.edu.au]    {{ nomination.task }}
                        </p>
                        <p v-if="nomination.nomineeNo == user.accountNo">
                            → Self Nominated    {{ nomination.task }}
                        </p>
                    </div>
                    <p v-if="source.isSelfNominatedAll">
                        → N/A - Self nominated for all roles
                    </p>
                </div>
                <div class="flex flex-row">
                    <p class="font-medium mr-2">Last Edited:</p>
                    {{ new Date(source.updated_at).toLocaleString() }}
                </div>
            </div>
        </div>
        <div class="flex flex-col w-1/5 bg-gray-200 text-4xl ml-2 p-2">
            <p :class="statusColour[source.status]">
                {{ statusText[source.status] }}
            </p>
            <ApplicationNominationData
                v-show="toggleContent"
                :nominations="source.nominations"
                :appStatus="source.status"
                :rejectReason="source.rejectReason"
            />
            <ApplicationInfoOptions
                class="flex"
                v-show="toggleContent"
                :status="source.status"
                @cancelApplication="handleCancelApplication()"
                @editApplication="handleEditApplication()"
            />
        </div>
        <div class="flex flex-col bg-white">
            <button
                class="ml-2 text-5xl px-6 bg-gray-200 text-center h-14"
                @click="toggleContent=!toggleContent"
            >
                <img :src="toggleImage(toggleContent)" class="toggleImageIcon"/>
            </button>
        </div>
    </div>
</template>

<style>
.toggleImageIcon{
    width: 100%;
    height: 100%;
}
</style>
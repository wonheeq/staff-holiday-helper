<script setup>
import ApplicationInfoOptions from './ApplicationInfoOptions.vue';
import ApplicationNominationData from './ApplicationNominationData.vue';
import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/UserStore';
import Swal from 'sweetalert2';
import axios from 'axios';
let userStore = useUserStore();
const { userId } = storeToRefs(userStore);
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
            axios.get("/api/cancelApplication/" + userId.value + "/" + props.source.applicationNo)
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

</script>
<template>
    <div class="flex flex-row bg-white mr-4">
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
                        <p v-if="nomination.nomineeNo != userId">
                            → {{ nomination.name }} - [{{ nomination.accountNo }}@curtin.edu.au]    {{ nomination.task }}
                        </p>
                        <p v-if="nomination.nomineeNo == userId">
                            → Self Nominated    {{ nomination.task }}
                        </p>
                    </div>
                    <p v-if="source.isSelfNominatedAll">
                        → N/A - Self nominated for all roles
                    </p>
                </div>
                <div class="flex flex-row">
                    <p class="font-medium mr-2">Application Submitted:</p>
                    {{ new Date(source.created_at).toLocaleString() }}
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
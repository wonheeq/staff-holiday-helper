<script setup>
import ApplicationInfoOptions from './ApplicationInfoOptions.vue';
import ApplicationNominationData from './ApplicationNominationData.vue';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { useDark } from "@vueuse/core";
const isDark = useDark();
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
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
    "E": "Expired",
};
const statusColour = {
    "P": "text-orange-500",
    "U": "text-blue-500",
    "Y": "text-green-500",
    "N": "text-red-500",
    "C": "text-gray-500",
    "E": "",
};

let toggleContent = ref(false);
let toggleImage = (isVisible) => {
    if (isVisible) {
        return '/images/triangle_up.svg';
    }

    return '/images/triangle_down.svg';
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
            })
            .catch((error) => {
                console.log(error);
                Swal.fire({
                    icon: 'error',
                    title: "Failed to Cancel Application",
                    text: error.response.data
                });
            });
        }
    });
}

function handleEditApplication() {
    emit('editApplication');
}
</script>
<template>
    <div v-if="isMobile" class="flex flex-col">
        <div class="flex flex-col p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
            <p class="text-base font-bold">{{ source.sDate }} - {{ source.eDate }}</p>
            <p :class="statusColour[source.status]">
                {{ statusText[source.status] }}
            </p>
            <ApplicationNominationData
                v-show="toggleContent"
                :nominations="source.nominations"
                :appStatus="source.status"
                :rejectReason="source.rejectReason"
                :processedBy="source.processedBy"
            />
            <div v-show="toggleContent">
                <p class="text-sm font-medium">Application ID: {{ source.applicationNo }}</p>
                <div>
                    <p class="text-sm font-medium">Substitute/s:</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomineeArray in source.nominationsToDisplay">
                        <p class="text-xs laptop:text-base" v-if="nomineeArray.nomineeNo != user.accountNo">
                            • {{ nomineeArray.nomineeName }} - {{ nomineeArray.nomineeNo }}@curtin.edu.au
                        </p>
                        <p v-else class="text-xs laptop:text-base">
                            • Self Nomination
                        </p>
                        <div v-for="task in nomineeArray.tasks">
                            <p class="text-xs laptop:text-base">
                                →{{ task }}
                            </p>
                        </div>
                    </div>
                    <p v-else>
                        • Self nominated for all roles
                    </p>
                </div>
                <div class="flex flex-row text-sm">
                    <p class="text-sm font-medium mr-2">Last Edited:</p>
                    {{ new Date(source.updated_at).toLocaleString() }}
                </div>
            </div>
        </div>
        <div class="flex flex-row space-x-0.5">
            <button @click="handleCancelApplication()" v-if="source.status!='C'" class="mt-0.5" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <img class="h-6 w-8" src="/images/delete.svg" :class="isDark?'darkModeImage':''"/>
            </button>
            <button
                class="mt-0.5 text-center h-8 w-full" :class="isDark?'bg-gray-700':'bg-gray-200'"
                @click="toggleContent=!toggleContent"
            >
                <img :src="toggleImage(toggleContent)" class="toggleImageIcon" :class="isDark?'darkModeImage':''"/>
            </button>
            <button @click="handleEditApplication()" class="mt-0.5" :class="isDark?'bg-gray-700':'bg-gray-200'">
                <img class="h-6 w-8" src="/images/edit.svg" :class="isDark?'darkModeImage':''"/>
            </button>
        </div>
    </div>
    <div v-else class="flex flex-row">
        <div class="flex flex-col w-5/6 p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
            <p class="text-xl font-bold">{{ source.sDate }} - {{ source.eDate }}</p>
            <div v-show="toggleContent">
                <p class="font-medium text-lg">Application ID: {{ source.applicationNo }}</p>
                <div>
                    <p class="font-medium text-lg">Substitute/s:</p>
                    <div v-if="!source.isSelfNominatedAll" v-for="nomineeArray in source.nominationsToDisplay">
                        <p class="text-xs laptop:text-base" v-if="nomineeArray.nomineeNo != user.accountNo">
                            • {{ nomineeArray.nomineeName }} - {{ nomineeArray.nomineeNo }}@curtin.edu.au
                        </p>
                        <p v-else class="text-xs laptop:text-base">
                            • Self Nomination
                        </p>
                        <div v-for="task in nomineeArray.tasks">
                            <p class="text-xs laptop:text-base">
                                →{{ task }}
                            </p>
                        </div>
                    </div>
                    <p v-else>
                        • Self nominated for all roles
                    </p>
                </div>
                <div class="flex flex-row">
                    <p class="font-medium mr-2">Last Edited:</p>
                    {{ new Date(source.updated_at).toLocaleString() }}
                </div>
            </div>
        </div>
        <div class="flex flex-col w-1/5 text-4xl ml-2 p-2"  :class="isDark?'bg-gray-700':'bg-gray-200'">
            <p :class="statusColour[source.status]">
                {{ statusText[source.status] }}
            </p>
            <ApplicationNominationData
                v-show="toggleContent"
                :nominations="source.nominations"
                :appStatus="source.status"
                :rejectReason="source.rejectReason"
                :processedBy="source.processedBy"
            />
            <ApplicationInfoOptions
                class="flex"
                v-show="toggleContent"
                :status="source.status"
                @cancelApplication="handleCancelApplication()"
                @editApplication="handleEditApplication()"
            />
        </div>
        <div class="flex flex-col">
            <button
                class="ml-2 text-5xl px-6 text-center h-14"
                :class="isDark?'bg-gray-700':'bg-gray-200'"
                @click="toggleContent=!toggleContent"
            >
                <img :src="toggleImage(toggleContent)" class="toggleImageIcon" :class="isDark?'darkModeImage':''"/>
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
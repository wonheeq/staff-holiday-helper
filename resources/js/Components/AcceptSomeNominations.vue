<script setup>
import Modal from './Modal.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import Swal from 'sweetalert2';
import axios from 'axios';
import AcceptSomeNominationOptions from './AcceptSomeNominationOptions.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useMessageStore } from "@/stores/MessageStore";
const messageStore = useMessageStore();
const { fetchMessages } = messageStore;
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { useDark } from "@vueuse/core";
const isDark = useDark();
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const page = usePage();
const user = computed(() => page.props.auth.user);
let emit = defineEmits(['close']);
let props = defineProps({
    data: Object,
    roles: Object
});
const deadAreaColor = computed(() => {
    return isDark.value ? '#1f2937': '#FFFFFF';
})

let buttonActive = ref(false);

/*
Changes the status of a nomination to 'Y' or 'N'.
Checks if all nominations have been responded to and if so, enables the submit button.
*/
function handleStatusChangedForRole(role, status) {
    role.status = status;
    if (props.roles.filter(r => r.status !== 'U').length == props.roles.length) {
        buttonActive.value = true;
    }
    else {
        buttonActive.value = false;
    }
}

/*
Handles submission of data to the backend for the acceptSomeNominations functionality
*/
function submitResponses() {
    // format data for api request
    let responses = [];
    for (let role of props.roles) {
        responses.push({
            "accountRoleId": role.accountRoleId,
            "status": role.status
        });
    }

    let data = {
        'messageId': props.data.messageId,
        'accountNo': user.value.accountNo,
        'applicationNo': props.data.applicationNo,
        'responses': responses
    };   

    axios.post('/api/acceptSomeNominations', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to respond to nominations, please try again.',
                    text: res.data.error
                });
                console.log(res);
            }
            else {
                props.data.acknowledged = 1;
                props.data.updated_at = new Date();
                fetchMessages(user.value.accountNo);
                Swal.fire({
                    icon: "success",
                    title: 'Successfully responded to the nominations.',
                }).then(() => {
                    handleClose();
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to respond to nominations, please try again.',
        });
    });
}

// Disable submit button and close modal
function handleClose() {
    buttonActive.value = false;
    emit('close');
}
</script>
<template>
<Modal>
    <div v-if="isMobile" class="h-fit rounded-md p-2 mx-2" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-xl font-bold" :class="isDark?'text-white':''">
                <!-- Filter for content element that contains 'Duration' and get the first element
                    Assumes that there is Duration in one of the content elements    
                -->
                {{ props.data.content && JSON.parse(props.data.content).filter(content => content.includes('Duration:'))[0] }}
            </p>
            <button @click="handleClose()">
                    <img src="/images/close.svg"
                    class="close-button h-full"
                    :class="isDark?'darkModeImage':''"
                />
            </button>
        </div>
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-lg " :class="isDark?'text-white':''">
                You have been nominated for the following roles by {{ props.data.senderName }}:
            </p>
        </div>
        <div class="h-[70%] py-4">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div class="flex mb-2 items-center h-[5rem] space-x-1 justify-between mr-4"
                        v-for="role in props.roles" :key="role.id">
                        <p class="w-4/5 border-b h-full" :class="isDark?'text-white':''">
                            {{ role.roleName }}
                        </p>
                        <AcceptSomeNominationOptions
                            class="w-1/5 h-full"
                            @statusUpdated="(status) => handleStatusChangedForRole(role, status)"
                        />
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div class="h-[10%]">
            <button
                class="w-full h-full p-2  rounded-md text-lg font-bold"
                :class="{
                    'bg-blue-300': buttonActive && !isDark,
                    'bg-gray-300': !buttonActive && !isDark,
                    'bg-blue-800 text-white': buttonActive && isDark,
                    'bg-gray-900 text-white': !buttonActive && isDark,
                }"
                :disabled="!buttonActive"
                @click="submitResponses()"
            >
                Submit Responses
            </button>
        </div>
    </div>
    <div v-else class="w-3/5 1080:w-1/2 1440:w-2/6 h-[32rem] 1080:h-[48rem] rounded-md p-4" :class="isDark?'bg-gray-800':'bg-white'">
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-2xl 1080:text-3xl 4k:text-5xl font-bold" :class="isDark?'text-white':''">
                <!-- Filter for content element that contains 'Duration' and get the first element
                    Assumes that there is Duration in one of the content elements    
                -->
                {{ props.data.content && JSON.parse(props.data.content).filter(content => content.includes('Duration:'))[0] }}
            </p>
            <button @click="handleClose()">
                    <img src="/images/close.svg"
                    class="close-button h-full"
                    :class="isDark?'darkModeImage':''"
                />
            </button>
        </div>
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-xl 1080:text-2xl 4k:text-3xl" :class="isDark?'text-white':''">
                You have been nominated for the following roles by {{ props.data.senderName }}:
            </p>
        </div>
        <div class="h-[70%] py-4">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div class="flex mb-2 items-center space-x-2 justify-between mr-4"
                        v-for="role in props.roles" :key="role.id">
                        <p class="1080:text-lg 4k:text-2xl" :class="isDark?'text-white':''">
                            {{ role.roleName }}
                        </p>
                        <AcceptSomeNominationOptions
                            @statusUpdated="(status) => handleStatusChangedForRole(role, status)"
                        />
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div class="h-[10%]">
            <button
                class="w-full h-full p-2 1080:p-4 rounded-md text-xl 1080:text-4xl font-bold"
                :class="{
                    'bg-blue-300': buttonActive && !isDark,
                    'bg-gray-300': !buttonActive && !isDark,
                    'bg-blue-800 text-white': buttonActive && isDark,
                    'bg-gray-900 text-white': !buttonActive && isDark,
                }"
                :disabled="!buttonActive"
                @click="submitResponses()"
            >
                Submit Responses
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
    height: 50px;
    width: auto;
}
/* laptop */
@media 
(min-width: 1360) {
    .close-button {
        height: 50px;
        width: auto;
    }
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
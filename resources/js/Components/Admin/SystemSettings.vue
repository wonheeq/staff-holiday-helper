<script setup>
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import axios from "axios";
import Swal from "sweetalert2";
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useDark } from "@vueuse/core";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();

const page = usePage();
const user = computed(() => page.props.auth.user);
const MAX_SYSTEM_NOTIFICATION_LENGTH = 300;
const options = {
    default: "2 days",
    all: [
        "1 day",
        "2 days",
        "3 days",
        "4 days",
        "5 days",
        "6 days",
        "1 week"
    ]
}
let oldReminderTimeframe = ref(options.default);
let reminderTimeframe = ref(options.default);
let systemNotificationContent = ref("");

let showReminderApplyButton = ref(false);
let showSystemNotificationButton = ref(false);

watch(reminderTimeframe, () => {
    if (reminderTimeframe.value != oldReminderTimeframe.value) {
        showReminderApplyButton.value = true;
    }
    else {
        showReminderApplyButton.value = false;
    }
});

function changeReminderTimeframe() {
    showReminderApplyButton.value = false;
    oldReminderTimeframe.value = reminderTimeframe.value;

    let data = {
        'timeframe': reminderTimeframe.value,
        'accountNo': user.value.accountNo,
    };

    axios.post('/api/setReminderTimeframe', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to change reminder timeframe, please try again',
                });
            }
            else {
                Swal.fire({
                    icon: "success",
                    title: 'Successfully changed reminder timeframe',
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to change reminder timeframe, please try again',
        });
    });
}

watch(systemNotificationContent, () => {
    if (systemNotificationContent.value.length > 0 && systemNotificationContent.value.length <= MAX_SYSTEM_NOTIFICATION_LENGTH) {
        showSystemNotificationButton.value = true;
    }
    else {
        showSystemNotificationButton.value = false;
    }
});

function createSystemNotification() {
    let data = {
        'content': systemNotificationContent.value,
        'accountNo': user.value.accountNo,
    };

    systemNotificationContent.value = "";
    showSystemNotificationButton.value = false;

    axios.post('/api/createSystemNotification', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to create notification, please try again',
                });
            }
            else {
                Swal.fire({
                    icon: "success",
                    title: 'Successfully created notification',
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to create notification, please try again',
        });
    });
}

axios.get('/api/getReminderTimeframe/' + user.value.accountNo)
.then(res => {
    if (res.status == 200) {
        reminderTimeframe.value = res.data;
        oldReminderTimeframe.value = res.data;
    }
    else {
        console.log("Failed to getReminderTimeframe");
    }
}).catch(err => {
    console.log(err);
});
</script>
<template>
    <div class="space-y-4">
        <div>
            <div class="flex flex-row h-[2rem] space-x-4 items-center 4k:mt-5 4k:ml-5">
                <p class="text-2xl h-full 4k:text-3xl">
                    Reminder Timeframe:
                </p>
                <vSelect :options="options.all" :clearable="false"
                    class="timeframe_options" 
                    :class="isDark ? 'dropdown-dark':''"
                    v-model="reminderTimeframe"
                />
                <button v-show="showReminderApplyButton"
                    :class="{
                        'h-full px-4 border-black border rounded-md bg-blue-200 font-bold 4k:text-2xl': !isDark,
                        'h-full px-4 border-white border rounded-md bg-gray-600 font-bold 4k:text-2xl': isDark,
                    }"
                    @click="changeReminderTimeframe"
                >
                    Apply Change
                </button>
            </div>
            <p class="4k:text-2xl 4k:mt-7 4k:ml-5">
                The amount of time after being nominated that a nominee will receive a reminder if they have not responded.
            </p>
        </div>
        <div class="4k:ml-5">
            <p class="text-2xl 4k:text-3xl 4k:mt-9">
                Create System Notification
            </p>
            <p class="4k:text-2xl 4k:mt-2">
                This will send a message to <b>all</b> accounts.
            </p>
            <div class="w-[48.5rem] h-32 relative 4k:w-[74rem] 4k:h-[23rem] 4k:mt-5">
                <textarea
                    class="w-full h-full 4k:text-2xl"
                    :class="isDark?'bg-gray-600':''"
                    v-model="systemNotificationContent">
                </textarea>
                <p class="absolute right-1 bottom-0 4k:text-xl"
                    v-show="systemNotificationContent.length > 0"
                    :class="systemNotificationContent.length > MAX_SYSTEM_NOTIFICATION_LENGTH ? 'text-red-600': ''"
                >
                    {{ MAX_SYSTEM_NOTIFICATION_LENGTH - systemNotificationContent.length }}
                </p>
            </div>
            <button v-show="showSystemNotificationButton"
                :class="{
                    'h-full px-4 border-black border rounded-md bg-blue-200 font-bold 4k:text-2xl': !isDark,
                    'h-full px-4 border-white border rounded-md bg-gray-600 font-bold 4k:text-2xl': isDark,
                }"
                class="py-2 mt-2 4k:mt-5"
                @click="createSystemNotification"    
            >
                Create Notification
            </button>
        </div>
    </div>
</template>
<style>
    textarea {
    resize: none;
    overflow: hidden;
    }
</style>

<style lang="postcss">

    .timeframe_options {
        width: 33rem; 
        height: 2rem; 
        @apply 4k:text-2xl 4k:h-11 4k:w-drpdwn 4k:mt-2 !important;
    }

</style>
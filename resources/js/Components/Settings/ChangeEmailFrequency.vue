<script setup>
import { ref, watch, reactive, computed } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useDark } from '@vueuse/core';
const isDark = useDark();
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import axios from "axios";
import Swal from "sweetalert2";
let emit = defineEmits(['close-settings', 'close-email']);
const page = usePage();
const user = computed(() => page.props.auth.user);
let errors = reactive([]);
const options = {
    default: "Daily",
    all: [
        "Hourly",
        "Twice a day",
        "Daily",
        "Every 2 days",
        "Every 3 days",
        "Every 4 days",
        "Every 5 days",
        "Every 6 days",
        "Once a week"
    ]
}

let oldReminderTimeframe = ref(options.default);
let reminderTimeframe = ref(options.default);


let showReminderApplyButton = ref(false);
let displaySuccess = ref(false);

watch(reminderTimeframe, () => {
    if (reminderTimeframe.value != oldReminderTimeframe.value) {
        showReminderApplyButton.value = true;
    }
    else {
        showReminderApplyButton.value = false;
    }
});

async function handleChangePreference() {
    displaySuccess = false;
    await axios.post("api/setEmailPreference", {
        accountNo: user.value.accountNo,
        frequency: reminderTimeframe.value
    });
}

axios.post('/api/getEmailFrequency/', {
    accountNo: user.value.accountNo
})
.then(res => {
    if (res.status == 200) {
        reminderTimeframe.value = res.data;
        oldReminderTimeframe.value = res.data;
    }
    else {
        console.log("Failed to getEmailFrequency");
    }
}).catch(err => {
    console.log(err);
});
</script>
<template>
<div>
    <div class="flex flex-row items-center justify-between">
        <button @click="emit('close-email');">
            <img src="/images/back.svg"
                class="close-button p-4"
                :class="isDark?'darkModeImage':''"
            />
        </button>
        <p class="text-xl 1080:text-3xl 1440:text-4xl 4k:text-5xl font-bold">
            Email Frequency
        </p>
        <button @click="emit('close-settings');">
            <img src="/images/close.svg"
                class="close-button p-4"
                :class="isDark?'darkModeImage':''"
            />
        </button>
    </div>
    <div class="pr-2 pt-2 1440:pr-4 1440:pt-4 flex flex-col ">
        <div class="text-base 1080:lg 1440:text-xl 4k:text-2xl">
            <p>Change how often you recieve your daily reminder email here.</p>
        </div>
        <div class="text-base 1080:lg 1440:text-xl 4k:text-2xl">
            <p>By default, you'll recieve an email once a day if you have unacknowledged messages.</p>
        </div>
        <div class="pt-8 pb-2 text-lg 1080:xl 1440:text-2xl 4k:text-4xl">
            <p >Email Frequency:</p>
        </div>
        <div class="flex flex-row h-fit space-x-4 items-center">
            <div class="w-[75%]">
            <vSelect :options="options.all" :clearable="false"
                style="width:100%; height: fit-content; background-color: white;
                border: solid; border-color: #6b7280; border-width: 1px;
                --vs-border-style: none; --vs-search-input-placeholder-color: #6b7280"
                v-model="reminderTimeframe"
            />
            </div>
            <div class="w-[25%]">
                <button
                    class="h-fit w-full p-1 border-black border rounded-md bg-blue-200 font-bold"
                    v-show="showReminderApplyButton"
                    @click="handleChangePreference()"
                >
                    Apply Change
                </button>
            </div>
        </div>
        <div class="flex justify-center mb-2 mt-2 text-red-500 4k:text-xl text-center">
            <ul>
                <li v-for="error in errors.slice(0, 1)">
                    {{ error }}
                </li>
            </ul>
        </div>
    </div>
</div>
</template>
<style>
.darkModeImage {
    filter: invert(100%) sepia(100%) saturate(0%) hue-rotate(0deg) brightness(95%) contrast(100%);
}
.close-button {
    height: 40px;
    width: auto;
}
/* 1080p */
@media
(min-width: 1920px) {
    .close-button {
        height: 56px;
        width: auto;
    }
}
/* 1440p */
@media
(min-width: 2560px) {
    .close-button {
        height: 60px;
        width: auto;
    }
}
/* 2160p */
@media
(min-width: 3840px) {
    .close-button {
        height: 80px;
        width: auto;
    }
}
</style>

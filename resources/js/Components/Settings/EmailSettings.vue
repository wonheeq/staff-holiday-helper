<script setup>
import { ref, watch, reactive, computed } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { useDark } from '@vueuse/core';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { storeToRefs } from 'pinia';
import { useEmailFrequencyStore } from '@/stores/EmailFrequencyStore';
const emailFrequencyStore = useEmailFrequencyStore();
const { setFrequency } = emailFrequencyStore;
const { frequency } = storeToRefs(emailFrequencyStore);
const isDark = useDark();
let emit = defineEmits(['close-settings', 'close-email']);
const page = usePage();
const user = computed(() => page.props.auth.user);
let errors = reactive([]);
const options = [
    "Instantly",
    "Hourly",
    "Twice a day",
    "Daily",
    "Every 2 days",
    "Every 3 days",
    "Every 4 days",
    "Every 5 days",
    "Every 6 days",
    "Once a week"
];


let newFrequency = ref(frequency.value);


let showReminderApplyButton = ref(false);
let displaySuccess = ref(false);

watch(newFrequency, () => {
    displaySuccess.value = false;
    if (newFrequency.value != frequency.value) {
        showReminderApplyButton.value = true;
    }
    else {
        showReminderApplyButton.value = false;
    }
});

async function handleChangePreference() {
    displaySuccess.value = false;
    errors.length = 0;
    let success = await setFrequency(user.value.accountNo, newFrequency.value);
    if (success) {
        displaySuccess.value = true;
        showReminderApplyButton.value = false;
    }
}
</script>
<template>
<div>
    <div class="flex flex-row items-center justify-between">
        <button @click="emit('close-email');">
            <img src="/images/back.svg"
                class="close-button p-2"
                :class="isDark?'darkModeImage':''"
            />
        </button>
        <p class="text-xl 1080:text-3xl 1440:text-4xl 4k:text-5xl font-bold">
            Email Settings
        </p>
        <button @click="emit('close-settings');">
            <img src="/images/close.svg"
                class="close-button p-2"
                :class="isDark?'darkModeImage':''"
            />
        </button>
    </div>
    <div class="pr-2 pt-2 1440:pr-4 1440:pt-4 flex flex-col ">
        <div class="text-base 1080:lg 1440:text-xl 4k:text-2xl">
            <p>Change how often you recieve emails here.</p>
        </div>
        <div class="text-base 1080:lg 1440:text-xl 4k:text-2xl">
            <p>By default, you'll recieve an email once a day if you have unacknowledged messages.</p>
            <p>Picking "Instantly" means you will immediately recieve emails for events, such as applications being accepted, or being nominated for a role.</p>
        </div>
        <div class="pt-8 pb-2 text-lg 1080:xl 1440:text-2xl 4k:text-4xl">
            <p >Email Frequency:</p>
        </div>
        <div class="flex flex-row h-fit space-x-4 items-center">
            <div class="w-[75%]">
                <vSelect :options="options" :clearable="false" :class="isDark ? 'dropdown-dark':''"
                        style="width: 100%; height: 2rem; background-color: inherit;"
                v-model="newFrequency"

                />

            </div>
            <div class="w-[25%]">
                <button
                    class="h-fit w-full p-1 rounded-md font-bold"
                    :class="{
                        'bg-blue-300': !isDark,
                        'bg-blue-800 text-white': isDark,
                    }"
                    v-show="showReminderApplyButton"
                    @click="handleChangePreference()"
                >
                    Apply
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
        <p class="text-xs 1080:text-sm 4k:text-xl w-full text-center mt-2 1440:mt-4 p-4 border border-black rounded-md font-bold"
                :class="isDark?'bg-cyan-600 text-blue-200':'bg-cyan-100 text-blue-800'"
                v-show="displaySuccess"
            >
                Your preference has been changed successfully!
            </p>
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

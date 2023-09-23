<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import ChangePassword from './ChangePassword.vue';
import { useDark, useToggle } from '@vueuse/core';
const isDark = useDark();
const toggleDark = useToggle(isDark);
let emit = defineEmits(['close-settings']);

let activeScreen = ref("");
function resetView() {
    activeScreen.value = '';
    emit('close-settings');
}

const options = [
    {
        screen: 'changePassword',
        label: 'Change Password'
    },
    {
        screen: '',
        label: "Toggle Theme",
        click: function() {
            toggleDark();
        },
    },
];
</script>
<template>
    <Modal>
        <div class="w-1/5 min-w-[320px] 1080:min-w-[420px] h-fit min-h-[24rem] rounded-md drop-shadow-md pl-2 pb-2 1440:pl-4 1440:pb-4" :class="isDark?'bg-gray-800':'bg-white'">
            <div v-if="activeScreen==''" class="flex flex-row items-center justify-between">
                <button :disabled="true">
                    <img src="/images/back.svg"
                        class="close-button p-4 invisible"
                    />
                </button>
                <p class="text-xl 1080:text-3xl 1440:text-4xl 4k:text-5xl font-bold">
                    Settings
                </p>
                <button @click="resetView();">
                    <img src="/images/close.svg"
                        class="close-button p-4"
                        :class="isDark?'darkModeImage':''"
                    />
                </button>
            </div>
            <div class="flex flex-col space-y-2 mr-2" v-if="activeScreen == ''">
                <div class="rounded-md border py-2"
                    :class="isDark?'bg-gray-700 border-gray-600':'border-black'"
                    v-for="option in options">
                    <button v-if="option.click == null" class="text-lg 1080:text-xl 1440:text-2xl 4k:text-3xl w-full"
                        @click="activeScreen=option.screen">
                        {{ option.label }}
                    </button>
                    <button v-else class="text-lg 1080:text-xl 1440:text-2xl 4k:text-3xl w-full"
                        @click="option.click()">
                        {{ option.label }}
                    </button>
                </div>
            </div>
            <ChangePassword v-if="activeScreen=='changePassword'"
                @close-password="activeScreen=''"
                @close-settings="resetView();"
            />
        </div>
    </Modal>
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

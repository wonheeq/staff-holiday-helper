<script setup>
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useDark } from "@vueuse/core";
import { useEmailFrequencyStore } from '@/stores/EmailFrequencyStore';
import { useReminderTimeframeStore } from '@/stores/ReminderTimeframeStore';
const reminderTimeframeStore = useReminderTimeframeStore();
const { getReminderTimeframe } = reminderTimeframeStore;
const { reminderTimeframe } = storeToRefs(reminderTimeframeStore);
const emailFrequencyStore = useEmailFrequencyStore();
const { getFrequency } = emailFrequencyStore;
const { frequency } = storeToRefs(emailFrequencyStore);
const isDark = useDark();
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const { updateWidth } = screenSizeStore;
onMounted(() => {
    window.onresize = () => {
        updateWidth(screen.availWidth);
    }

    updateWidth(screen.availWidth);
});

if (frequency.value == null) {
    getFrequency();
}
if (reminderTimeframe.value == null) {
    getReminderTimeframe(user.value.accountNo);
}
</script>
<template>
<main :class="isMobile ? isDark?'bg-mobile-dark':'bg-mobile' : isDark?'bg-desktop-dark scrollbar-dark':'bg-desktop'">
    <slot/>
</main>
</template>
<style>
.bg-desktop {
    background: url('/images/background.svg') no-repeat center top; 
    background-size: cover;
    height: 100vh;
}
.bg-desktop-dark {
    background: url('/images/background_dark.svg') no-repeat center top; 
    background-size: cover;
    height: 100vh;
    color: white;
}
.bg-mobile {
    background: url('/images/background.svg') center top; 
    background-size: cover;
    height: auto;
    overflow: auto;
}
.bg-mobile-dark {
    background: url('/images/background_dark.svg') center top; 
    background-size: cover;
    height: auto;
    overflow: auto;
    color: white;
}
</style>
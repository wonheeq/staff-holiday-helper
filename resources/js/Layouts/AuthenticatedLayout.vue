<script setup>
import Navbar from "@/Components/Navbar.vue";
import SettingsModal from "@/Components/Settings/SettingsModal.vue";
import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { useEmailFrequencyStore } from '@/stores/EmailFrequencyStore';
import { useReminderTimeframeStore } from '@/stores/ReminderTimeframeStore';
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const user = computed(() => page.props.auth.user);
const reminderTimeframeStore = useReminderTimeframeStore();
const { getReminderTimeframe } = reminderTimeframeStore;
const { reminderTimeframe } = storeToRefs(reminderTimeframeStore);
const emailFrequencyStore = useEmailFrequencyStore();
const { getFrequency } = emailFrequencyStore;
const { frequency } = storeToRefs(emailFrequencyStore);
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);

let settingsVisible = ref(false);

if (frequency.value == null) {
    getFrequency();
}
if (reminderTimeframe.value == null) {
    getReminderTimeframe(user.value.accountNo);
}
</script>
<template>
    <div class="w-full h-[100vh]">
        <div v-if="isMobile">
            <div class="margin-fix-mobile"></div>
            <Navbar
            class="h-[7vh] mx-2"
            @open-settings="settingsVisible = true"/>
            <slot />
        </div>
        <div v-else>
            <div class="margin-fix"></div>
            <Navbar
            class="h-[7vh] mx-4"
            @open-settings="settingsVisible = true"/>
            <slot />
        </div>
    </div>
    <SettingsModal @close-settings="settingsVisible = false" v-show="settingsVisible"/>
</template>

<style>
.margin-fix{
    height: 1rem;
}
.margin-fix-mobile{
    height: 0.5rem;
}
</style>

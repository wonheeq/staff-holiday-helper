<script setup>
import Navbar from "@/Components/Navbar.vue";
import SettingsModal from "@/Components/Settings/SettingsModal.vue";
import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
// const { $isMobile() } = storeToRefs(screenSizeStore);

let settingsVisible = ref(false);
</script>
<template>
    <div class="w-full h-[100vh]">
        <div v-if="$isMobile()">
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

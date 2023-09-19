<script setup>
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useDark } from "@vueuse/core";
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

</script>
<template>
<main :class="isMobile ? isDark?'bg-mobile-dark':'bg-mobile' : isDark?'bg-desktop-dark':'bg-desktop'">
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
}
.bg-mobile {
    background: url('/images/background.svg') center top; 
    background-size: cover;
    height: auto;
    overflow: auto;
}
.bg-mobile {
    background: url('/images/background_dark.svg') center top; 
    background-size: cover;
    height: auto;
    overflow: auto;
}
</style>
<script setup>
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
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
<main :class="isMobile ? 'bg-mobile' : 'bg-desktop'">
    <slot/>
</main>
</template>
<style>
.bg-desktop {
    background: url('/images/background.svg') no-repeat center top; 
    background-size: cover;
    height: 100vh;
}
.bg-mobile {
    background: url('/images/background.svg') center top; 
    background-size: cover;
    height: auto;
    overflow: auto;
}
</style>
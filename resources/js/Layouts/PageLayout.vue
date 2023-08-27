<script setup>
function isMobile() {
    if( screen.width <= 760 ) {
        return true;
    }
    else {
        return false;
    }
}
</script>
<template>
<main :class="isMobile() ? 'bg-mobile' : 'bg-desktop'">
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
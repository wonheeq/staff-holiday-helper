<script setup>
import Navbar from "@/Components/Navbar.vue";
import SettingsModal from "@/Components/SettingsModal.vue";
import axios from "axios";
import { ref } from 'vue';

let settingsVisible = ref(false);


</script>

<template>
    <div class="w-full h-[100vh]">
        <div class="margin-fix"></div>
        <Navbar
            class="h-[7vh] mx-4"
            @open-settings="settingsVisible = true"/>
        <slot />
        <SettingsModal @close-settings="settingsVisible = false" v-show="settingsVisible"/>
    </div>
</template>

<style>
.margin-fix{
    height: 1rem;
}
</style>

<script setup>
import Navbar from "@/Components/Navbar.vue";
import SettingsModal from "@/Components/SettingsModal.vue";
import axios from "axios";
import { ref } from 'vue';

let settingsVisible = ref(false);

// Post to logout method
async function handleLogout() {
    await axios.post("logout").then(
        function(response) {
            if( response.data.response == "success") {
                window.location.href = response.data.url
            }
        }
    )
}
</script>

<template>
    <main>
        <div class="margin-fix"></div>
        <Navbar
            class="h-[7vh] mx-4"
            @open-settings="settingsVisible = true"
            @log-out="handleLogout"/>
        <slot />
        <SettingsModal @close-settings="settingsVisible = false" v-show="settingsVisible"/>
    </main>
</template>

<style>
.margin-fix{
    height: 1rem;
}
</style>

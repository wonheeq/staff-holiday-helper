<template>
    <PageLayout>
        <div>
            <!-- Login Window -->
            <login-form v-if="showLogin" @forgotPass="goToReset" @unitLookup="goToLookup"></login-form>

            <!-- Password Reset Window -->
            <reset-form v-if="showReset" @resetBack="goToLogin"></reset-form>

            <!-- Unit Lookup Window -->
            <unit-lookup v-if="showLookup" @got-results="goToResults" @lookupBack="goToLogin"></unit-lookup>

            <!-- Unit Search Results Window -->
            <unit-result v-if="showResult" @resultBack="goToLookup" :results="searchResults"></unit-result>

        </div>
    </PageLayout>
</template>

<script setup>
import PageLayout from "@/Layouts/PageLayout.vue";
import LoginForm from "@/Components/Landing/LoginForm.vue";
import ResetForm from "@/Components/Landing/ResetForm.vue";
import UnitLookup from "@/Components/Landing/UnitLookup.vue";
import unitResult from "@/Components/Landing/UnitResult.vue";
import { ref } from "vue";

// Variables
const showLogin = ref(true);
const showReset = ref(false);
const showLookup = ref(false);
const showResult = ref(false);

const searchResults = ref(null);

// Show Password Reset Window
function goToReset() {
    showLogin.value = false;
    showReset.value = true;
}

// Show Login Window
function goToLogin() {
    showLogin.value = true;
    showReset.value = false;
    showLookup.value = false;
    showReset.value = false;
}

// Show Unit Lookup Window
function goToLookup() {
    showLogin.value = false;
    showLookup.value = true;
}

// Show Unit Results Window
function goToResults(response) {
    searchResults.value = response;

    showLookup.value = false;
    showResult.value = true;
}



</script>




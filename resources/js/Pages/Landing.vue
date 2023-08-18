<!--
    File: Landing.vue
    Purpose: Parent file for the landing/login page for LeaveOnTime.
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<template>
    <main>
        <div>
            <!-- Login Window -->
            <login-form v-if="showLogin" @forgotPass="goToReset" @unitLookup="goToLookup"></login-form>

            <!-- Password Reset Window -->
            <reset-form v-if="showReset" @resetBack="goToLogin"></reset-form>

            <!-- Unit Lookup Window -->
            <unit-lookup v-if="showLookup" @got-results="goToResults" @lookupBack="goToLogin"></unit-lookup>

            <!-- Unit Serach Results Window -->
            <unit-result
                :unit-name="unitName" :email="email" :name="name"
                v-if="showResult" @resultBack="goToLookup">
            </unit-result>

        </div>
    </main>
</template>

<script setup>
import LoginForm from "@/Components/Landing/LoginForm.vue";
import ResetForm from "@/Components/Landing/ResetForm.vue";
import PasswordForm from "@/Components/Landing/PasswordForm.vue";
import UnitLookup from "@/Components/Landing/UnitLookup.vue";
import unitResult from "@/Components/Landing/UnitResult.vue";
import { ref } from "vue";

// Variables for window visibility
const showLogin = ref(true);
const showReset = ref(false);
const showPass = ref(false);
const showLookup = ref(false);
const showResult = ref(false);
const email = ref('');
const name = ref('');
const unitName = ref('');

// Show Password Reset Window
function goToReset() {
    showLogin.value = false;
    showReset.value = true;
}

// Show Login Window
function goToLogin() {
    showLogin.value = true;
    showReset.value = false;
    showPass.value = false;
    showLookup.value = false;
    showReset.value = false;
}

// Show Unit Lookup Window
function goToLookup() {
    showLogin.value = false;
    showLookup.value = true;
}

// Show Unit Results Window
function goToResults(inUnitName, inEmail, inName) {
    unitName.value = inUnitName;
    email.value = inEmail;
    name.value = inName;

    showLookup.value = false;
    showResult.value = true;
}

// Show New Password Window
function goToPass() {
    showPass.value = true;
    showReset.value = false;
    showLookup.value = false;
    showReset.value = false;
    showLogin.value = false;
}

</script>




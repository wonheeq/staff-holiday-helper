<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import Spinner from './Spinner.vue';
import { ref } from "vue";
import { useDark } from "@vueuse/core";
const isDark = useDark();

let showConf = ref(false);
const staffID = ref('');
const staffEmail = ref('');
const errorMsg = ref('');
const isLoading = ref(false);


async function handleReset() {
    errorMsg.value = ''; // reset message
    showConf.value = false;
    isLoading.value = true;

    // post to request reset email.
    await axios.post("/reset-password", {
        accountNo: staffID.value,

    }).then(function (response) { // success response
        isLoading.value = false;
        showConf.value = true;

    }).catch(error => { // fail response
        isLoading.value = false;
        // comment below out to remove error message popup.
        if (error.response) {
            // fixing errors cause of laravel backend jank.
            if (((error.response.data.message) === "The email field must be a valid email address.") ||
                ((error.response.data.message) === "We can't find a user with that email address.") ||
                ((error.response.data.message) === "The account no field is required.")){
                errorMsg.value = "Invalid Staff ID."
            }
            else {
                // errorMsg.value = "Invalid Staff ID."
                errorMsg.value = error.response.data.message;
            }
        }
    })
    // uncomment below to show conf regardless of if id was correct.
    // showConf.value = true;
}

</script>

<template>
    <div class="w-screen h-screen flex flex-col justify-center items-center ">
        <!-- Box/White Area -->
        <div class="w-[80%] laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit p-5 drop-shadow-md rounded-md" :class="isDark?'bg-gray-800':'bg-white'">

            <!-- Logo -->
            <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="logo mx-auto mb-5"  :class="isDark?'darkModeImage':''">

            <form id="resetForm" action="#" @submit.prevent="handleReset">
                <!-- Staff ID -->
                <div class="mb-5">
                    <landing-input
                        title="Staff ID"
                        v-model="staffID"
                        inType="textType"
                        autocomplete="username"  
                    >
                    </landing-input>
                </div>

                <!-- Reset Button -->
                <button :disabled="isLoading" type="submit" class="w-full font-bold text-2xl 4k:text-3xl p-2 mb-2" 
                    :class="isDark?'bg-blue-800':'bg-blue-300'">
                    <spinner v-show="isLoading"></spinner>
                    <div :class="{ 'invisible': isLoading }">
                        Reset Password
                    </div>
                </button>
            </form>

            <!-- Error Message -->
            <div class="flex justify-center text-center mb-2">
                <h1 class="text-red-500 4k:text-xl">{{ errorMsg }}</h1>
            </div>

            <!-- Back Button -->
            <div class="flex justify-between">
                <button @click="$emit('resetBack')" class="underline font-bold 4k:text-xl">Back to Login</button>
            </div>
        </div>

        <!-- Confirmation Popup -->
        <div class="max-w-[90%]">
            <div v-show="showConf === true" class="4k:text-2xl 1440:w-fit h-fit bg-blue-100 border border-black p-5 mt-7 rounded-lg">
            <p class="text-center">A confirmation email has been sent to the email address linked to
                this account if it exists!</p>
            <p class="text-center">Please follow the steps in the email to proceed with the password
                reset</p>
            </div>
        </div>


    </div>
</template>

<style>
@media
(max-width: 1360px) {

    .logo{
        height: auto;
        width: 60%;
    }
}
</style>
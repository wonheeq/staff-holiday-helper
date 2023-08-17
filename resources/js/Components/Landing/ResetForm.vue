<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import Spinner from './Spinner.vue';
import { ref } from "vue";

let showConf = ref(false);
const staffID = ref('');
const staffEmail = ref('');
const errorMsg = ref('');
const isLoading = ref(false);


async function handleReset() {
    errorMsg.value = ''; // reset message
    isLoading.value = true;
    staffEmail.value = staffID.value + '@curtin.edu.au'; // build email

    // post to request reset email.
    await axios.post("reset-password", {
        email: staffEmail.value,
        accountNo: staffID.value,

    }).then( function(response) { // success response
        isLoading.value = false;
        showConf.value = true;

    }).catch(error => { // fail response
        isLoading.value = false;
        // comment below out to remove error message popup.
        if(error.response) {
            // fixing errors cause of laravel backend jank.
            if( (error.response.data.message) === "The email field must be a valid email address."){
                errorMsg.value = "Invalid Staff ID."
            }
            else if((error.response.data.message) === "We can't find a user with that email address."){
                errorMsg.value = "Invalid Staff ID."
            }
            else {
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
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <form action="#" @submit.prevent="handleReset">
            <!-- Staff ID -->
            <div class="mb-5">
                <landing-input
                    title="Staff ID"
                    v-model="staffID"
                    inType="textType" >
                </landing-input>
            </div>

            <!-- Reset Button -->
            <button
                :disabled="isLoading"
                type="submit"
                class="w-full font-bold text-2xl bg-blue-300 p-2 mb-5"
            >
            <spinner v-show="isLoading"></spinner>
            <div :class="{'invisible': isLoading}">
                Reset Password
            </div>
            </button>
        </form>

        <!-- Error Message -->
        <div class="flex justify-center text-center mb-2">
            <h1 class="text-red-500">{{ errorMsg }}</h1>
        </div>

        <!-- Back Button -->
        <div class="flex justify-between">
            <button @click="$emit('resetBack')" class="underline font-bold">Back to Login</button>
        </div>
    </div>

    <!-- Confirmation Popup -->
    <div v-show="showConf === true"
        class ="1440:w-fit h-fit bg-blue-100 border border-black p-5 mt-7 rounded-lg">
        <p class="text-center">A confirmation email has been sent to the email address linked to
                               this account if it exists!</p>
        <p class="text-center">Please follow the steps in the email to proceed with the password
                               reset</p>
    </div>
</div>
</template>



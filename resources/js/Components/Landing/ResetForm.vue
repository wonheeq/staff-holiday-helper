<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import { ref } from "vue";

let showConf = ref(false);
const staffID = ref('');
const staffEmail = ref('');
const errorMsg = ref('');


async function handleReset() {
    staffEmail.value = staffID.value + '@curtin.edu.au';
    console.log(staffEmail.value);
    await axios.post("reset-password", {
        email: staffEmail.value,
        accountNo: staffID.value,
    }).then( function(response) {
        // console.log(response);
        showConf.value = true;
        errorMsg.value = '';
    }).catch(error => {
        if(error.response) {
            errorMsg.value = error.response.message;
            // console.log(error.response);
        }
    })
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
                type="submit"
                class="w-full font-bold text-2xl bg-blue-300 p-2 mb-5"
            >Reset Password</button>
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



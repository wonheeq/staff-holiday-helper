<script setup>
import LandingInput from './LandingInput.vue';
import { ref } from "vue";

let showConf = ref(false);
</script>

<template>
<div class="w-screen h-screen flex flex-col justify-center items-center ">
    <!-- Box/White Area -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <!-- Staff ID -->
        <div class="mb-5">
            <landing-input title="Staff ID" inType="textType" ></landing-input>
        </div>

        <!-- Reset Button -->
        <button
            @click="showConf = !showConf"
            class="w-full font-bold text-2xl bg-blue-300 p-2 mb-5"
        >Reset Password</button>

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



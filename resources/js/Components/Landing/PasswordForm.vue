<!--
    File: PasswordForm.vue
    Purpose: Password Reset Component for use in Landing.vue
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 1/08/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<template>
<div class="w-screen h-screen flex flex-col justify-center items-center ">
    <!-- Box/White Backgorund -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <form action="#" @submit.prevent="handleReset">
            <!-- Password Input 1 -->
            <div class="mb-5">
                <landing-input
                v-model="password" title="New Password" inType="passwordType" >
            </landing-input>
            </div>

            <!-- Password Input 2 -->
            <div class="mb-5">
                <landing-input
                v-model="passwordConf" title="Confirm New Password" inType="passwordType" >
            </landing-input>
            </div>

            <!-- Reset Button -->
            <!-- :disabled="!buttonActive" -->

            <button
                type="submit"
                class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2">Reset Password
            </button>
        </form>








        <!-- Error Message -->
        <!-- <div class="flex justify-center mb-2 text-red-500">
            <ul>
                <li v-for="error in errors.slice(0, 1)">
                    {{ error }}
                </li>
            </ul>
        </div> -->

        <!-- Bottom Links -->
        <div class="flex justify-between">
            <!-- Back Button -->
            <button @click="goToLanding" class="underline font-bold">Back to Login</button>
        </div>
    </div>

    <!-- Confirmation Message -->
    <div v-show="showConf" class ="1440:w-fit h-fit bg-blue-100 border border-black p-5 mt-7 rounded-lg">
        <p class="text-center">Your password has been successfully changed!</p>
    </div>

</div>
</template>

<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import { ref, watch, reactive } from "vue";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const formData = ref({
    password: '',
    passwordConf: ''
});

const testPassword = ref({''});

async function handleReset() {
    await axios.post("/update-password", {
        token: props.token,
        email: props.email,
        password: formData.value.password,
    });
}

</script>





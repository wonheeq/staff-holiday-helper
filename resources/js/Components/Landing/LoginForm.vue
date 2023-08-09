<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import { ref } from "vue";

axios.defaults.withCredentials = true;

const formData = ref({
    accountNo: '',
    password: ''
});
const errorMsg = ref('');

async function handleLogin() {
    // get csrf cookie
    await axios.get("/sanctum/csrf-cookie");

    // post credentials to login route
    await axios.post("login", {
        accountNo: formData.value.accountNo,
        password: formData.value.password,
    }).then( function(response) {
        // if login successful, redirect to url provided by response
        // else, update error message
        if( response.data.response == "success") {
            window.location.href = response.data.url
        } else {
            errorMsg.value = response.data.error;
        }
    }).catch(error => {
        // if 422 error occurs, update error message
        if(error.response) {
            errorMsg.value = 'Please enter your credentials'
            console.log(error.response);
        }
    });
};
</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">

    <!-- Box/Background -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" class="mx-auto mb-5" >

        <form action="#" @submit.prevent="handleLogin">
            <!-- Username and Password Input -->
            <landing-input
                title="Staff ID"
                v-model="formData.accountNo"
                inType="textType">
            </landing-input>

            <landing-input
                title="Password"
                v-model="formData.password"
                inType="passwordType">
            </landing-input>

             <!-- Login Button -->
            <button
                type="submit"
                class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2"
            >Sign In</button>
        </form>

        <!-- Error Message -->
        <div class="flex justify-center mb-2">
            <h1 class="text-red-500">{{ errorMsg }}</h1>
        </div>

        <!-- Bottom Links -->
        <div class="flex justify-between">
            <!-- Forgot Password -->
            <button @click="$emit('forgotPass')" class="underline font-bold">Forgot Password?</button>

            <!-- Unit Lookup -->
            <button @click="$emit('unitLookup')" class="underline font-bold">Unit Lookup</button>
        </div>
    </div>
</div>
</template>



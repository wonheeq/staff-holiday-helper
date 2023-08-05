<!--
    File: LoginForm.vue
    Purpose: Login Window Component for use in Landing.vue
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<script setup>
import LandingInput from './LandingInput.vue';
import { ref } from "vue";

const passOne = ref("");
let showError = ref(false);
</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">

    <!-- Box/Background -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" class="mx-auto mb-5" >

        <!-- Username and Password Input -->
        <landing-input title="Staff ID" inType="textType"></landing-input>
        <landing-input v-model="passOne" title="Password" inType="passwordType"></landing-input>

        <!-- Login Button -->
        <button
            @click="showError = !showError"
            class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2"
        >Login</button>

        <!-- Error Message -->
        <div v-show="showError" class="flex justify-center mb-2">
            <h1 class="text-red-500">Invalid Username or Password</h1>
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



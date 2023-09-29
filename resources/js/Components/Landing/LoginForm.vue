<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useDark } from "@vueuse/core";
const isDark = useDark();

axios.defaults.withCredentials = true;


const formData = useForm({
    accountNo: '',
    password: ''
});
const errorMsg = ref('');

async function handleLogin() {
    // get csrf cookie
    await axios.get("/sanctum/csrf-cookie");

    formData.post(route('login'), {
        onFinish: () => formData.reset('password'),
        onError: (error) => {
            errorMsg.value = error;
        }
    });
};
</script>

<template>
    <div class="w-screen h-screen flex justify-center items-center ">

        <!-- Box/Background -->
        <div class="w-[80%] laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit p-5 drop-shadow-md rounded-md" :class="isDark?'bg-gray-800':'bg-white'">

            <!-- Logo -->
            <img src="/images/logo-horizontal.svg" class="logo mx-auto mb-5" :class="isDark?'darkModeImage':''">

            <form action="#" @submit.prevent="handleLogin">
                <!-- Username and Password Input -->
                <landing-input
                    title="Staff ID"
                    v-model="formData.accountNo"
                    autocomplete="username"
                    inType="textType">
                </landing-input>

                <landing-input
                    title="Password"
                    v-model="formData.password"
                    autocomplete="current-password"
                    inType="passwordType">
                </landing-input>

                 <!-- Login Button -->
                <button
                    type="submit"
                    class="w-full font-bold text-2xl 4k:text-3xl p-2 mb-2"
                    :class="isDark?'bg-blue-800':'bg-blue-300'"
                >Sign In</button>
            </form>

            <!-- Error Message -->
            <div class="flex justify-center mb-2">
                <h1 class="text-red-500 4k:text-xl">{{ errorMsg }}</h1>
            </div>

            <!-- Bottom Links -->
            <div class="flex justify-between">
                <!-- Forgot Password -->
                <button @click="$emit('forgotPass')" class="underline font-bold 4k:text-xl">Forgot Password?</button>

                <!-- Unit Lookup -->
                <button @click="$emit('unitLookup')" class="underline font-bold 4k:text-xl">Unit Lookup</button>
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
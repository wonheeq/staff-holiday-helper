<script setup>
import axios from 'axios';
import LandingInput from './LandingInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

function isMobile() {
    if( screen.availWidth <= 760 ) {
        return true;
    }
    else {
        return false;
    }
}
</script>

<template>

    <!-- Box/Background -->
    <div v-if="isMobile()">
        <div class="w-screen h-screen flex justify-center items-center ">
            <div class="h-fit bg-white p-5 drop-shadow-md">
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
                        class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
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
    </div>


    <div v-else>
        <div class="w-screen h-screen flex justify-center items-center ">
            <div class=" laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit bg-white p-5 drop-shadow-md">

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
                        class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
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
    </div>
</template>

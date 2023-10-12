

<template>
    <div class="w-screen h-screen flex flex-col justify-center items-center ">
        <!-- Box/White Backgorund -->
        <div class="w-[80%] laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit p-5 drop-shadow-md rounded-md" :class="isDark?'bg-gray-800':'bg-white'">

            <!-- Logo -->
            <img src="/images/logo-horizontal.svg" class="logo mx-auto mb-5" :class="isDark?'darkModeImage':''">

            <form id="welcomeForm" action="#" @submit.prevent="handleReset">
                <!-- Password Input 1 -->
                <div class="mb-5">
                    <landing-input
                        v-model="passOne"
                        title="New Password"
                        inType="passwordType"
                        autocomplete="new-password"     
                    >
                    </landing-input>
                </div>

                <!-- Password Input 2 -->
                <div class="mb-5">
                    <landing-input
                        v-model="passTwo"
                        title="Confirm New Password"
                        inType="passwordType"
                        autocomplete="new-password"       
                    >
                    </landing-input>
                </div>

                <!-- Reset Button -->

                <button
                    type="submit"
                    class="w-full font-bold text-2xl 4k:text-3xl p-2 mb-2"
                    :class="isDark?'bg-blue-800':'bg-blue-300'">
                    Set Password
                </button>
            </form>


            <!-- Error Message -->
            <div class="flex justify-center mb-2 mt-2 text-red-500 4k:text-xl text-center">
                            <ul>
                                <li v-for="error in errors.slice(0, 1)">
                                    {{ error }}
                                </li>
                            </ul>
                        </div>


        </div>

        <!-- Confirmation Message -->
        <div v-show="showConf" class="4k:text-2xl 1440:w-fit h-fit mt-2 1440:mt-4 p-4 border border-black rounded-md font-bold"
                :class="isDark?'bg-cyan-600 text-blue-200':'bg-cyan-100 text-blue-800'">
            <p class="text-center">Your password has been successfully changed!</p>
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

    <script setup>
    import axios from 'axios';
    import LandingInput from './LandingInput.vue';
    import { ref, watch, reactive } from "vue";
    import { useDark } from "@vueuse/core";
import { usePage } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3';
const page = usePage();
const isDark = useDark();


    const props = defineProps({
        hash: {
            type: String,
            required: true,
        },
    });


    async function handleReset() {

        await axios.post("/api/set-password", {
            hash: props.hash,
            password: passOne.value,
            password_confirmation: passTwo.value,

        }).then( function(response) {
            // goToLanding();
            // showConf.value = true;
            // errors.length = 0;
            // buttonActive.value = false;


        }).catch(error => {
            if(error.response) {
                errors.push(error.response.data.message);
            }
        })
    }

    const passOne = ref("");
    const passTwo = ref("");
    const showConf = ref(false);
    const MIN_LENGTH = 10;
    const MAX_LENGTH = 30;
    const hasUppercase = new RegExp("(?=.*[A-Z])");
    const hasLowercase = new RegExp("(?=.*[a-z])");
    const hasWhitespace = new RegExp("/\s/");
    const hasDigit = new RegExp("\\d");

    let errors = reactive([]);
    let buttonActive = ref(false);
    let password = reactive({
        password: "",
        confirm: ""
    });

    // Navigate to the landing page
    function goToLanding() {
        window.location.href = "/";
    }

    // Password validation
    let validatePasswords = () => {
       errors.length = 0;

       if (!hasUppercase.test(password.password)) {
           errors.push("Password must contain at least one uppercase letter.");
       }

       if (!hasLowercase.test(password.password)) {
           errors.push("Password must contain at least one lowercase letter.");
       }

       if (!hasDigit.test(password.password)) {
           errors.push("Password must contain at least one number.");
       }

       if (password.password.length < MIN_LENGTH || password.password.length > MAX_LENGTH) {
           errors.push("Password length must be between 10 and 30.");
       }

       if (hasWhitespace.test(password.password)) {
           errors.push("Password must not contain spaces.");
       }

       // Check if passwords match and activate submit button if so
       if (password.password !== password.confirm) {
           errors.push("Passwords do not match.");
           buttonActive.value = false;
       }
       else if (password.password == password.confirm && errors.length == 0) {
           buttonActive.value = true;
       }
    };

    // Watches the refs for the values emitted by the landingInputs and updates
    // the value used for password validation
    watch(passOne, () =>  {
        password.password = passOne.value;
    });
    watch(passTwo, () =>  {
        password.confirm = passTwo.value;
    });

    // Watches the value used for password validation to check if after each change
    watch(password, () => {
        validatePasswords();
    });

    </script>






<template>
    <div class="w-screen h-screen flex flex-col justify-center items-center ">
        <!-- Box/White Backgorund -->
        <div class="w-[80%] laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit bg-white p-5 drop-shadow-md ">
        <!-- <div class="bg-white w-1/5 min-w-[320px] 1080:min-w-[420px] h-fit rounded-md drop-shadow-md pl-2 pb-2 1440:pl-4 1440:pb-4"> -->


            <!-- Logo -->
            <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="logo mx-auto mb-5" >

            <form action="#" @submit.prevent="handleReset">
                <!-- Password Input 1 -->
                <div class="mb-5">
                    <landing-input
                        v-model="passOne" title="New Password" inType="passwordType" >
                    </landing-input>
                </div>

                <!-- Password Input 2 -->
                <div class="mb-5">
                    <landing-input
                        v-model="passTwo" title="Confirm New Password" inType="passwordType" >
                    </landing-input>
                </div>

                <!-- Reset Button -->

                <button
                    type="submit"
                    :disabled="!buttonActive"
                    class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2">

                    Reset Password
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

            <!-- Bottom Links -->
            <div class="flex justify-between mt-5">
                <!-- Back Button -->
                <button @click="goToLanding" class="underline font-bold 4k:text-xl">Back to Login</button>
            </div>
        </div>

        <!-- Confirmation Message -->
        <div v-show="showConf" class ="max-w-[90%] 4k:text-2xl 1440:w-fit h-fit bg-blue-100 border border-black p-5 mt-7 rounded-lg">
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

    const props = defineProps({
        accountNo: {
            type: String,
            required: true,
        },
        token: {
            type: String,
            required: true,
        },
    });


    async function handleReset() {

        await axios.post("/update-password", {
            token: props.token,
            accountNo: props.accountNo,
            password: passOne.value,
            password_confirmation: passTwo.value,

        }).then( function(response) {
            showConf.value = true;
            errors.length = 0;
            buttonActive.value = false;


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


    function isMobile() {
        if( screen.availWidth <= 760 ) {
            return true;
        }
        else {
            return false;
        }
    }
    </script>





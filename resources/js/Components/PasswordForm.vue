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
            :disabled="!buttonActive"
            @click="showConf = true"
            class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2">Reset Password
        </button>

        <!-- Error Message -->
        <div class="flex justify-center mb-2 text-red-500">
            <ul>
                <li v-for="error in errors.slice(0, 1)">
                    {{ error }}
                </li>
            </ul>
        </div>

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
import LandingInput from './LandingInput.vue';
import { ref, watch, reactive } from "vue";

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

   if (password.password.length < MIN_LENGTH || password.password.length > MAX_LENGTH) {
       errors.push("Password length must be between 10 and 30.");
   }

   if (!hasDigit.test(password.password)) {
       errors.push("Password must contain at least one number.");
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





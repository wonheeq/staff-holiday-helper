<!--
    File: UnitLookup.vue
    Purpose: Vue Component for the Unit Lookup Window for use in Landing.vue
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<script setup>
import { ref } from "vue";
import axios from 'axios';
import LandingInput from './LandingInput.vue';

let emit = defineEmits(['gotResults']);

const formData = ref({
    code: ''
});
const email = ref('');
const name = ref('');
const unitName = ref('');
const unitId = ref('');
const errorMsg = ref('');

async function handleSearch() {
    axios.post("api/getUnitDetails", {
        code: formData.value.code

    }).then( function(response) {
        emit("gotResults", response);
    }).catch(error => {
        if(error.response) {
            errorMsg.value = "Please enter a valid unit ID";
            console.log(error);
        }
    });
}
</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">
    <!-- Box/White Background -->
    <div class=" laptop:w-[25%] 1080:w-[20%] 1440:w-[17%] 4k:w-[14%] h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <form action="#" @submit.prevent="handleSearch">
            <!-- Unit name/ID input -->
            <landing-input
                title="Unit ID"
                v-model="formData.code"
                inType="textType" >
            </landing-input>

            <!-- Search Button -->
            <button
                type="submit"
                class="w-full font-bold text-2xl 4k:text-3xl bg-blue-300 p-2 mb-2"
            >Search</button>
        </form>

        <!-- Error Message -->
        <div class="flex justify-center text-center mb-2">
            <h1 class="text-red-500 4k:text-xl">{{ errorMsg }}</h1>
        </div>

        <!-- Bottom Links -->
        <div class="flex justify-between">
            <!-- Back button -->
            <button @click="$emit('lookupBack')" class="underline font-bold 4k:text-xl">Back</button>
        </div>
    </div>
</div>
</template>



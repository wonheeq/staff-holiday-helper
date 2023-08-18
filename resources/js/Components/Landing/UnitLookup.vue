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


async function handleSearch() {
    axios.post("api/getUnitDetails", {
        code: formData.value.code

    }).then( function(response) {
        email.value = response.data.email;
        name.value = response.data.name;
        unitName.value = response.data.unitName;
        unitId.value = response.data.unitId;
        emit("gotResults", unitId.value, unitName.value, email.value, name.value);

    }).catch(error => {
        if(error.response) {
            console.log(error);
        }
    });
}
</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">
    <!-- Box/White Background -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

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
                class="w-full font-bold text-2xl bg-blue-300 p-2 mb-2"
            >Search</button>
        </form>

        <!-- Bottom Links -->
        <div class="flex justify-between">
            <!-- Back button -->
            <button @click="$emit('lookupBack')" class="underline font-bold">Back</button>
        </div>
    </div>
</div>
</template>



<!--
    File: UnitLookup.vue
    Purpose: Vue Component for the Unit Lookup Window for use in Landing.vue
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

<script setup>
import LandingInput from './LandingInput.vue';
</script>

<template>
<div class="w-screen h-screen flex justify-center items-center ">
    <!-- Box/White Background -->
    <div class="w-1/4 1080:w-1/5 1440:w-1/6 4k:w-1/6 h-fit bg-white p-5 drop-shadow-md">

        <!-- Logo -->
        <img src="/images/logo-horizontal.svg" alt="Logo Horizontal" class="mx-auto mb-5" >

        <!-- Unit name/ID input -->
        <div class="mb-5">
            <landing-input title="Unit Name / ID" inType="textType" ></landing-input>
        </div>

        <!-- Search Button -->
        <button
            @click="$emit('lookupSearch')" class="w-full font-bold text-2xl bg-blue-300 p-2 mb-5"
        >Search</button>

        <!-- Bottom Links -->
        <div class="flex justify-between">
            <!-- Back button -->
            <button @click="$emit('lookupBack')" class="underline font-bold">Back</button>
        </div>

    </div>
</div>
</template>



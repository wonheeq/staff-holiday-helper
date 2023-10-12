<template>
    <!-- Text Input -->
    <div v-if="inType === 'textType'" class="mb-5">
        <h1 class="font-bold text-xl 1080:text-2xl 1440:text-2xl 4k:text-3xl">{{ title }}</h1>
        <input v-if="title =='Staff ID'"
            autocomplete="username"
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="border-black w-full 4k:text-xl"
            :class="isDark?'bg-gray-800 border-white':''"
        />
        <input v-else
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="border-black w-full 4k:text-xl"
            :class="isDark?'bg-gray-800 border-white':''" />
    </div>

    <!-- Password Input -->
    <div v-if="inType === 'passwordType'" class="mb-5">
        <h1 class="font-bold text-xl 1080:text-2xl 1440:text-2xl 4k:text-4xl">{{ title }}</h1>
        <div class="flex items-center">
            <input
                :type="fieldType.type"
                :value="modelValue"
                class="border-black w-full 4k:text-xl"
                :class="isDark?'bg-gray-800 border-white':''"
                @input="$emit('update:modelValue', $event.target.value)"
            />

            <button @click.prevent="switchVis" tabindex="-1" type="button" class="fixed right-7">
                <img :src="fieldType.image" :class="isDark?'darkModeImage':''">
            </button>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';

import { useDark } from "@vueuse/core";
const isDark = useDark();
defineProps({
title: { type: String, default: "", },
inType: { type: String, default: "textType", },
modelValue: { type: String, default: "" }
});


// Function to toggle masking for the password input
let fieldType = reactive({
    type: "password",
    image: "/images/Eye_light.svg"
});
let switchVis = () => {
    if (fieldType.type === "password" ) {
        fieldType.type = "text";
        fieldType.image = "/images/Eye_fill.svg";
    } else {
        fieldType.type = "password";
        fieldType.image = "/images/Eye_light.svg";
    }
};
</script>




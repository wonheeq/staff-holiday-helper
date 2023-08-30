<!--
    File: LandingInput.vue
    Purpose: Custom Input component for use in Landing.Vue and its children
    Author: Ellis Janson Ferrall (20562768)
    Last Modified: 30/07/2023
        By: Ellis Janson Ferrall (20562768)
 -->

 <template>
    <!-- Text Input -->
    <div v-if="inType === 'textType'" class="mb-5">
        <h1 class="font-bold text-xl 1080:text-2xl 1440:text-2xl 4k:text-4xl">{{ title }}</h1>
        <input
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="border-black w-full"
        />
    </div>

    <!-- Password Input -->
    <div v-if="inType === 'passwordType'" class="mb-5">
        <h1 class="font-bold text-xl 1080:text-2xl 1440:text-2xl 4k:text-4xl">{{ title }}</h1>
        <div class="flex justify-between border border-solid  border-black">
            <input
                class="border-none w-full"
                :type="fieldType"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />

            <button v-if="fieldType === 'password'" @click="switchVis">
                <img src="/images/Eye_light.svg" class="mr-5 ml-5">
            </button>

            <button v-if="fieldType === 'text'" @click="switchVis">
                <img src="/images/Eye_fill.svg" class="mr-5 ml-5">
            </button>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';

defineProps({
title: { type: String, default: "", },
inType: { type: String, default: "textType", },
modelValue: { type: String, default: "" }
});


// Function to toggle masking for the password input
let fieldType = ref("password");
function switchVis() {
if ( fieldType.value == "password" ) {
    fieldType.value = "text";
} else {
    fieldType.value = "password";
}
}

</script>




 <template>
    <!-- Text Input -->
    <div v-if="inType === 'textType'" class="mb-5">
        <h1 class="font-bold text-xl 1080:text-2xl 1440:text-2xl 4k:text-3xl">{{ title }}</h1>
        <input
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="border-black w-full 4k:text-xl"
        />
    </div>

    <!-- Password Input -->
    <div v-if="inType === 'passwordType'" class="mb-5">
        <h1 class="font-bold text-xl 1080:text-2xl 1440:text-2xl 4k:text-4xl">{{ title }}</h1>
        <div class="flex border border-solid items-center border-black ">
            <input
                class="border-none w-full 4k:text-xl"
                :type="fieldType.type"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />

            <button @click.prevent="switchVis" tabindex="-1" type="button" class="fixed right-7">
                <img :src="fieldType.image" class="">
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




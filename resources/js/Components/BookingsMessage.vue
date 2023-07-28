<script setup>
import { ref } from 'vue';
let props = defineProps({ source: Object });

const statusText = {
    "P": "Pending",
    "U": "Undecided",
    "Y": "Approved",
    "N": "Denied",
};
const statusColour = {
    "P": "text-yellow-500",
    "U": "text-yellow-500",
    "Y": "text-green-500",
    "N": "text-red-500",
};

let toggleContent = ref(false);
let toggleImage = (isVisible) => {
    if (isVisible) {
        return 'images/triangle_up.svg';
    }

    return 'images/triangle_down.svg';
}
</script>
<template>
    <div class="flex flex-row bg-white mr-4">
        <div class="flex flex-col w-5/6 bg-gray-200 p-2">
            <p class="text-xl font-bold">{{ source.start }} - {{ source.end }}</p>
            <div v-show="toggleContent">
                <div class="flex flex-row">
                    <p class="font-medium mr-2">Application ID:</p>
                    {{ source.id }}
                </div>
                <div>
                    <p class="font-medium">Substitute/s:</p>
                    <p v-for="nomination in source.nominations">
                        → {{ nomination.name }} ({{ nomination.user_id }}) - [{{ nomination.user_id }}@curtin.edu.au]    {{ nomination.task }}
                    </p>
                </div>
                <div class="flex flex-row">
                    <p class="font-medium mr-2">Application Submitted:</p>
                    {{ new Date(source.created_at).toLocaleString() }}
                </div>
            </div>
        </div>
        <div class="flex flex-col w-1/6 bg-gray-200 text-4xl ml-2">
            <p :class="statusColour[source.status]" class="p-2">
                {{ statusText[source.status] }}
            </p>
        </div>
        <div class="flex flex-col bg-white">
            <button
                class="ml-2 text-5xl px-6 bg-gray-200 text-center h-14"
                @click="toggleContent=!toggleContent"
            >
                <img :src="toggleImage(toggleContent)" class="toggleImageIcon"/>
            </button>
        </div>
    </div>
</template>

<style>
.toggleImageIcon{
    width: 100%;
    height: 100%;
}
</style>
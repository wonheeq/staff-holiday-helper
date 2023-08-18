<script setup>
import NomineeDropdown from '@/Components/Bookings/NomineeDropdown.vue';
let props = defineProps({
    nomination: Object,
    options: Object,
    isDisabled: Boolean
});

let emit = defineEmits(['nominationSelected']);

const disabledClass = "bg-gray-300 border-gray-100";
</script>
<template>
    <div class="flex mb-2.5 mt-2.5 w-full">
        <div class="flex space-x-6 1080:space-x-7 ml-2 1080:ml-2.5 w-[40%]">
            <input type="checkbox"
                class="1080:w-6 1080:h-6 1440:w-8 1440:h-8"
                :class="isDisabled ? disabledClass : ''"
                v-model="nomination.selected"
                :disabled="isDisabled"   
                @click="emit('nominationSelected', nomination.selected)" 
            />
            <p class="text-sm 1080:text-lg 1440:text-xl truncate w-[85%] pr-2">
                {{ nomination.role }}
            </p>
        </div> 
        <div class="flex mr-2 w-[60%] justify-between">
            <p class="text-sm 1080:text-lg 1440:text-xl pl-5 w-fit truncate">
                {{ nomination.nomination }}
            </p>
            <NomineeDropdown
                class="w-64 1440:w-96"
                :options="options"
                @optionSelected="(selection) => nomination.nomination = selection"
                :isDisabled="isDisabled"
            />
        </div>
    </div>
</template>
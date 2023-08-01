<script setup>
import NomineeDropdown from '@/Components/Bookings/NomineeDropdown.vue';
let props = defineProps({
    nomination: Object,
    options: Object,
});
</script>
<template>
    <div class="flex mb-2.5 ml-2.5 mt-2.5 w-full justify-between">
        <div class="flex space-x-7">
            <input type="checkbox" class="h-8 w-8" v-model="nomination.selected"/>
            <p class="text-xl">
                {{ nomination.role }}
            </p>
        </div>
        <div class="flex items-center">
            <p class="text-xl mr-8">
                {{ nomination.nomination }}
            </p>
            <NomineeDropdown
                class="w-96 mr-[1.125rem]"
                :options="options"
                @optionSelected="(selection) => nomination.nomination = selection"
            />
        </div>
        
    </div>
</template>
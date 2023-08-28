<script setup>
import NomineeDropdown from '@/Components/Bookings/NomineeDropdown.vue';
let props = defineProps({
    nomination: Object,
    options: Object,
    isDisabled: Boolean
});

let emit = defineEmits(['nominationSelected']);

const disabledClass = "bg-gray-300 border-gray-100";
function isMobile() {
    if( screen.availWidth <= 760 ) {
        return true;
    }
    else {
        return false;
    }
}
</script>
<template>
    <div class="flex mb-2.5 mt-2.5 w-full">
        <div class="flex laptop:space-x-4 1080:space-x-7 ml-2 1080:ml-2.5 1440:ml-3 4k:ml-4 w-full mr-2 justify-between">
            <div>
                <div class="flex space-x-3 laptop:space-x-6 4k:space-x-8">
                    <input type="checkbox"
                        class="1080:w-6 1080:h-6 1440:w-8 1440:h-8 4k:h-12 4k:w-12"
                        :class="isDisabled ? disabledClass : ''"
                        v-model="nomination.selected"
                        :disabled="isDisabled"   
                        @click="emit('nominationSelected', nomination.selected)" 
                    />
                    <p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl h-full w-[10rem] laptop:w-[12rem] 1080:w-[22rem] 1440:w-[31rem] 4k:w-[48rem]">
                        {{ nomination.role }}
                    </p>
                </div>
                <p v-if="isMobile()" v-show="nomination.nomination !== ''" class="text-xs pl-6">
                    →{{ nomination.nomination }}
                </p>
            </div>
            <p v-if="!isMobile()" class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl h-full w-[11rem] 1080:w-[17rem] 1440:w-[20rem] 4k:w-[32rem]">
                {{ nomination.nomination }}
            </p>
            <NomineeDropdown
                class="w-[8rem] laptop:w-[12rem] 1080:w-[17rem] 1440:w-[22rem] 4k:w-[32rem]"
                :options="options"
                @optionSelected="(selection) => nomination.nomination = selection"
                :isDisabled="isDisabled"
            />
        </div>
    </div>
</template>
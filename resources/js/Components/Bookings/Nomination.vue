<script setup>
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
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
        <div class="flex laptop:space-x-4 1080:space-x-7 ml-2 1080:ml-2.5 1440:ml-3 4k:ml-4 w-full mr-2 justify-between">
            <div class="w-[70%]">
                <div class="flex space-x-3 laptop:space-x-6 4k:space-x-8">
                    <input type="checkbox"
                        class="1080:w-6 1080:h-6 1440:w-8 1440:h-8 4k:h-12 4k:w-12"
                        :class="isDisabled ? disabledClass : ''"
                        v-model="nomination.selected"
                        :disabled="isDisabled"   
                        @click="emit('nominationSelected', nomination.selected)" 
                    />
                    <p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl h-full w-full">
                        {{ nomination.role }}
                    </p>
                </div>
            </div>
            <vSelect :options="props.options" :clearable="true"
                style="width: 30%; height: 2rem; background-color: white; 
                border: solid; border-color: #6b7280; border-width: 1px;
                --vs-border-style: none; --vs-search-input-placeholder-color: #6b7280"                                 
                v-model="props.nomination.nomination"
                @option:selected="(selection) => nomination.nomination = selection"
            />
        </div>
    </div>
</template>
<script setup>
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
import { useDark } from "@vueuse/core";
const isDark = useDark();
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
let props = defineProps({
    nomination: Object,
    options: Object,
    isDisabled: Boolean
});

let emit = defineEmits(['nominationSelected']);
</script>
<template>
    <div v-if="isMobile" class="flex mb-2.5 mt-2.5 w-full">
        <div class="mx-2 w-full justify-between">
            <div class="w-full pr-2">
                <div class="flex space-x-3 w-full laptop:space-x-6 4k:space-x-8">
                    <input type="checkbox"
                        class="w-8 h-8"
                        :class="isDisabled ? isDark?'bg-gray-700 border-gray-600':'bg-gray-300 border-gray-100' : isDark?'bg-gray-800':''"                        v-model="nomination.selected"
                        :disabled="isDisabled"   
                        @click="emit('nominationSelected', nomination.selected)" 
                    />
                    <p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl h-full w-full 4k:pl-3">
                        {{ nomination.role }}
                    </p>
                </div>
            </div>
            <vSelect :options="props.options" :clearable="true" :class="isDark ? 'dropdown-dark':''" class="my-1"
                style="width: 100%; height: 2rem; background-color: inherit; font-size: 0.75rem; "     
                v-model="props.nomination.nomination"
                @option:selected="(selection) => nomination.nomination = selection"
                :disabled="isDisabled"
            />
        </div>
    </div>
    <div v-else class="flex mb-2.5 mt-2.5 w-full">
        <div class="flex space-x-6 mx-2 w-full justify-between">
            <div class="w-[60%]">
                <div class="flex space-x-3 w-full laptop:space-x-6 4k:space-x-8">
                    <input type="checkbox"
                        class="w-8 h-8"
                        :class="isDisabled ? isDark?'bg-gray-700 border-gray-600':'bg-gray-300 border-gray-100' : isDark?'bg-gray-800':''"                        v-model="nomination.selected"
                        :disabled="isDisabled"   
                        @click="emit('nominationSelected', nomination.selected)" 
                    />
                    <p class="text-xs 1080:text-lg 1440:text-xl 4k:text-2xl h-full w-full 4k:pl-3">
                        {{ nomination.role }}
                    </p>
                </div>
            </div>
            <vSelect :options="props.options" :clearable="true" :class="isDark ? 'dropdown-dark':''"
                style="width: 40%; height: 2rem; background-color: inherit; "       
                v-model="props.nomination.nomination"
                @option:selected="(selection) => nomination.nomination = selection"
                :disabled="isDisabled"
            />
        </div>
    </div>
</template>
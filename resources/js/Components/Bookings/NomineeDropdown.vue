<script setup>
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { ref } from 'vue';
import NomineeDropdownOption from "./NomineeDropdownOption.vue";

let emit = defineEmits(['optionSelected']);
let props = defineProps({
    options: {
        type: Object,
        default: {},
    },
});

let dropdownSearch = ref("");
let displayOptions = ref(false);
let deadAreaColor = "#FFFFFF";

let getButtonSrc = (val) => {
    return val ? '/images/triangle_up.svg' : '/images/triangle_down.svg';
};

function handleSelection(label) {
    displayOptions.value = false;
    emit('optionSelected', label);
};
</script>
<template>
    <div class="flex flex-col">
        <div class="flex flex-row">
            <input type="text"
                class="h-8 w-full"
                v-model="dropdownSearch"
                @focusin="displayOptions=true"
            />
            <button class="w-8"
                @click="displayOptions = !displayOptions"
            >
                <img :src="getButtonSrc(displayOptions)"
                class="border-y border-r border-colour h-full w-full"/>
            </button>
        </div>
        <div class="bg-white border-x border-b border-black h-40 z-10"
            v-show="displayOptions"
        >
            <VueScrollingTable
                class="w-full"
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <NomineeDropdownOption
                        v-for="option in options.filter(option => option.toLowerCase().includes(dropdownSearch.toLowerCase()))"
                        :label="option"
                        @optionSelected="(selectedOption) => handleSelection(selectedOption)"
                    />
                </template>
            </VueScrollingTable>
        </div>
    </div>
</template>
<style>
.border-colour {
    border-right-color: #6b7280;
    border-top-color: #6b7280;
    border-bottom-color: #6b7280;
}
</style>
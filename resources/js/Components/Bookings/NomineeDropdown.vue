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
    isDisabled: Boolean,
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

const disabledClass = "bg-gray-300 border-gray-100";
</script>
<template>
    <div class="flex flex-col">
        <div class="flex flex-row">
            <input type="text"
                :class="isDisabled ? disabledClass : ''"
                class="h-8 w-full"
                v-model="dropdownSearch"
                @focusin="displayOptions=true"
                :disabled="isDisabled"
            />
            <button class="w-8"
                :class="isDisabled ? disabledClass : ''"
                @click="displayOptions = !displayOptions"
                :disabled="isDisabled"
            >
                <img :src="getButtonSrc(displayOptions)"
                    class="border-y border-r h-full w-full"
                    :class="isDisabled ? disabledClass : 'border-colour'"
                />
            </button>
        </div>
        <div class="bg-white border-x border-b border-black h-40 z-10"
            v-show="displayOptions && !isDisabled"
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
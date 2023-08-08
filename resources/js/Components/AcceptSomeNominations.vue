<script setup>
import Modal from './Modal.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import axios from 'axios';
import AcceptSomeNominationOptions from './AcceptSomeNominationOptions.vue';
import { ref } from 'vue';

let emit = defineEmits(['close']);
let props = defineProps({
    data: Object,
    roles: Object
});
let deadAreaColor = "#FFFFFF";

let buttonActive = ref(false);

function handleStatusChangedForRole(role, status) {
    role.status = status;

    if (props.roles.filter(r => r.status !== 'U').length == props.roles.length) {
        buttonActive.value = true;
    }
    else {
        buttonActive.value = false;
    }
}
</script>
<template>
<Modal>
    <div class="bg-white w-2/5 h-[48rem] rounded-md p-4" v-if="props.data">
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-4xl font-bold">
                <!-- Filter for content element that contains 'Duration' and get the first element
                    Assumes that there is Duration in one of the content elements    
                -->
                {{ JSON.parse(props.data.content).filter(content => content.includes('Duration:'))[0] }}
            </p>
            <button @click="emit('close'); buttonActive = false">
                    <img src="/images/close.svg"
                    class="close-button h-full"
                />
            </button>
        </div>
        <div class="flex h-[10%] items-center justify-between">
            <p class="text-2xl">
                You have been nominated for the following roles by {{ props.data.senderName }}:
            </p>
        </div>
        <div class="h-[70%] py-4">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div class="flex mb-2 items-center space-x-2 justify-between mr-4"
                        v-for="role in props.roles" :key="role.id">
                        <p class="text-lg">
                            {{ role.roleName }}
                        </p>
                        <AcceptSomeNominationOptions
                            @statusUpdated="(status) => handleStatusChangedForRole(role, status)"
                        />
                    </div>
                </template>
            </VueScrollingTable>
        </div>
        <div class="h-[10%]">
            <button
                class="w-full h-full p-4 rounded-md text-4xl font-bold"
                :class="{
                    'bg-blue-300': buttonActive,
                    'bg-gray-300': !buttonActive
                }"
                :disabled="!buttonActive"    
            >
                Submit Responses
            </button>
        </div>
    </div>
</Modal>
</template>
<style>
.close-button {
    width: auto;
}
</style>
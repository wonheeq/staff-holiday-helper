<script setup>
import Modal from './Modal.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import axios from 'axios';
import { computed } from 'vue';

let emit = defineEmits(['close']);
let props = defineProps({
    data: Object,
    roles: Object
});
let deadAreaColor = "#FFFFFF";

const computedRoles = computed(() => props.roles);
</script>
<template>
<Modal>
    <div class="bg-white w-2/5 h-[48rem] rounded-md p-4">
        <div class="flex h-[10%] items-center justify-between">
            <p class="font-bold text-4xl">
                You have been nominated for the following roles ({{ props.data.applicationNo }}):
            </p>
            <button @click="emit('close')">
                    <img src="/images/close.svg"
                    class="close-button h-full"
                />
            </button>
        </div>
        <div class="h-[90%]">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div class="flex mb-2 items-center space-x-2 justify-between mr-4"
                        v-for="role in computedRoles" :key="role.id">
                        <p>
                            {{ role.roleName }}
                        </p>
                        <div class="flex space-x-4">
                            <button class="rounded-md border border-black p-2">
                                Accept
                            </button>
                            <button class="rounded-md border border-black p-2"
                                :class="role.classes.reject"
                                @click="role.status = 'N'; role.classes.reject='bg-red-400';"
                            >
                                Reject
                            </button>
                        </div>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
</Modal>
</template>
<style>
.close-button {
    width: auto;
}
</style>
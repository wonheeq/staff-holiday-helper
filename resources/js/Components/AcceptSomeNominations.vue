<script setup>
import Modal from './Modal.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import axios from 'axios';

let emit = defineEmits(['close']);
let props = defineProps({
    data: Object,
    roles: Object
});
let deadAreaColor = "#FFFFFF";
</script>
<template>
<Modal>
    <div class="bg-white w-2/5 h-[48rem] rounded-md p-4">
        <div class="flex h-[10%] items-center justify-between">
            <p class="font-bold text-4xl">
                You have been nominated for the following roles:
            </p>
            <button @click="emit('close')">
                    <img src="/images/close.svg"
                    class="close-button"
                />
            </button>
        </div>
        <div class="h-[90%]">
            <VueScrollingTable
                :deadAreaColor="deadAreaColor"
                :scrollHorizontal="false"
            >
                <template #tbody>
                    <div class="flex mb-2 items-center space-x-2" v-for="role in props.roles" :key="role.id">
                        <input type="checkbox" />
                        <p>
                            {{ role.roleName }}
                        </p>
                    </div>
                </template>
            </VueScrollingTable>
        </div>
    </div>
</Modal>
</template>
<style>
.close-button {
    height: 40px;
    width: auto;
}
/* 1080p */
@media 
(min-width: 1920px) {
    .close-button {
        height: 56px;
        width: auto;
    }
}
/* 1440p */
@media 
(min-width: 2560px) {
    .close-button {
        height: 60px;
        width: auto;
    }
}
/* 2160p */
@media 
(min-width: 3840px) {
    .close-button {
        height: 80px;
        width: auto;
    }
}
</style>
<script setup>
import Modal from './Modal.vue';
import VueScrollingTable from "vue-scrolling-table";
import "/node_modules/vue-scrolling-table/dist/style.css";
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/UserStore';
import Swal from 'sweetalert2';
import axios from 'axios';
let userStore = useUserStore();
const { userId } = storeToRefs(userStore);
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

function submitResponses() {
    let responses = [];

    for (let role in props.roles) {
        responses.push({
            "accountRoleId": role.accountRoleId,
            "status": role.status
        });
    }

    let data = {
        'messageId': props.data.messageId,
        'accountNo': userId.value,
        'applicationNo': props.data.applicationNo,
        'responses': responses
    };   

    axios.post('/api/acceptSomeNominations', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to respond to nominations, please try again.',
                    text: res.data.error
                });
                console.log(res);
            }
            else {
                props.data.acknowledged = 1;
                props.data.updated_at = new Date();
                Swal.fire({
                    icon: "success",
                    title: 'Successfully responded to the nominations.',
                }).then(() => {
                    handleClose();
                });
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to respond to nominations, please try again.',
        });
    });
}

function handleClose() {
    buttonActive.value = false;
    emit('close');
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
                {{ props.data.content && JSON.parse(props.data.content).filter(content => content.includes('Duration:'))[0] }}
            </p>
            <button @click="handleClose()">
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
                @click="submitResponses()"
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
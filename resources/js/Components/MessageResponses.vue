<script setup>
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/UserStore';
import Swal from 'sweetalert2';
import axios from 'axios';
let userStore = useUserStore();
const { userId } = storeToRefs(userStore);
let props = defineProps({
    source: Object,
});

let emit = defineEmits(['acceptSomeNominations']);

const element_class = "flex flex-row justify-evenly pl-2 w-[11.5rem] 1080:w-[19rem] 1440:w-[22rem] 4k:w-[34.5rem] border-l-4 border-white";

/*
processes the data and sends it to the acceptNominations method in the backend
*/
function handleAcceptAll() {
    let data = {
        'messageId': props.source.messageId,
        'accountNo': userId.value,
        'applicationNo': props.source.applicationNo,
    };
    axios.post('/api/acceptNominations', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to accept nominations, please try again.',
                    text: res.message
                });
                console.log(res);
            }
            else {
                // Set acknowledged status of message to true and update updated_at date
                props.source.acknowledged = 1;
                props.source.updated_at = new Date();
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to accept nominations, please try again.',
        });
    });
}

// Remits acceptSomeNominations event to be handled by parent
function handleAcceptSome() {
    emit('acceptSomeNominations');
}

/*
processes the data and sends it to the rejectNominations method in the backend
*/
function handleReject() {
    let data = {
        'messageId': props.source.messageId,
        'accountNo': userId.value,
        'applicationNo': props.source.applicationNo,
    };
    axios.post('/api/rejectNominations', data)
        .then(res => {
            if (res.status == 500) {
                Swal.fire({
                    icon: "error",
                    title: 'Failed to reject nominations, please try again.',
                    text: res.message
                });
            }
            else {
                // Set acknowledged status of message to true and update updated_at date
                props.source.acknowledged = 1;
                props.source.updated_at = new Date();
            }
        }).catch(err => {
        console.log(err);
        Swal.fire({
            icon: "error",
            title: 'Failed to reject nominations, please try again.',
        });
    });
}
</script>

<template>
    <!--Substitution Request for a single nomination, message is not acknowledged-->
    <div v-if="props.source.subject=='Substitution Request' && !props.source.isNominatedMultiple && props.source.acknowledged == 0" :class="element_class">
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center"
                @click="handleAcceptAll()"
            >
                <img src="/images/accept.svg"/>
                <p class="text-sm 1440:text-lg">Accept</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center"
                @click="handleReject()"
            >
                <img src="/images/reject.svg"/>
                <p class="text-sm 1440:text-lg">Reject</p>
            </button>
        </div>
    </div>
    <!--Substitution Request for a multi nomination, message is not acknowledged-->
    <div v-if="props.source.subject=='Substitution Request' && props.source.isNominatedMultiple && props.source.acknowledged == 0" :class="element_class">
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center"
                @click="handleAcceptAll()"
            >
                <img src="/images/accept.svg"/>
                <p class="text-sm 1440:text-lg">Accept All</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center"
                @click="handleAcceptSome()"
            >
                <img src="/images/accept-some.svg"/>
                <p class="text-sm 1440:text-lg">Accept Some</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center"
                @click="handleReject()"
            >
                <img src="/images/reject.svg"/>
                <p class="text-sm 1440:text-lg">Reject All</p>
            </button>
        </div>
    </div>
    <!--Regular message, message is not acknowledged-->
    <div v-show="props.source.subject!='Substitution Request' && props.source.acknowledged == 0" :class="element_class">
        <div class="flex flex-col justify-center ">
            <button @click="props.source.acknowledged = 1"
                class="flex flex-col items-center">
                <img src="/images/acknowledge.svg"/>
                <p class="text-sm 1440:text-lg">Acknowledge</p>
            </button>
        </div>
    </div>
    <!--Regular message, message is acknowledged-->
    <div v-show="props.source.acknowledged == 1" :class="element_class">
        <div class="flex flex-col justify-center ">
            Acknowledged at {{ new Date(props.source.updated_at).toLocaleString() }}
        </div>
    </div>
</template>
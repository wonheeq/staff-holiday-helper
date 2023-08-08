<script setup>
import axios from 'axios';
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/UserStore';
let userStore = useUserStore();
const { userId } = storeToRefs(userStore);

let props = defineProps({
    source: Object,
});


function handleAcknowledgeMessage() {
    props.source.acknowledged = 1;
    props.source.updated_at = new Date();

    let data = {
        'messageId': props.source.messageId,
        'accountNo': userId.value,
    };

    axios.post('/api/acknowledgeMessage', data)
            .then(res => {
                if (res.status != 200) {
                    console.log(err);
                }
            }).catch(err => {
            console.log(err);
        });
}

const element_class = "flex flex-row justify-evenly pl-2 w-[11.5rem] 1080:w-[19rem] 1440:w-[22rem] 4k:w-[34.5rem] border-l-4 border-white";
</script>

<template>
    <div v-if="props.source.title=='Substitution Request' && !props.source.is_nominated_multiple && props.source.acknowledged == 0" :class="element_class">
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="/images/accept.svg"/>
                <p class="text-sm 1440:text-lg">Accept</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="/images/reject.svg"/>
                <p class="text-sm 1440:text-lg">Reject</p>
            </button>
        </div>
    </div>
    <div v-if="props.source.title=='Substitution Request' && props.source.is_nominated_multiple && props.source.acknowledged == 0" :class="element_class">
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="/images/accept.svg"/>
                <p class="text-sm 1440:text-lg">Accept All</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="/images/accept-some.svg"/>
                <p class="text-sm 1440:text-lg">Accept Some</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="/images/reject.svg"/>
                <p class="text-sm 1440:text-lg">Reject All</p>
            </button>
        </div>
    </div>
    <div v-show="props.source.title!='Substitution Request' && props.source.acknowledged == 0" :class="element_class">
        <div class="flex flex-col justify-center ">
            <button @click="handleAcknowledgeMessage()"
                class="flex flex-col items-center">
                <img src="/images/acknowledge.svg"/>
                <p class="text-sm 1440:text-lg">Acknowledge</p>
            </button>
        </div>
    </div>
    <div v-show="props.source.acknowledged == 1" :class="element_class">
        <div class="flex flex-col justify-center ">
            Acknowledged at {{ new Date(props.source.updated_at).toLocaleString() }}
        </div>
    </div>
</template>
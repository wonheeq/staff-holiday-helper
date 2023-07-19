<script setup>
let props = defineProps({
    isAcknowledged: Boolean,
    source: Object,
});
</script>

<template>
    <div v-show="props.source.title=='Substitution Request'" class="flex flex-row justify-evenly pl-2 w-36 1440:w-48 4k:w-60 border-l-4 border-white">
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="images/accept.svg"/>
                <p class="text-sm 1440:text-lg">Accept</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="images/reject.svg"/>
                <p class="text-sm 1440:text-lg">Reject</p>
            </button>
        </div>
    </div>
    <div v-show="props.source.title!='Substitution Request' && !isAcknowledged" class="flex flex-row justify-evenly space-x-2 px-4 w-36 1440:w-48 4k:w-60 border-l-4 border-white">
        <div class="flex flex-col justify-center ">
            <button @click="props.source.acknowledged = 1"
                class="flex flex-col items-center">
                <img src="images/acknowledge.svg"/>
                <p class="text-sm 1440:text-lg">Acknowledge</p>
            </button>
        </div>
    </div>
</template>
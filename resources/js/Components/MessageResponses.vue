<script setup>
let props = defineProps({
    isAcknowledged: Boolean,
    source: Object,
});

const element_class = "flex flex-row justify-evenly pl-2 w-[11.5rem] 1080:w-[19rem] 1440:w-[22rem] 4k:w-[34.5rem] border-l-4 border-white";
</script>

<template>
    <div v-if="props.source.title=='Substitution Request' && !props.source.is_nominated_multiple" :class="element_class">
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
    <div v-if="props.source.title=='Substitution Request' && props.source.is_nominated_multiple" :class="element_class">
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="images/accept.svg"/>
                <p class="text-sm 1440:text-lg">Accept All</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="images/accept-some.svg"/>
                <p class="text-sm 1440:text-lg">Accept Some</p>
            </button>
        </div>
        <div class="flex flex-col justify-center">
            <button class="flex flex-col items-center">
                <img src="images/reject.svg"/>
                <p class="text-sm 1440:text-lg">Reject All</p>
            </button>
        </div>
    </div>
    <div v-if="props.source.title!='Substitution Request' && !isAcknowledged" :class="element_class">
        <div class="flex flex-col justify-center ">
            <button @click="props.source.acknowledged = 1"
                class="flex flex-col items-center">
                <img src="images/acknowledge.svg"/>
                <p class="text-sm 1440:text-lg">Acknowledge</p>
            </button>
        </div>
    </div>
</template>
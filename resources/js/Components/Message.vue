<script setup>

let props = defineProps({
    source: Object,
});

let copyEmail = (e) => {
    let email = e + "@curtin.edu.au";
    navigator.clipboard.writeText(email); 
    alert("Email address copied to clipboard.");
};
</script>

<template>
    <div class="flex flex-row justify-between bg-gray-200 p-2">
        <div class="flex flex-col">
            <div class="flex flex-row items-center">
                <p class="text-sm 1440:text-lg font-bold">{{ props.source.title }}</p>
                <p class="text-sm 1440:text-lg ml-2">by {{ props.source.titleUserName }}</p>
                <img
                    class="ml-1.5 email"
                    src="images/mail.svg"
                    v-b-tooltip.hover title="Copy Email Address to Clipboard"
                    @click="copyEmail(props.source.titleUserId)"
                    />
            </div>
            <p v-for="p in props.source.content" class="laptop:text-xs">
                {{ p }}
            </p>
            <p class="text-xs 1440:text-sm">Message created at {{ props.source.timestamp }}</p>
        </div>
        <div v-show="props.source.title=='Substitution Request'" class="flex flex-row justify-evenly pl-2 w-36 1440:w-48 border-l-4 border-white">
            <div class="flex flex-col justify-center">
                <button class="flex flex-col items-center">
                    <img src="images/accept.svg"/>
                    <p class="text-sm">Accept</p>
                </button>
            </div>
            <div class="flex flex-col justify-center">
                <button class="flex flex-col items-center">
                    <img src="images/reject.svg"/>
                    <p class="text-sm">Reject</p>
                </button>
            </div>
        </div>
        <div v-show="props.source.title!='Substitution Request'" class="flex flex-row justify-evenly space-x-2 px-4 w-36 1440:w-48 border-l-4 border-white">
            <div class="flex flex-col justify-center ">
                <button class="flex flex-col items-center">
                    <img src="images/acknowledge.svg"/>
                    <p class="text-sm">Acknowledge</p>
                </button>
            </div>
        </div>
    </div>
</template>

<style>
.email{
    height: 18px;
    width: 18px;
}

/* 1440p */
@media 
(min-width: 1930px) {
.email{
    height: 24px;
    width: 24px;
}
}
</style>
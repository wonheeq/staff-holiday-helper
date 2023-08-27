<script setup>
import MessageResponses from './MessageResponses.vue';
import Swal from 'sweetalert2';
let props = defineProps({
    source: Object,
});

let emit = defineEmits(['acceptSomeNominations', 'reviewApplication']);

let copyEmail = (e) => {
    let email = e + "@curtin.edu.au";
    navigator.clipboard.writeText(email); 
    
    Swal.fire("Email address copied to clipboard.");
};

function handleAcceptSomeNominations() {
    emit('acceptSomeNominations');
}

function handleReviewApplication() {
    emit('reviewApplication');
}
</script>

<template>
    <div class="flex flex-row justify-between bg-gray-200 p-2">
        <div class="flex flex-col w-[75%] laptop:w-full">
            <div class="flex flex-row items-center">
                <p class="text-sm 1080:text-lg 1440:text-xl 4k:text-2xl font-bold">{{ props.source.subject }}</p>
                <p class="text-sm 1080:text-lg 1440:text-xl 4k:text-2xl ml-2">by {{ props.source.senderName }}</p>
                <input
                    type="image"
                    v-if="props.source.senderNo != null"
                    class="ml-1.5 email"
                    src="/images/mail.svg"
                    title="Copy Email Address to Clipboard"
                    @click="copyEmail(props.source.senderNo)"
                    v-title
                    />
            </div>
            <p class="text-xs 1080:text-base 1440:text-lg 4k:text-xl"
                v-for="content in JSON.parse(props.source.content)"
            >
                {{ content }}
            </p>
            <p class="text-xs 1080:text-sm 1440:text-base 4k:text-xl">Message created at {{ new Date(props.source.created_at).toLocaleString() }}</p>
        </div>
        <MessageResponses
            class="w-[25%] laptop:w-fit"
            :source="source"
            @acceptSomeNominations="handleAcceptSomeNominations()"
            @reviewApplication="handleReviewApplication()"
        />
    </div>
</template>

<style>
.email{
    height: 18px;
    width: 18px;
}

/* 1080p */
@media 
(min-width: 1920px) {
.email{
    height: 22px;
    width: 22px;
}
}
/* 1440p */
@media 
(min-width: 2560px) {
.email{
    height: 24px;
    width: 24px;
}
}
/* 4k */
@media 
(min-width: 3840px) {
.email{
    height: 40px;
    width: 40px;
}
}
</style>
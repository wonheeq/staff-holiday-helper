<script setup>
import MessageResponses from './MessageResponses.vue';
import Swal from 'sweetalert2';
import { useDark } from "@vueuse/core";
import { storeToRefs } from 'pinia';
import { useScreenSizeStore } from '@/stores/ScreenSizeStore';
const screenSizeStore = useScreenSizeStore();
const { isMobile } = storeToRefs(screenSizeStore);
const isDark = useDark();
let props = defineProps({
    source: Object,
});

let emit = defineEmits(['acceptSomeNominations', 'reviewApplication']);

let copyEmail = (e) => {
    let email = e + "@curtin.edu.au";
    navigator.clipboard.writeText(email); 
    
    Swal.fire({
        'title':"Email address copied to clipboard.",
        'icon':'info'
    }); 
};

function handleAcceptSomeNominations() {
    emit('acceptSomeNominations');
}

function handleReviewApplication() {
    emit('reviewApplication');
}
const textClass = "text-sm 1080:text-lg 1440:text-xl 4k:text-2xl";
</script>

<template>
    <div class="flex flex-row justify-between p-2" :class="isDark?'bg-gray-700':'bg-gray-200'">
        <div v-if="props.source.subject !== 'System Notification'" class="flex flex-col w-[75%] laptop:w-full pr-2">
            <div v-if="isMobile && props.source.subject !== 'System Notification'" class="flex-row items-center">
                <p class="text-sm 1080:text-lg 1440:text-xl 4k:text-2xl font-bold">
                    {{ props.source.subject }}
                </p>
                <div class="flex items-center" :class="textClass">
                    <p v-if="props.source.subject == 'Confirmed Substitutions'">
                        for {{ props.source.senderName }}   
                    </p>
                    <p v-else :class="textClass">
                        by {{ props.source.senderName }}
                    </p>
                    <input
                        type="image"
                        v-if="props.source.senderNo != null"
                        class="ml-1.5 email"
                        :class="isDark?'darkModeEmail':''"
                        src="/images/mail.svg"
                        title="Copy Email Address to Clipboard"
                        @click="copyEmail(props.source.senderNo)"
                        v-title
                    />
                </div>
            </div>
            <div v-if="!isMobile && props.source.subject !== 'System Notification'" class="flex flex-row items-center">
                <p class="font-bold" :class="textClass">
                    {{ props.source.subject }}
                </p>
                <div class="flex items-center">
                    <p v-if="props.source.subject == 'Confirmed Substitutions'" :class="textClass" class="ml-1.5">
                        for {{ props.source.senderName }}
                    </p>
                    <p v-else :class="textClass" class="ml-1.5">
                        by {{ props.source.senderName }}
                    </p>
                    <input
                        type="image"
                        v-if="props.source.senderNo != null"
                        class="ml-1.5 email"
                        :class="isDark?'darkModeEmail':''"
                        src="/images/mail.svg"
                        title="Copy Email Address to Clipboard"
                        @click="copyEmail(props.source.senderNo)"
                        v-title
                        />
                </div>
            </div>
            <p v-if="props.source.subject !== 'System Notification'" class="whitespace-pre-wrap text-xs 1080:text-base 1440:text-lg 4k:text-xl"
                v-for="content in JSON.parse(props.source.content)"
            >
                {{ content }}
            </p>
            <p v-if="props.source.subject !== 'System Notification'" class="text-xs 1080:text-sm 1440:text-base 4k:text-xl">Message created at {{ new Date(props.source.created_at).toLocaleString() }}</p>
        </div>
        <div v-if="props.source.subject == 'System Notification'" class="flex flex-col w-[75%] laptop:w-full">
            <div class="flex laptop:flex-row items-center">
                <p class="text-sm 1080:text-lg 1440:text-xl 4k:text-2xl">
                    <b>{{ props.source.subject }}</b>
                </p>
            </div>
            <p class="whitespace-pre-wrap text-xs 1080:text-base 1440:text-lg 4k:text-xl">
                {{ JSON.parse(props.source.content) }}
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

.darkModeEmail {
    filter: invert(0%) sepia(0%) saturate(100%) hue-rotate(0deg) brightness(200%) contrast(60%);
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